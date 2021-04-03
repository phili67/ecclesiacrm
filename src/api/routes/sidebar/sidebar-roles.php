<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\SessionUser;
use LogicException;


$app->group('/roles', function (RouteCollectorProxy $group) {

    $group->get('/all', 'getAllRoles' );
    $group->post('/persons/assign', 'rolePersonAssign' );

});

function getAllRoles (Request $request, Response $response, array $args) {
  $roles = ListOptionQuery::create()->getFamilyRoles();
  $roles = $roles->toArray();
  return $response->withJson($roles);
}

function rolePersonAssign (Request $request, Response $response, array $args) {
  $data = (object)$request->getParsedBody();

  $personId = empty($data->personId) ? null : $data->personId;
  $roleId = empty($data->roleId) ? null : $data->roleId;

  $per_fam_ID = PersonQuery::Create()->findOneById($personId)->getFamId();

  if ( !(SessionUser::getUser()->isEditRecordsEnabled() || $personId == SessionUser::getUser()->getPersonId() || $per_fam_ID == SessionUser::getUser()->getPerson()->getFamId() ) ) {
      return $response->withStatus(401);
  }

  $person = PersonQuery::create()->findPk($personId);
  $role = ListOptionQuery::create()
      ->filterByOptionId($roleId)
      ->findOne();

  if (!$person || !$role) {
      return $response->withStatus(404, gettext('The record could not be found.'));
  }

  if ($person->getFmrId() == $roleId) {
      return $response->withJson(['success' => true, 'msg' => gettext('The role is already assigned.')]);
  }

  $person->setFmrId($role->getOptionId());
  if ($person->save()) {
      return $response->withJson(['success' => true, 'msg' => gettext('The role is successfully assigned.')]);
  } else {
      throw new LogicException(gettext('The role could not be assigned.'));
  }


}
