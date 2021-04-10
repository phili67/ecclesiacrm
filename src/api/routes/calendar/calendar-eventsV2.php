<?php

/*******************************************************************************
 *
 *  filename    : events.php
 *  last change : 2020-01-26
 *  description : manage the full calendar with events
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *  Updated : 2020/05/8
 *
 ******************************************************************************/

use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\CalendarEventV2Controller;

$app->group('/events', function (RouteCollectorProxy $group) {

    /*
      * @! Get all events for all calendars for a specified range
      */
    $group->get('/', CalendarEventV2Controller::class . ":getAllEvents" );
    /*
      * @! Get all events after now
      */
    $group->get('/notDone', CalendarEventV2Controller::class . ":getNotDoneEvents" );
    /*
     * @! Get all events from today
     */
    $group->get('/numbers', CalendarEventV2Controller::class . ":numbersOfEventOfToday") ;
    /*
     * @! Get all event type
     */
    $group->get('/types', CalendarEventV2Controller::class . ":getEventTypes" );
    /*
     * @! Get all event names
     */
    $group->get('/names', CalendarEventV2Controller::class . ":eventNames" );
    /*
     * @! delete event type
     * #! param: id->int  :: type ID
     */
    $group->post('/deleteeventtype', CalendarEventV2Controller::class . ":deleteeventtype" );
    /*
     * @! get event info
     * #! param: id->int  :: event ID
     */
    $group->post('/info', CalendarEventV2Controller::class . ":eventInfo" );
    /*
    * @! Set a person for the event + check
    * #! param: id->int  :: event ID
    * #! param: id->int  :: person ID
    */
    $group->post('/person', CalendarEventV2Controller::class . ":personCheckIn" );
    /*
    * @! Set the group persons for the event + check
    * #! param: id->int  :: event ID
    * #! param: id->int  :: group ID
    */
    $group->post('/group', CalendarEventV2Controller::class . ":groupCheckIn" );
    /*
    * @! Set the family persons for the event + check
    * #! param: id->int  :: event ID
    * #! param: id->int  :: family ID
    */
    $group->post('/family', CalendarEventV2Controller::class . ":familyCheckIn" );
    /*
    * @! get event count
    * #! param: id->int  :: event ID
    * #! param: id->int  :: type ID
    */
    $group->post('/attendees', CalendarEventV2Controller::class . ":eventCount" );
    /*
    * @! manage an event eventAction, [createEvent,moveEvent,resizeEvent,attendeesCheckinEvent,suppress,modifyEvent]
    * #! param: id->int       :: eventID
    * #! param: id->int       :: type ID
    * #! param: ref->array    :: calendarID
    * #! param: id->int       :: reccurenceID
    * #! param: ref->start    :: the start date : YYYY-MM-DD
    * #! param: ref->start    :: the end date : YYYY-MM-DD
    * #! param: ref->location :: location
    */
    $group->post('/', CalendarEventV2Controller::class . ":manageEvent" );

});
