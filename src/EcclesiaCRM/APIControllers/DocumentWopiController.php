<?php

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Psr7\Stream;

use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Service\CollaboraService;

/**
 * WOPI host endpoints called by the Collabora Online server (no user session, no JWT) :
 * each request is authorized by the WOPI access token created in DocumentCollaboraController.
 */
class DocumentWopiController
{
    private $container;
    private CollaboraService $collabora;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->collabora = new CollaboraService();
    }

    private function authorize(ServerRequest $request, array $args): ?array
    {
        $token = (string)($request->getQueryParams()['access_token'] ?? '');

        return $this->collabora->validateToken($token, (string)$args['id']);
    }

    // CheckFileInfo : GET /wopi/files/{id}
    public function checkFileInfo(ServerRequest $request, Response $response, array $args): Response
    {
        $file = $this->authorize($request, $args);

        if (is_null($file)) {
            return $response->withStatus(404);
        }

        $person = PersonQuery::create()->findPk($file['user_id']);
        $user = UserQuery::create()->findOneByPersonId($file['user_id']);
        $modified = filemtime($file['path']);
        $origin = parse_url($this->collabora->getWopiHostURL());

        return $response->withJson([
            'BaseFileName' => $file['name'],
            'OwnerId' => (string)$file['owner_id'],
            'UserId' => (string)$file['user_id'],
            'UserFriendlyName' => is_null($person) ? 'User ' . $file['user_id'] : $person->getFullName(),
            'Size' => filesize($file['path']),
            'Version' => (string)$modified,
            'LastModifiedTime' => gmdate('Y-m-d\TH:i:s', $modified) . '.0000000Z',
            'UserCanWrite' => $file['can_write'],
            'SupportsUpdate' => true,
            'IsAdminUser' => !is_null($user) && $user->isAdmin(),
            'UserCanNotWriteRelative' => true,
            'PostMessageOrigin' => $origin['scheme'] . '://' . $origin['host'] . (isset($origin['port']) ? ':' . $origin['port'] : '')
        ], null, JSON_UNESCAPED_UNICODE);
    }

    // GetFile : GET /wopi/files/{id}/contents
    public function getFile(ServerRequest $request, Response $response, array $args): Response
    {
        $file = $this->authorize($request, $args);

        if (is_null($file)) {
            return $response->withStatus(404);
        }

        return $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Length', (string)filesize($file['path']))
            ->withHeader('X-WOPI-ItemVersion', (string)filemtime($file['path']))
            ->withBody(new Stream(fopen($file['path'], 'rb')));
    }

    // PutFile : POST /wopi/files/{id}/contents
    public function putFile(ServerRequest $request, Response $response, array $args): Response
    {
        $file = $this->authorize($request, $args);

        if (is_null($file)) {
            return $response->withStatus(404);
        }

        if (!$file['can_write']) {
            return $response->withStatus(403);
        }

        // the temporary file stays in the same folder : the final rename() is atomic
        $tmpFile = dirname($file['path']) . '/.wopi.' . bin2hex(random_bytes(6)) . '.tmp';
        $out = @fopen($tmpFile, 'wb');

        if ($out === false) {
            return $response->withStatus(500);
        }

        $body = $request->getBody();
        while (!$body->eof()) {
            fwrite($out, $body->read(65536));
        }
        fclose($out);

        // we never replace a document by an empty file
        if (filesize($tmpFile) === 0) {
            unlink($tmpFile);
            return $response->withStatus(400);
        }

        $this->collabora->backupFile($file['path']);

        @chmod($tmpFile, fileperms($file['path']) & 0777);

        if (!rename($tmpFile, $file['path'])) {
            @unlink($tmpFile);
            return $response->withStatus(500);
        }

        clearstatcache(true, $file['path']);

        return $response->withJson(['LastModifiedTime' => gmdate('Y-m-d\TH:i:s', filemtime($file['path'])) . '.0000000Z']);
    }
}
