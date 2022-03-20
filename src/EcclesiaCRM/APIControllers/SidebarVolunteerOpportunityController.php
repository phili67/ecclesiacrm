<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\Utils\LoggerUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\VolunteerOpportunityQuery;
use EcclesiaCRM\VolunteerOpportunity;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;

class SidebarVolunteerOpportunityController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function selectMenu($menus, $volID, $parentId = NULL)
    {
        $res = '<select class="form-control selectHierarchy" data-id="'.$volID.'">\n';
        $res .= '<option value="-1">--'._("None").'--</option>';

        LoggerUtils::getAppLogger()->info(print_r($menus, true));

        foreach ($menus as $menu) {
            if ($menu['vol_ID'] != $volID) {
                $res .= '<option value="' . $menu['vol_ID'] . '" '.(($parentId != NULL and $parentId == $menu['vol_ID'])?'selected':''). '>' . $menu['vol_Name'] . '</option>';
            }
        }
        $res .= '</select>';

        return $res;
    }

    public function getAllVolunteerOpportunities(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isCanvasserEnabled() && SessionUser::getUser()->isMenuOptionsEnabled())) {
            return $response->withStatus(401);
        }

        $volunteerOpportunities = VolunteerOpportunityQuery::Create()->orderByName(Criteria::ASC)->find();

        $volunteerOpportunitiesMenu = VolunteerOpportunityQuery::Create()
            ->select(['vol_ID', 'vol_Name'])
            ->orderByName(Criteria::ASC)->find();

        $menus = $volunteerOpportunitiesMenu->toArray();

        $res = [];

        foreach ($volunteerOpportunities as $volunteerOpportunity) {
            $elt = [
                'Id' => $volunteerOpportunity->getId(),
                'Active' => $volunteerOpportunity->getActive(),
                'Name' => $volunteerOpportunity->getName(),
                'Description' => $volunteerOpportunity->getDescription(),
                'ParentId' => $volunteerOpportunity->getParentId(),
                'Menu' => $this->selectMenu($menus, $volunteerOpportunity->getId(), $volunteerOpportunity->getParentId())
            ];

            $res[] = $elt;
        }


        return $response->withJson(["VolunteerOpportunities" => $res]);
    }

    public function deleteVolunteerOpportunity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->id) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->id);

            if (!is_null($vo)) {
                $vo->delete();
            }

            return $response->withJson(['success' => true]);

        }

        return $response->withJson(['success' => false]);
    }

    public function createVolunteerOpportunity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->Name) && isset ($input->desc) && isset ($input->state) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = new VolunteerOpportunity();

            $vo->setName($input->Name);
            $vo->setDescription($input->desc);
            $vo->setActive(($input->state)?1:0);

            $vo->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function setVolunteerOpportunity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->id) && isset ($input->Name) && isset ($input->desc) && isset ($input->state) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {

            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->id);

            $vo->setName($input->Name);
            $vo->setDescription($input->desc);
            $vo->setActive(($input->state)?1:0);

            $vo->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function editVolunteerOpportunity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->id) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            return $response->write(VolunteerOpportunityQuery::Create()->findOneById($input->id)->toJSON());
        }

        return $response->withJson(['success' => false]);
    }

    public function changeParentVolunteerOpportunity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->voldId) && isset($input->parentId) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->voldId);

            if ($input->parentId == -1) {
                $vo->setParentId(NULL);
            } else {
                $vo->setParentId($input->parentId);
            }

            $vo->save();
        }

        return $response->withJson(['success' => false]);
    }


}
