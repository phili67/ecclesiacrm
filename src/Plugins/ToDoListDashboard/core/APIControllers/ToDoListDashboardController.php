<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/09/18
//


namespace Plugins\APIControllers;

use EcclesiaCRM\Utils\InputUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\SessionUser;

spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\\Service', '\\'), array(__DIR__.'/../../core/Service', '/'), $className) . '.php';
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../model', '/'), $className) . '.php';
});

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

            $tdl->setName(InputUtils::FilterHTML($input->name));
            $tdl->setUserId(SessionUser::getId());
            $tdl->setVisible(true);

            $tdl->save();

            return $response->withJson(['status' => "success", 'ListId' => $tdl->getId()]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function ListInfo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->listID)) {
            // first all other aren't yet visible
            $list = ToDoListDashboardQuery::create()
                ->findOneById($input->listID);

            return $response->withJson(['status' => "success", 'Name' => $list->getName()]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function modifyList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->ListID) and isset($input->Name)) {
            // first all other aren't yet visible
            $list = ToDoListDashboardQuery::create()
                ->findOneById($input->ListID);

            if ( !is_null($list) ) {
                $list->setName(InputUtils::FilterHTML($input->Name));
                $list->save();
            }

            return $response->withJson(['status' => "success", 'Name' => $list->getName()]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function removeList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->ListID)) {
            // first all other aren't yet visible
            $list = ToDoListDashboardQuery::create()
                ->findOneById($input->ListID);

            if ( !is_null($list) ) {
                $list->delete();
            }


            // we select a list by default
            $list = ToDoListDashboardQuery::create()
                ->filterByUserId(SessionUser::getId())
                ->findOne();

            $res = [];

            if ( !is_null($list) ) {
                $selectedListId = $list->getId();

                $lists = ToDoListDashboardQuery::create()
                    ->filterByUserId(SessionUser::getId())
                    ->find();

                foreach ($lists as $list) {
                    if ($list->getId() == $selectedListId) {
                        $list->setVisible(true);
                    } else {
                        $list->setVisible(false);
                    }

                    $list->save();
                }


                // now we extract the datas of the current list
                $items = ToDoListDashboardItemQuery::create()
                    ->orderByPlace()
                    ->findByList($selectedListId);

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
            } else {
                $selectedListId = -1;
            }

            return $response->withJson(['status' => "success", 'ListId' => $selectedListId, 'items' => $res]);
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
                ->orderByPlace()
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
            // now we reload all the datas
            $items = ToDoListDashboardItemQuery::create()
                ->orderByPlace()
                ->findByList($input->ListId);


            $item = new ToDoListDashboardItem();

            $item->setName(InputUtils::FilterHTML($input->name));
            $item->setDateTime($input->DateTime);
            $item->setList($input->ListId);
            $item->setChecked(false);
            $item->setPlace($items->count());

            $item->save();

            // now we reload all the datas
            $items = ToDoListDashboardItemQuery::create()
                ->orderByPlace()
                ->findByList($input->ListId);

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

    public function checkListItem (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    public function changeListItemsOrder (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->list)) {

            $place = 0;

            foreach ($input->list as $itemId) {
                $item = ToDoListDashboardItemQuery::create()
                    ->findOneById($itemId);

                $item->setPlace($place++);

                $item->save();
            }

            return $response->withJson(['status' => "success"]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function deleteListItem(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if ( isset($input->ItemID) and isset($input->ListId) ) {
            // now we reload all the datas
            $item = ToDoListDashboardItemQuery::create()
                ->findOneById($input->ItemID);

            if (!is_null($input)) {
                $item->delete();
            }

            // now we reload all the datas
            $items = ToDoListDashboardItemQuery::create()
                ->orderByPlace()
                ->findByList($input->ListId);

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

    public function ListItemInfo (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if ( isset($input->ItemID) ) {
            // now we reload all the datas
            $item = ToDoListDashboardItemQuery::create()
                ->findOneById($input->ItemID);

            return $response->withJson(['status' => "success", "name" => $item->getName(), "date" => $item->getDateTime()->format('Y-m-d H:i:s')]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function modifyListItem (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if ( isset($input->ListId) and isset($input->ItemID) and isset($input->Name) and isset($input->DateTime)) {
            // now we reload all the datas
            $item = ToDoListDashboardItemQuery::create()
                ->findOneById($input->ItemID);

            $item->setName(InputUtils::FilterHTML(input->Name));
            $item->setDateTime($input->DateTime);

            $item->save();

            // now we reload all the datas
            $items = ToDoListDashboardItemQuery::create()
                ->orderByPlace()
                ->findByList($input->ListId);

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
}
