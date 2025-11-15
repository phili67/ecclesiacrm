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
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\VolunteerOpportunityQuery;
use EcclesiaCRM\VolunteerOpportunity;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;

use Propel\Runtime\Propel;
use PDO;

class VolunteerOpportunityController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function selectMenuParents($menus, $volID, $parentId = NULL)
    {
        $res = '<select class="form-control form-control-sm selectHierarchy" data-id="'.$volID.'">\n';
        $res .= '<option value="-1">--'._("None").'--</option>';

        foreach ($menus as $menu) {
            if ($menu['vol_ID'] != $volID) {
                $res .= '<option value="' . $menu['vol_ID'] . '" '.(($parentId != NULL and $parentId == $menu['vol_ID'])?'selected':''). '>' . $menu['vol_Name'] . '</option>';
            }
        }
        $res .= '</select>';

        return $res;
    }

    private function selectMenuIcons($volID, $icon)
    {
        $connection = Propel::getConnection();

        $result = $connection->query("SHOW COLUMNS FROM `volunteeropportunity_vol` LIKE 'vol_icon'");

        $res = '<select class="form-control form-control-sm selectIcon" data-id="'.$volID.'">\n';

        if ($result) {
            $arr = $result->fetch(PDO::FETCH_ASSOC)['Type'];
            $option_array = explode("','", preg_replace("/(enum|set)\('(.+?)'\)/", "\\2", $arr));

            foreach ($option_array as $item) {
                $res .= '<option value="' . $item . '" '.($icon == $item?'selected':''). '>' . $item . '</option>';
            }
        }
        $res .= '</select>';

        return $res;
    }

    private function selectMenuColors($volID, $icon)
    {
        $connection = Propel::getConnection();

        $result = $connection->query("SHOW COLUMNS FROM `volunteeropportunity_vol` LIKE 'vol_color'");

        $res = '<select class="form-control form-control-sm selectColor" data-id="'.$volID.'">\n';

        if ($result) {
            $arr = $result->fetch(PDO::FETCH_ASSOC)['Type'];
            $option_array = explode("','", preg_replace("/(enum|set)\('(.+?)'\)/", "\\2", $arr));

            foreach ($option_array as $item) {
                $res .= '<option value="' . $item . '" '.($icon == $item?'selected':''). '>' . $item . '</option>';
            }
        }
        $res .= '</select>';

        return $res;
    }

    public function getAllVolunteerOpportunities(ServerRequest $request, Response $response, array $args): Response
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
                'MenuParents' => $this->selectMenuParents($menus, $volunteerOpportunity->getId(), $volunteerOpportunity->getParentId()),
                'MenuIcons' => $this->selectMenuIcons( $volunteerOpportunity->getId(), $volunteerOpportunity->getIcon() ),
                'MenuColors' => $this->selectMenuColors( $volunteerOpportunity->getId(), $volunteerOpportunity->getColor() )
            ];

            $res[] = $elt;
        }


        return $response->withJson(["VolunteerOpportunities" => $res]);
    }

    public function deleteVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
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

    public function createVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
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

    public function setVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
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

    public function editVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->id) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            return $response->write(VolunteerOpportunityQuery::Create()->findOneById($input->id)->toJSON());
        }

        return $response->withJson(['success' => false]);
    }

    public function changeParentVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
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

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function changeIconVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->voldId) && isset($input->iconId) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->voldId);

            $vo->setIcon($input->iconId);

            $vo->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function changeColorVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->voldId) && isset($input->colId) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->voldId);

            $vo->setColor($input->colId);

            $vo->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function getPersons(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->voldId) && isset($input->colId) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->voldId);

            $vo->setColor($input->colId);

            $vo->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    

}
