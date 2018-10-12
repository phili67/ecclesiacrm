<?php

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\PersonQuery;
use LogicException;


$app->group('/roles', function () {
    
    $this->get('/all', function ($request, $response, $args) {
        $roles = ListOptionQuery::create()->getFamilyRoles();
        $roles = $roles->toArray();
        return $response->withJson($roles);
    });
    
    
    $this->post('/persons/assign', function ($request, $response, $args) {
        $data = (object)$request->getParsedBody();
        
        $personId = empty($data->personId) ? null : $data->personId;
        $roleId = empty($data->roleId) ? null : $data->roleId;
        
        $per_fam_ID = PersonQuery::Create()->findOneById($personId)->getFamId();
        
        if ( !($_SESSION['user']->isEditRecordsEnabled() || $personId == $_SESSION['user']->getPersonId() || $per_fam_ID == $_SESSION['user']->getPerson()->getFamId() ) ) {
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
        
        
    });
    
    
    
});
