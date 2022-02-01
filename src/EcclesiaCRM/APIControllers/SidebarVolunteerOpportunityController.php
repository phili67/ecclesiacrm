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

    public function getAllVolunteerOpportunities(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isCanvasserEnabled() && SessionUser::getUser()->isMenuOptionsEnabled())) {
            return $response->withStatus(401);
        }

        $volunteerOpportunities = VolunteerOpportunityQuery::Create()->orderByOrder(Criteria::ASC)->find();

        $arr = $volunteerOpportunities->toArray();

        $res = "";
        $place = 0;

        $count = count($arr);

        foreach ($arr as $elt) {
            $new_elt = "{";
            foreach ($elt as $key => $value) {
                $new_elt .= "\"" . $key . "\":" . json_encode($value) . ",";
            }

            $place++;

            if ($place == 1 && $count != 1) {
                $position = "first";
            } else if ($place == $count && $count != 1) {
                $position = "last";
            } else if ($count != 1) {
                $position = "intermediate";
            } else {
                $position = "none";
            }

            $res .= $new_elt . "\"place\":\"" . $position . "\",\"realplace\":\"" . $place . "\"},";
        }

        return $response->write('{"VolunteerOpportunities":[' . substr($res, 0, -1) . "]}");
    }

    public function deleteVolunteerOpportunity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->id) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->id);
            $place = $vo->getOrder();

            if (!is_null($vo)) {
                $vo->delete();
            }

            $vos = VolunteerOpportunityQuery::Create()->find();
            $count = $vos->count();

            for ($i = $place + 1; $i <= $count + 1; $i++) {
                $vo = VolunteerOpportunityQuery::Create()->findOneByOrder($i);
                if (!is_null($vo)) {
                    $vo->setOrder($i - 1);
                    $vo->save();
                }
            }

            return $response->withJson(['success' => $count]);

        }

        return $response->withJson(['success' => false]);
    }

    public function upActionVolunteerOpportunity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->id) && isset ($input->place) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
            $firstVO = VolunteerOpportunityQuery::Create()->findOneByOrder($input->place - 1);
            $firstVO->setOrder($input->place)->save();

            $secondVO = VolunteerOpportunityQuery::Create()->findOneById($input->id);
            $secondVO->setOrder($input->place - 1)->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function downActionVolunteerOpportunity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->id) && isset ($input->place) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
            $firstVO = VolunteerOpportunityQuery::Create()->findOneByOrder($input->place + 1);
            $firstVO->setOrder($input->place)->save();

            $secondVO = VolunteerOpportunityQuery::Create()->findOneById($input->id);
            $secondVO->setOrder($input->place + 1)->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function createVolunteerOpportunity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->Name) && isset ($input->desc) && isset ($input->state) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $volunteerOpportunities = VolunteerOpportunityQuery::Create()->orderByOrder(Criteria::DESC)->find();

            $place = 1;

            foreach ($volunteerOpportunities as $volunteerOpportunity) {// get the last Order !!!
                $place = $volunteerOpportunity->getOrder() + 1;
                break;
            }

            $vo = new VolunteerOpportunity();

            $vo->setName($input->Name);
            $vo->setDescription($input->desc);
            $vo->setActive(($input->state)?1:0);
            $vo->setOrder($place);

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
}
