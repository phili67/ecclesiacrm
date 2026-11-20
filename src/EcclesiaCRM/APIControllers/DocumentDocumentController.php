<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\Utils\InputUtils;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;


// Documents documents APIs
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Service\NoteRevisionService;
use Propel\Runtime\Propel;

class DocumentDocumentController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->personID) && isset ($input->famID) && isset ($input->title) && isset ($input->type) && isset ($input->text) && isset ($input->bPrivate)){
            if (!is_bool($input->bPrivate) || !in_array($input->type, ['note', 'video', 'audio', 'document'], true)) {
                return $response->withJson(['success' => false, 'message' => _('Invalid document')], 400);
            }
            $note = new Note();
            $note->setPerId($input->personID);
            $note->setFamId($input->famID);
            $note->setPrivate($input->bPrivate);
            $note->setTitle(InputUtils::FilterHTML($input->title));
            $note->setText(InputUtils::FilterPresentationHTML($input->text));
            $note->setType($input->type);
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->setDateLastEdited(new \DateTime());

            $note->setCurrentEditedBy(0);
            $note->setCurrentEditedDate(NULL);

            $connection = Propel::getWriteConnection('main');
            $connection->beginTransaction();
            try {
                $note->save($connection);
                (new NoteRevisionService())->record($note, SessionUser::getId(), new \DateTime(), $connection);
                $connection->commit();
            } catch (\Throwable $error) {
                $connection->rollBack();
                throw $error;
            }

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function getDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->docID) && isset ($input->personID) && isset ($input->famID) ){
            $note = NoteQuery::Create()->findOneById ($input->docID);

            if ($note === null || !$note->isNoteDocument() || !$note->canViewNote(SessionUser::getId())) {
                return $response->withJson(['success' => false, 'message' => _('Forbidden')], 403);
            }
            return $response->withJson([
                'success' => true,
                'note' => $note->toArray(),
                'version' => (new NoteRevisionService())->version($note, Propel::getWriteConnection('main')),
                'canEdit' => $note->canEditNote(SessionUser::getId()),
                'canChangePrivacy' => $note->isCreator(SessionUser::getId())
            ]);
        }

        return $response->withJson(['success' => false]);
    }

    public function leaveDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->docID) ){
            $note = NoteQuery::Create()->findOneById ($input->docID);

            if (!is_null($note) && $note->isNoteDocument() && $note->canViewNote(SessionUser::getId())) {
                return $response->withJson(['success' => true ,'note' => $note->toArray()]);
            }

            return $response->withJson(['success' => false, 'message' => _('Forbidden')], 403);
        }

        return $response->withJson(['success' => false]);
    }

    public function updateDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->docID) && isset ($input->title) && isset ($input->type) && isset ($input->text) && isset ($input->bPrivate) && isset($input->version)){
            if (!is_bool($input->bPrivate) || !in_array($input->type, ['note', 'video', 'audio', 'document'], true)) {
                return $response->withJson(['success' => false, 'message' => _('Invalid document')], 400);
            }
            return $this->saveVersion($response, $input, false);
        }

        return $response->withJson(['success' => false, 'message' => _('Invalid document')], 400);
    }

    public function history(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();
        $note = isset($input->docID) ? NoteQuery::create()->findOneById($input->docID) : null;
        if ($note === null || !$note->isNoteDocument() || !$note->canViewNote(SessionUser::getId())) {
            return $response->withJson(['success' => false, 'message' => _('Forbidden')], 403);
        }
        $service = new NoteRevisionService();
        $connection = Propel::getWriteConnection('main');
        $history = $service->history($note, $connection);
        foreach ($history as &$revision) {
            $author = PersonQuery::create()->findPk($revision['author_id']);
            $revision['author'] = $author ? $author->getFullName() : _('Unknown');
        }
        return $response->withJson([
            'success' => true, 'history' => $history,
            'version' => $service->version($note, $connection),
            'canEdit' => $note->canEditNote(SessionUser::getId())
        ]);
    }

    public function restore(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();
        if (!isset($input->docID, $input->revisionId, $input->version)
            || !is_numeric($input->revisionId) || (int)$input->revisionId <= 0) {
            return $response->withJson(['success' => false, 'message' => _('Invalid document')], 400);
        }
        return $this->saveVersion($response, $input, true);
    }

    private function saveVersion(Response $response, object $input, bool $restore): Response
    {
        $note = NoteQuery::create()->findOneById($input->docID);
        if ($note === null || !$note->isNoteDocument() || !$note->canEditNote(SessionUser::getId())) {
            return $response->withJson(['success' => false, 'message' => _('Forbidden')], 403);
        }
        $connection = Propel::getWriteConnection('main');
        $service = new NoteRevisionService();
        $connection->beginTransaction();
        try {
            $service->lock($note, SessionUser::getId(), $connection);
            if (!$note->isNoteDocument() || !$note->canEditNote(SessionUser::getId())) {
                $connection->rollBack();
                return $response->withJson(['success' => false, 'message' => _('Forbidden')], 403);
            }
            $version = $service->version($note, $connection);
            if (!is_string($input->version) || !hash_equals($version, $input->version)) {
                $connection->rollBack();
                return $response->withJson([
                    'success' => false, 'message' => _('This document has changed since you opened it.'),
                    'note' => $note->toArray(), 'version' => $version,
                    'canChangePrivacy' => $note->isCreator(SessionUser::getId())
                ], 409);
            }
            if (!$restore && !$note->isCreator(SessionUser::getId()) && $input->bPrivate !== $note->isPrivate()) {
                $connection->rollBack();
                return $response->withJson(['success' => false, 'message' => _('Forbidden')], 403);
            }
            if ($restore) {
                $revision = $service->find($note, (int)$input->revisionId, $connection);
                if ($revision === null) {
                    $connection->rollBack();
                    return $response->withJson(['success' => false, 'message' => _('Invalid document')], 400);
                }
            }
            $service->recordInitialIfMissing($note, $connection);
            $note->setTitle($restore ? $revision['title'] : InputUtils::FilterHTML($input->title));
            $note->setText($restore ? $revision['content'] : InputUtils::FilterPresentationHTML($input->text));
            $note->setType($restore ? $revision['type'] : $input->type);
            if (!$restore) {
                $note->setPrivate($input->bPrivate);
            }
            $now = new \DateTime();
            $note->setEditedBy(SessionUser::getId());
            $note->setDateLastEdited($now);
            $note->setCurrentEditedBy(0);
            $note->setCurrentEditedDate(null);
            $note->save($connection);
            $service->record($note, SessionUser::getId(), $now, $connection);
            $version = $service->version($note, $connection);
            $connection->commit();
            return $response->withJson(['success' => true, 'note' => $note->toArray(), 'version' => $version]);
        } catch (\Throwable $error) {
            $connection->rollBack();
            throw $error;
        }
    }

    public function deleteDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->docID) ){
            $note = NoteQuery::Create()->findOneById ($input->docID);

            if ($note !== null && $note->isNoteDocument() && $note->isCreator(SessionUser::getId())) {
                $note->delete();

                return $response->withJson(['success' => true]);
            }
        }

        return $response->withJson(['success' => false, 'message' => _('Forbidden')], 403);
    }
}
