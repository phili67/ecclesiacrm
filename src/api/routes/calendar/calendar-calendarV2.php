<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//  updated : 2018/05/13
//


use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\APIControllers\CalendarV2Controller;

$app->group('/calendar', function (RouteCollectorProxy $group) {

    /*
     * @! Get all events for all calendars for a specified range
     * #! param: ref->start :: the start date : YYYY-MM-DD
     * #! param: ref->end   :: the end date : YYYY-MM-DD
     */
    $group->post('/getallevents', CalendarV2Controller::class . ':getallCalendarEvents' );
    /*
     * @! Get all events for all calendars for a specified range
     * #! param: ref->start :: the start date : YYYY-MM-DD
     * #! param: ref->end   :: the end date : YYYY-MM-DD
     */
    $group->post('/getalleventsForEventsList', CalendarV2Controller::class . ':getallCalendarEventsForEventsList' );
    /*
     * @! get all the number of calendar for the current user
     */
    $group->post('/numberofcalendars', CalendarV2Controller::class . ':numberOfCalendars' );
    /*
     * @! Show Hide calendar
     * #! param: ref->array :: calIDs
     * #! param: id->bool   :: isPresent
     */
    $group->post('/showhidecalendars', CalendarV2Controller::class . ':showHideCalendars' );
    /*
     * @! set Description type for a calendar
     * #! param: ref->array  :: calIDs
     * #! param: ref->string :: desc
     * #! param: ref->string :: type
     */
    $group->post('/setDescriptionType', CalendarV2Controller::class . ':setCalendarDescriptionType' );
    /*
     * @! Get all calendars for a specified user
     * #! param: ref->string :: type
     * #! param: ref->bool   :: onlyvisible
     * #! param: ref->bool   :: allCalendars
     */
    $group->post('/getallforuser', CalendarV2Controller::class . ':getAllCalendarsForUser' );
    /*
     * @! Get infos for a calendar
     * #! param: ref->array  :: calIDs
     * #! param: ref->string :: type
     */
    $group->post('/info', CalendarV2Controller::class . ':calendarInfo' );
    /*
    * @! Set color for a calendar
    * #! param: ref->array  :: calIDs
    * #! param: ref->hex    :: color : #FFF
    */
    $group->post('/setcolor', CalendarV2Controller::class . ':setCalendarColor' );
    /*
    * @! Check the calendar to make it visible
    * #! param: ref->array  :: calIDs
    * #! param: ref->bool   :: isChecked
    */
    $group->post('/setckecked', CalendarV2Controller::class . ':setCheckedCalendar' );
    /*
     * @! Create a new calendar
     * #! param: ref->string  :: title
     */
    $group->post('/new', CalendarV2Controller::class . ':newCalendar' );
    /*
    * @! Create new calendar reservation
    * #! param: ref->string :: title
    * #! param: ref->string :: type
    * #! param: ref->string :: desc
    */
    $group->post('/newReservation', CalendarV2Controller::class . ':newCalendarReservation' );
    /*
    * @! Change calendar name
    * #! param: ref->array  :: calIDs
    * #! param: ref->string :: title
    */
    $group->post('/modifyname', CalendarV2Controller::class . ':modifyCalendarName' );
    /*
    * @! get attendees for a calendar
    * #! param: ref->array  :: calIDs
    */
    $group->post('/getinvites', CalendarV2Controller::class . ':getCalendarInvites' );
    /*
    * @! Delete a share calendar for a person
    * #! param: ref->array  :: calIDs
    * #! param: ref->int    :: principal
    */
    $group->post('/sharedelete', CalendarV2Controller::class . ':shareCalendarDelete' );
    /*
    * @! Share a calendar with a person
    * #! param: ref->array  :: calIDs
    * #! param: id->int     :: person ID
    * #! param: ref->bool   :: notification
    */
    $group->post('/shareperson', CalendarV2Controller::class . ':shareCalendarPerson');
    /*
    * @! Share a calendar with a person
    * #! param: ref->array  :: calIDs
    * #! param: id->int     :: family ID
    * #! param: ref->bool   :: notification
    */
    $group->post('/sharefamily', CalendarV2Controller::class . ':shareCalendarFamily' );
    /*
   * @! Share a calendar with an entire group
   * #! param: ref->array  :: calIDs
   * #! param: id->int     :: group ID
   * #! param: ref->bool   :: notification
   */
    $group->post('/sharegroup', CalendarV2Controller::class . ':shareCalendarGroup' );
    /*
   * @! Share a calendar with an entire group
   * #! param: ref->array  :: calIDs
   */
    $group->post('/sharestop', CalendarV2Controller::class . ':shareCalendarStop');
    /*
   * @! Set right access for a calendar
   * #! param: ref->array  :: calIDs
   * #! param: ref->int    :: principal
   * #! param: ref->int    :: rightAccess
   */
    $group->post('/setrights', CalendarV2Controller::class . ':setCalendarRights' );
    /*
   * @! Delete a calendar
   * #! param: ref->array  :: calIDs
   */
    $group->post('/delete', CalendarV2Controller::class . ':deleteCalendar' );

});
