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

            $note->save();

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
            if (!$note->canEditNote(SessionUser::getId())) {
                return $response->withJson(['success' => true, 'note' => $note->toArray(), 'canEdit' => false, 'canChangePrivacy' => false]);
            }

            if ($note->getCurrentEditedBy() > 0 && (int)$note->getCurrentEditedBy() !== (int)SessionUser::getId() && $note->getCurrentEditedDate() !== null) {
                $currentDate = new \DateTime();

                $since_start = $currentDate->diff($note->getCurrentEditedDate());

                $min = $since_start->days * 24 * 60;
                $min += $since_start->h * 60;
                $min += $since_start->i;

                if ( $min < SystemConfig::getValue('iDocumentTimeLeft') ) {
                    $editor = PersonQuery::create()->findPk($note->getCurrentEditedBy());
                    $currentUserName = gettext("This document is opened by");
                    if ($editor != null) {
                        $currentUserName .= " : ".$editor->getFullName()." (".(SystemConfig::getValue('iDocumentTimeLeft')-$min)." ".gettext("Minutes left").")";
                    }

                    return $response->withJson(['success' => false, 'message' => $currentUserName]);

                } else {// we reset the count
                    $note->setCurrentEditedDate(null);
                    $note->setCurrentEditedBy(0);
                    $note->save();
                }
            }

            $note->setCurrentEditedBy(SessionUser::getId());
            $note->setCurrentEditedDate(new \DateTime());
            $note->save();

            return $response->withJson(['success' => true, 'note' => $note->toArray(), 'canEdit' => true, 'canChangePrivacy' => $note->isCreator(SessionUser::getId())]);
        }

        return $response->withJson(['success' => false]);
    }

    public function leaveDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->docID) ){
            $note = NoteQuery::Create()->findOneById ($input->docID);

            if (!is_null($note) && $note->isNoteDocument()
                && $note->canEditNote(SessionUser::getId())
                && (int)$note->getCurrentEditedBy() === (int)SessionUser::getId()) {

                // now the document is used by someone
                $note->setCurrentEditedBy(0);
                $note->setCurrentEditedDate(NULL);

                $note->save();
                // !now the document is used by someone

                return $response->withJson(['success' => true ,'note' => $note->toArray()]);
            }

            return $response->withJson(['success' => false, 'message' => _('Forbidden')], 403);
        }

        return $response->withJson(['success' => false]);
    }

    public function updateDocument(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->docID) && isset ($input->title) && isset ($input->type) && isset ($input->text) && isset ($input->bPrivate)){
            if (!is_bool($input->bPrivate) || !in_array($input->type, ['note', 'video', 'audio', 'document'], true)) {
                return $response->withJson(['success' => false, 'message' => _('Invalid document')], 400);
            }
            $note = NoteQuery::Create()->findOneById ($input->docID);

            if ($note === null || !$note->isNoteDocument() || !$note->canEditNote(SessionUser::getId())
                || (!$note->isCreator(SessionUser::getId()) && (bool)$input->bPrivate !== $note->isPrivate())) {
                return $response->withJson(['success' => false, 'message' => _('Forbidden')], 403);
            }
            if ($note->getCurrentEditedBy() > 0 && (int)$note->getCurrentEditedBy() !== (int)SessionUser::getId()
                && $note->getCurrentEditedDate() !== null
                && $note->currentEditbyUserName() !== '') {
                return $response->withJson(['success' => false, 'message' => _('This document is opened by another user')], 409);
            }

            $note->setPrivate($input->bPrivate);
            $note->setTitle(InputUtils::FilterHTML($input->title));
            $note->setText(InputUtils::FilterPresentationHTML($input->text));
            $note->setType($input->type);
            $note->setEditedBy(SessionUser::getId());
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

            if ($note !== null && $note->isNoteDocument() && $note->isCreator(SessionUser::getId())) {
                $note->delete();

                return $response->withJson(['success' => true]);
            }
        }

        return $response->withJson(['success' => false, 'message' => _('Forbidden')], 403);
    }
}
