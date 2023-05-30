<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2023/05/19
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Propel\Runtime\Propel;

use EcclesiaCRM\dto\SystemURLs;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

use Slim\Views\PhpRenderer;

class VIEWQueryController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function querylist (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/query/');

        //Set the page title
        if (!SessionUser::getUser()->isShowMenuQueryEnabled()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'querylist.php', $this->argumentsQueryListArray());
    }

    public function argumentsQueryListArray ()
    {
        //Set the page title
        $sPageTitle    = _('Query Listing');
        

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $sSQL = 'SELECT * FROM query_qry LEFT JOIN query_type ON query_qry.qry_Type_ID = query_type.qry_type_id ORDER BY query_qry.qry_Type_ID, query_qry.qry_Name';

        $connection = Propel::getConnection();
        $statement = $connection->prepare($sSQL);
        $statement->execute();

        $aFinanceQueries = explode(',', SystemConfig::getValue('aFinanceQueries'));

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'aFinanceQueries'           => $aFinanceQueries,
            'connection'                => $connection,
            'statement'                 => $statement
        ];

        return $paramsArguments;
    }
}
