<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/09/10
//


namespace Plugins\APIControllers;

use EcclesiaCRM\Utils\InputUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\OutputUtils;

spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\\Service', '\\'), array(__DIR__.'/../../core/Service', '/'), $className) . '.php';
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../model', '/'), $className) . '.php';
});

use PluginStore\NewsDashboardQuery;
use PluginStore\NewsDashboard;

use Plugins\Service\NewsDashboardService;

class NewsDashboardController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function getAllNews()
    {
        $newsAll = NewsDashboardQuery::create()
            ->find();

        $res = [];

        foreach ($newsAll as $newsSimple) {
            $res[] = [
                'Id' => $newsSimple->getId(),
                'Title' => $newsSimple->getTitle(),
                'Text' => $newsSimple->getText(),
                'Img' => NewsDashboardService::getImage($newsSimple->getType()),
                'Date' => OutputUtils::change_date_for_place_holder($newsSimple->getDateentered()->format('Y-m-d'))
            ];
        }

        return $res;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if ( isset($input->userID) and isset($input->title) and isset($input->text) and isset($input->type)) {
            $news = new NewsDashboard();

            $news->setUserId(SessionUser::getId());
            $news->setDateentered(new \DateTime('now'));
            $news->setDatelastedited(new \DateTime('now'));
            $news->setTitle(InputUtils::FilterHTML($input->title));
            $news->setText(InputUtils::FilterHTML($input->text));
            $news->setType($input->type);

            $news->save();

            return $response->withJson(['status' => "success", 'items' => $this->getAllNews()]);
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if ( isset($input->userID) and isset($input->title) and isset($input->text) and isset($input->type) and isset($input->newsID)) {

            $news = NewsDashboardQuery::create()
                ->findOneById($input->newsID);

            if (!is_null($news)) {

                $news->setUserId(SessionUser::getId());
                $news->setDateentered(new \DateTime('now'));
                $news->setDatelastedited(new \DateTime('now'));
                $news->setTitle(InputUtils::FilterHTML($input->title));
                $news->setText(InputUtils::FilterHTML($input->text));
                $news->setType($input->type);

                $news->save();


                return $response->withJson(['status' => "success", 'items' => $this->getAllNews()]);
            }
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function remove(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if ( isset($input->newsID) ) {
            $news = NewsDashboardQuery::create()
                ->findOneById($input->newsID);

            if ( !is_null($news) ) {
                $news->delete();

                return $response->withJson(['status' => "success", 'items' => $this->getAllNews()]);
            }
        }

        return $response->withJson(['status' => "failed"]);
    }

    public function info(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->newsID)) {
            $news = NewsDashboardQuery::create()
                ->findOneById($input->newsID);

            // OutputUtils::change_date_for_place_holder($note->getDateentered()->format('Y-m-d'))

            return $response->withJson(['status' => "success", "note" => $news->toArray()]);
        }

        return $response->withJson(['status' => "failed"]);
    }
}
