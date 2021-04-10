<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\SessionUser;

class SidebarRolesController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAllRoles(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $roles = ListOptionQuery::create()->getFamilyRoles();
        $roles = $roles->toArray();
        return $response->withJson($roles);
    }

    public function rolePersonAssign(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $data = (object)$request->getParsedBody();

        $personId = empty($data->personId) ? null : $data->personId;
        $roleId = empty($data->roleId) ? null : $data->roleId;

        $per_fam_ID = PersonQuery::Create()->findOneById($personId)->getFamId();

        if (!(SessionUser::getUser()->isEditRecordsEnabled() || $personId == SessionUser::getUser()->getPersonId() || $per_fam_ID == SessionUser::getUser()->getPerson()->getFamId())) {
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
            return $response->withStatus(404, gettext('The role could not be assigned.'));
        }

        return $response->withStatus(404, gettext("Error"));
    }
}
