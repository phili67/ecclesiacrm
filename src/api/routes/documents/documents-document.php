<?php

// copyright 2019 Philippe Logel All rights reserved not MIT licence
use Slim\Http\Request;
use Slim\Http\Response;

// Documents filemanager APIs
use EcclesiaCRM\Note;
use EcclesiaCRM\SessionUser;

$app->group('/document', function () {
    
    $this->post('/create', 'createDocument' );

});

function createDocument(Request $request, Response $response, array $args) {
  $input = (object)$request->getParsedBody();

  if ( isset ($input->personID) && isset ($input->famID) && isset ($input->title) && isset ($input->type) && isset ($input->text) && isset ($input->bPrivate)){
    $note = new Note();
    $note->setPerId($input->personID);
    $note->setFamId($input->famID);
    $note->setPrivate($input->bPrivate);
    $note->setTitle($input->title);
    $note->setText($input->text);
    $note->setType($input->type);
    $note->setEntered(SessionUser::getUser()->getPersonId());

    $note->setCurrentEditedBy(0);
    $note->setCurrentEditedDate(NULL);
    $note->save();
   
    return $response->withJson(['success' => true]);
  }
  
  return $response->withJson(['success' => false]);
}

