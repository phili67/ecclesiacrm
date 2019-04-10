<?php

// copyright 2019 Philippe Logel All rights reserved not MIT licence
use Slim\Http\Request;
use Slim\Http\Response;

// Documents filemanager APIs
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\SessionUser;

$app->group('/document', function () {
    
    $this->post('/create', 'createDocument' );
    $this->post('/get',    'getDocument' );
    $this->post('/update', 'updateDocument' );

});

function getDocument(Request $request, Response $response, array $args) {
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

function createDocument(Request $request, Response $response, array $args) {
  $input = (object)$request->getParsedBody();

  if ( isset ($input->docID) && isset ($input->personID) && isset ($input->famID) ){
    $note = NoteQuery::Create()->findOneById ($input->docID);
       
    return $response->withJson(['success' => true ,'note' => $note->toArray()]);
  }
  
  return $response->withJson(['success' => false]);
}

function updateDocument(Request $request, Response $response, array $args) {
  $input = (object)$request->getParsedBody();

  if ( isset ($input->docID) && isset ($input->personID) && isset ($input->famID) && isset ($input->title) && isset ($input->type) && isset ($input->text) && isset ($input->bPrivate)){
    $note = NoteQuery::Create()->findOneById ($input->docID);
    
    if ($input->personID != 0) {
      $note->setPerId($input->personID);
    }
    
    if ($input->famID != 0) {
      $note->setFamId($input->famID);
    }
    
    $note->setPrivate($input->bPrivate);
    $note->setTitle($input->title);
    $note->setText($input->text);
    $note->setType($input->type);
    $note->setEntered(SessionUser::getUser()->getPersonId());

    $note->save();
   
    return $response->withJson(['success' => $note->toArray()]);
  }
  
  return $response->withJson(['success' => false]);
}

