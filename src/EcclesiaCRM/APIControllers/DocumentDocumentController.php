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
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PersonQuery;

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
            $note = new Note();
            $note->setPerId($input->personID);
            $note->setFamId($input->famID);
            $note->setPrivate($input->bPrivate);
            $note->setTitle(InputUtils::FilterHTML($input->title));
            $note->setText(InputUtils::FilterHTML($input->text));
            $note->setType($input->type);
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->setDateLastEdited(new \DateTime());

            $note->setCurrentEditedBy(0);
            $note->setCurrentEditedDate(NULL);

            $note->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function getDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->docID) && isset ($input->personID) && isset ($input->famID) ){
            $note = NoteQuery::Create()->findOneById ($input->docID);

            if ( $note->getCurrentEditedBy() > 0 && !( SessionUser::getUser()->isAdmin() || $note->isVisualableBy ($input->personID) ) ) {
                $currentDate = new \DateTime();

                $since_start = $currentDate->diff($note->getCurrentEditedDate());

                $min = $since_start->days * 24 * 60;
                $min += $since_start->h * 60;
                $min += $since_start->i;

                if ( $min < SystemConfig::getValue('iDocumentTimeLeft') ) {
                    $editor = PersonQuery::create()->findPk($note->getCurrentEditedBy());
                    if ($editor != null) {
                        $currentUserName = gettext("This document is opened by")." : ".$editor->getFullName()." (".(SystemConfig::getValue('iDocumentTimeLeft')-$min)." ".gettext("Minutes left").")";
                    }

                    return $response->withJson(['success' => false ,'note' => $note->toArray(),'message' => $currentUserName]);

                } else {// we reset the count
                    $note->setCurrentEditedDate(null);
                    $note->setCurrentEditedBy(0);
                    $note->save();
                }
            } else {

                // now the document is used by someone
                $note->setCurrentEditedBy(SessionUser::getUser()->getPersonId());
                $note->setCurrentEditedDate(new \DateTime());

                $note->save();
                // !now the document is used by someone

            }

            return $response->withJson(['success' => true ,'note' => $note->toArray()]);
        }

        return $response->withJson(['success' => false]);
    }

    public function leaveDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->docID) ){
            $note = NoteQuery::Create()->findOneById ($input->docID);

            if (!is_null($note)) {

                // now the document is used by someone
                $note->setCurrentEditedBy(0);
                $note->setCurrentEditedDate(NULL);

                $note->save();
                // !now the document is used by someone

                return $response->withJson(['success' => true ,'note' => $note->toArray()]);
            }

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function updateDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->docID) && isset ($input->title) && isset ($input->type) && isset ($input->text) && isset ($input->bPrivate)){
            $note = NoteQuery::Create()->findOneById ($input->docID);

            $note->setPrivate($input->bPrivate);
            $note->setTitle(InputUtils::FilterHTML($input->title));
            $note->setText(InputUtils::FilterHTML($input->text));
            $note->setType($input->type);
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->setDateLastEdited(new \DateTime());

            // now the document is no more used
            $note->setCurrentEditedBy(0);
            $note->setCurrentEditedDate(NULL);
            // ! now the document is no more used

            $note->save();

            return $response->withJson(['success' => true,'note' => $note->toArray()]);
        }

        return $response->withJson(['success' => false]);
    }

    public function deleteDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->docID) ){
            $note = NoteQuery::Create()->findOneById ($input->docID);

            $note->delete();

            return $response->withJson(['success']);
        }

        return $response->withJson(['success' => false]);
    }
}
