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
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use Propel\Runtime\Propel;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\InputUtils;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

use Slim\Views\PhpRenderer;

class VIEWQueryController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function querylist (ServerRequest $request, Response $response, array $args): Response
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

    public function queryview (ServerRequest $request, Response $response, array $args): Response
    {
        $renderer = new PhpRenderer('templates/query/');

        //Set the page title
        if (!SessionUser::getUser()->isShowMenuQueryEnabled()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $iQueryID = $args['queryID'];

        $aFinanceQueries = explode(',', SystemConfig::getValue('aFinanceQueries'));

        if (!(SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance')) && in_array($iQueryID, $aFinanceQueries)) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'queryview.php', $this->argumentsQueryViewArray($iQueryID, $aFinanceQueries));
    }

    public function argumentsQueryViewArray ($iQueryID, $aFinanceQueries)
    {
        //Set the page title
        $sPageTitle    = _('Query View');
        

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'iQueryID'                  => $iQueryID,
            'aFinanceQueries'           => $aFinanceQueries
        ];

        return $paramsArguments;
    }

    public function querysql (ServerRequest $request, Response $response, array $args): Response
    {
        $renderer = new PhpRenderer('templates/query/');

        //Set the page title
        if (!SessionUser::getUser()->isAdmin()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'querysql.php', $this->argumentsQuerySQLArray());
    }

    public function argumentsQuerySQLArray ()
    {
        //Set the page title
        $sPageTitle    = _('Free-Text Query');
        

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle
        ];

        return $paramsArguments;
    }

    
}
