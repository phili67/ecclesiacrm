<?php
// Copyright 2018 Philippe Logel all right reserved
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\ListOptionIconQuery;
use EcclesiaCRM\ListOptionIcon;


$app->group('/mapicons', function (RouteCollectorProxy $group) {

  $group->post('/getall', 'getAllMapIcons' );
  $group->post('/checkOnlyPersonView', 'checkOnlyPersonView' );
  $group->post('/setIconName', 'setIconName' );
  $group->post('/removeIcon', 'removeIcon' );

});

function getAllMapIcons (Request $request, Response $response, array $args) {
    $files = scandir('../skin/icons/markers');

    return $response->withJson(array_values(array_diff($files, array(".","..",'shadow'))));
}

function checkOnlyPersonView (Request $request, Response $response, array $args) {
  $params = (object)$request->getParsedBody();

  if (isset ($params->onlyPersonView) && isset ($params->lstID) && isset ($params->lstOptionID)) {
    $icon = ListOptionIconQuery::Create()->filterByListId($params->lstID)->findOneByListOptionId($params->lstOptionID);

    if (!empty($icon)) {
      $icon->setOnlyVisiblePersonView($params->onlyPersonView);
      $icon->save();
    } else {
      $icon = new ListOptionIcon();

      $icon->setListId($params->lstID);
      $icon->setListOptionId($params->lstOptionID);
      $icon->setOnlyVisiblePersonView($params->onlyPersonView);
      $icon->save();
    }

    return $response->withJson(['status' => "success"]);
  }

  return $response->withJson(['status' => "failed"]);
}

function setIconName (Request $request, Response $response, array $args) {
  $params = (object)$request->getParsedBody();

  if (isset ($params->name) && isset ($params->lstID) && isset ($params->lstOptionID)) {

    $icon = ListOptionIconQuery::Create()->filterByListId($params->lstID)->findOneByListOptionId($params->lstOptionID);

    if (!empty($icon)) {
      $icon->setUrl($params->name);
      $icon->save();
    } else {
      $icon = new ListOptionIcon();

      $icon->setListId($params->lstID);
      $icon->setListOptionId($params->lstOptionID);
      $icon->setUrl($params->name);
      $icon->save();
    }

    return $response->withJson(['status' => "success"]);
  }

  return $response->withJson(['status' => "failed"]);
}

function removeIcon (Request $request, Response $response, array $args) {
  $params = (object)$request->getParsedBody();

  if (isset ($params->lstID) && isset ($params->lstOptionID)) {

    $icon = ListOptionIconQuery::Create()->filterByListId($params->lstID)->findOneByListOptionId($params->lstOptionID);

    if (!empty($icon)) {
      $icon->delete();

      return $response->withJson(['status' => "success"]);
    }

    return $response->withJson(['status' => "failed"]);

  }

  return $response->withJson(['status' => "failed"]);
}
