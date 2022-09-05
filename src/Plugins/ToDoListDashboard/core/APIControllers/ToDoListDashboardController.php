<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/09/18
//


namespace Plugins\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\SessionUser;

use PluginStore\ToDoListDashboard;
use PluginStore\ToDoListDashboardQuery;
use PluginStore\ToDoListDashboardItem;
use PluginStore\ToDoListDashboardItemQuery;

use Plugins\Service\ToDoListDashboardService;

class ToDoListDashboardController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->name)) {
            // first all other aren't yet visible
            $lists = ToDoListDashboardQuery::create()
                ->filterByUserId(SessionUser::getId())
                ->find();

            foreach ($lists as $list) {
                $list->setVisible(false);
                $list->save();
            }

            // we create a new list that is only visible
            $tdl = new ToDoListDashboard();

            $tdl->setName($input->name);
            $tdl->setUserId(SessionUser::getId());
            $tdl->setVisible(true);

            $tdl->save();

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function changeList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->id)) {
            // first all other aren't yet visible
            $lists = ToDoListDashboardQuery::create()
                ->filterByUserId(SessionUser::getId())
                ->find();

            foreach ($lists as $list) {
                $list->setVisible((($list->getId() == $input->id) ? true : false));
                $list->save();
            }

            $items = ToDoListDashboardItemQuery::create()
                ->findByList($input->id);

            $res = [];

            foreach ($items as $item) {
                $date = $item->getDateTime();

                $periodTime = ToDoListDashboardService::getColorPeriod($date);

                $res[] = [
                    'Id' => $item->getId(),
                    'Checked' => $item->getChecked(),
                    'Name' => $item->getName(),
                    'time' => $periodTime['time'],
                    'date' => $date->format('Y-m-d H:i:s'),
                    'color' => $periodTime['color'],
                    'period' => $periodTime['period']
                ];
            }

            return $response->withJson(['status' => "success", "items" => $res]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function addListItem(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->ListId) && isset($input->name) && isset($input->DateTime)) {
            $item = new ToDoListDashboardItem();

            $item->setName($input->name);
            $item->setDateTime($input->DateTime);
            $item->setList($input->ListId);
            $item->setChecked(false);

            $item->save();

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function checkItem(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->id)) {
            $item = ToDoListDashboardItemQuery::create()
                ->findOneById($input->id);

            $item->setChecked($input->checked);

            $item->save();

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }


}
