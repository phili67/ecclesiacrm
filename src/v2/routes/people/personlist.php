<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2019-06-16
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\PledgeQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;


use Slim\Views\PhpRenderer;

$app->group('/personlist', function () {
    $this->get('', 'renderPersonList' );
    $this->get('/', 'renderPersonList' );
    $this->get('/{mode}', 'renderPersonList' );
});


function renderPersonList (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/people/');

    $sMode = $args['mode'];

    if ( !( SessionUser::getUser()->isEditRecordsEnabled()
      || (strtolower($sMode)  == 'gdrp' && SessionUser::getUser()->isGdrpDpoEnabled())
      || (strtolower($sMode) == 'inactive' && SessionUser::getUser()->isEditRecordsEnabled())
          )
       ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'personlist.php', argumentsPersonListArray($sMode));
}

function argumentsPersonListArray ($sMode='Active')
{
    if (strtolower($sMode) == 'gdrp') {
       $time = new \DateTime('now');
       $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

       $persons = PersonQuery::create()
                ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP, when a person is completely deactivated
                ->_or() // or : this part is unusefull, it's only for debugging
                ->useFamilyQuery()
                  ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP, when a Family is completely deactivated
                ->endUse()
                ->orderByLastName()
                ->find();

    } else if (strtolower($sMode) == 'inactive') {
      if (SystemConfig::getValue('bGDPR')) {
        $time = new \DateTime('now');
        $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

        $persons = PersonQuery::create()
                ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)// GDRP, when a person isn't under GDRP but deactivated, we only can see the person who are over a certain date
                ->_or()// this part is unusefull, it's only for debugging
                ->useFamilyQuery()
                  ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)// GDRP, when a Family is completely deactivated
                ->endUse()
                ->orderByLastName()
                ->find();
      } else {
        $time = new \DateTime('now');

        $persons = PersonQuery::create()
                ->filterByDateDeactivated($time, Criteria::LESS_EQUAL)
                ->orderByLastName()
                ->find();
      }
    } else {
      $sMode = 'Active';
      $persons = PersonQuery::create()
              ->filterByDateDeactivated(null)
              ->orderByLastName()
              ->find();
    }

    $sPageTitle = _(ucfirst(_($sMode))) . ' : ' . _('Person List');

    $sRootDocument   = SystemURLs::getDocumentRoot();
    $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
    $sCSPNonce       = SystemURLs::getCSPNonce();

    $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
                       'sRootDocument'        => $sRootDocument,
                       'sPageTitle'           => $sPageTitle,
                       'sMode'                => $sMode,
                       'sCSPNonce'            => $sCSPNonce,
                       'persons'              => $persons,
                       'bNotGDRP'             => SessionUser::getUser()->isAddRecordsEnabled() && strtolower($sMode) != 'gdrp'
                       ];

   return $paramsArguments;
}
