<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PastoralCare;
use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\PastoralCareType;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\Map\PastoralCareTableMap;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/pastoralcare', function () {
    $this->get('/{personId:[0-9]+}', 'renderPastoralCare');
});


function renderPastoralCare (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/people/');
    
    $personId = $args['personId'];

    if ( !( SessionUser::getUser()->isPastoralCareEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }
    
    return $renderer->render($response, 'pastoralcare.php', argumentsPastoralListArray($personId));
}

function argumentsPastoralListArray ($currentPersonID=0)
{
    $currentPastorId = SessionUser::getUser()->getPerson()->getID();
    
    $linkBack = $sRootDocument."/PersonView.php?PersonID=".$currentPersonID;

    $ormPastoralCares = PastoralCareQuery::Create()
                          ->orderByDate(Propel\Runtime\ActiveQuery\Criteria::DESC)
                          ->leftJoinWithPastoralCareType()
                          ->findByPersonId($currentPersonID);
                      
    $ormPastors = PastoralCareQuery::Create()
                          ->groupBy(PastoralCareTableMap::COL_PST_CR_PASTOR_ID)
                          ->orderByPastorName(Propel\Runtime\ActiveQuery\Criteria::DESC)
                          ->findByPersonId($currentPersonID);
                      
    $ormPastoralTypeCares = PastoralCareTypeQuery::Create()
                          ->find();

    //Get name
    $person = PersonQuery::Create()->findOneById ($currentPersonID);

    $sPageTitle = gettext("Pastoral care for")."  : \"".$person->getFullName()."\"";
    
    $sRootDocument   = SystemURLs::getDocumentRoot();
    $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
    $sCSPNonce       = SystemURLs::getCSPNonce();
          
    $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
                       'sRootDocument'        => $sRootDocument,
                       'linkBack'             => $linkBack,
                       'sPageTitle'           => $sPageTitle,
                       'ormPastoralCares'     => $ormPastoralCares,
                       'currentPersonID'      => $currentPersonID,
                       'currentPastorId'      => $currentPastorId,
                       'ormPastors'           => $ormPastors,
                       'ormPastoralTypeCares' => $ormPastoralTypeCares,
                       'person'               => $person,
                       'sDateFormatLong'      => $sDateFormatLong,
                       'sCSPNonce'            => $sCSPNonce
                       ];   
   return $paramsArguments;
}