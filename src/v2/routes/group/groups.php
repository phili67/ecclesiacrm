<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\dto\SystemURLs;

use Slim\Views\PhpRenderer;

$app->group('/group', function () {
    $this->get('/list', 'groupList');
});


function groupList (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/group/');

    return $renderer->render($response, 'grouplist.php', renderGroupListArray());
}

function renderGroupListArray ()
{
    $rsGroupTypes = ListOptionQuery::create()->filterById('3')->find();

    //Set the page title
    $sPageTitle = _("Group Listing");

    $sRootDocument  = SystemURLs::getDocumentRoot();
    $CSPNonce = SystemURLs::getCSPNonce();

    $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
        'sRootDocument' => $sRootDocument,
        'sPageTitle'    => $sPageTitle,
        'CSPNonce'      => $CSPNonce,
        'rsGroupTypes'  => $rsGroupTypes];

    return $paramsArguments;
}