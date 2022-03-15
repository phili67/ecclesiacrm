<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use Slim\Views\PhpRenderer;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\TokenQuery;
use EcclesiaCRM\Note;
use EcclesiaCRM\Person;

$app->group('/verify', function (RouteCollectorProxy $group) {

    $group->get('/{token}', function (Request $request, Response $response, array $args) {
        $renderer = new PhpRenderer("templates/verify/");
        $token = TokenQuery::create()->findPk($args['token']);
        $haveFamily = false;
        if ($token != null && $token->isVerifyFamilyToken() && $token->isValid()) {
            $family = FamilyQuery::create()->findPk($token->getReferenceId());
            $haveFamily = ($family != null);
            if ($token->getRemainingUses() > 0) {
                $token->setRemainingUses($token->getRemainingUses() - 1);
                $token->save();
            }
        }

        if ($haveFamily) {
            return $renderer->render($response, "verify-family-info.php", array("family" => $family, "token" => $token));
        } else {
            return $renderer->render($response, "/../404.php", array("message" => gettext("Unable to load verification info")));
        }
    });

    $group->post('/{token}', function (Request $request, Response $response, array $args) {
        $token = TokenQuery::create()->findPk($args['token']);
        if ($token != null && $token->isVerifyFamilyToken() && $token->isValid()) {
            $family = FamilyQuery::create()->findPk($token->getReferenceId());
            if ($family != null) {
                $body = (object)$request->getParsedBody();
                $note = new Note();
                $note->setFamily($family);
                $note->setType("verify");
                $note->setEntered(Person::SELF_VERIFY);
                $note->setText(gettext("No Changes"));
                if (!empty($body->message)) {
                    $note->setText($body->message);
                }
                $note->save();
            }
        }
        return $response->withStatus(200);
    });

    /*$group->post('/', function (Request $request, Response $response, array $args) {
        $body = $request->getParsedBody();
        $renderer = new PhpRenderer("templates/verify/");
        $family = PersonQuery::create()->findByEmail($body["email"]);
        return $renderer->render($response, "view-info.php", array("family" => $family));
    });*/
});


