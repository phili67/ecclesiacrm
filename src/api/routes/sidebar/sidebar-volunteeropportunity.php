<?php
/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2018-07-11
 *  description : manage the Pastoral Care
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorizaion
 *  Updated : 2018-07-13
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\SidebarVolunteerOpportunityController;

$app->group('/volunteeropportunity', function (RouteCollectorProxy $group) {

  $group->post('/', SidebarVolunteerOpportunityController::class . ':getAllVolunteerOpportunities' );
  $group->post('/delete', SidebarVolunteerOpportunityController::class . ':deleteVolunteerOpportunity' );
  $group->post('/create', SidebarVolunteerOpportunityController::class . ':createVolunteerOpportunity' );
  $group->post('/set', SidebarVolunteerOpportunityController::class . ':setVolunteerOpportunity' );
  $group->post('/edit', SidebarVolunteerOpportunityController::class . ':editVolunteerOpportunity' );
  $group->post('/changeParent', SidebarVolunteerOpportunityController::class . ':changeParentVolunteerOpportunity' );

});
