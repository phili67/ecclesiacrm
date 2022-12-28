# <center><big>Ecclesia**CRM** documentation de l'API</big></center>
----

Ecclesia**CRM** use Slim 4.10.0 which allow to make api call to the restricted area of the CRM.



## API "calendar"

   in route : "/api/routes/calendar/calendar-calendarV2.php

Route | Method | function | Description
------|--------|----------|------------
`/getallevents` | POST | CalendarV2Controller::class . ':getallCalendarEvents' | Get all events for all calendars for a specified range

* `{ref}`->`start` :: the start date : YYYY-MM-DD
* `{ref}`->`end` :: the end date : YYYY-MM-DD

---
Route | Method | function | Description
------|--------|----------|------------
`/getalleventsForEventsList` | POST | CalendarV2Controller::class . ':getallCalendarEventsForEventsList' | Get all events for all calendars for a specified range

* `{ref}`->`start` :: the start date : YYYY-MM-DD
* `{ref}`->`end` :: the end date : YYYY-MM-DD

---
Route | Method | function | Description
------|--------|----------|------------
`/numberofcalendars` | POST | CalendarV2Controller::class . ':numberOfCalendars' | get all the number of calendar for the current user

---
Route | Method | function | Description
------|--------|----------|------------
`/showhidecalendars` | POST | CalendarV2Controller::class . ':showHideCalendars' | Show Hide calendar

* `{ref}`->`array` :: calIDs
* `{id}`->`bool` :: isPresent

---
Route | Method | function | Description
------|--------|----------|------------
`/setDescriptionType` | POST | CalendarV2Controller::class . ':setCalendarDescriptionType' | set Description type for a calendar

* `{ref}`->`array` :: calIDs
* `{ref}`->`string` :: desc
* `{ref}`->`string` :: type

---
Route | Method | function | Description
------|--------|----------|------------
`/getallforuser` | POST | CalendarV2Controller::class . ':getAllCalendarsForUser' | Get all calendars for a specified user

* `{ref}`->`string` :: type
* `{ref}`->`bool` :: onlyvisible
* `{ref}`->`bool` :: allCalendars

---
Route | Method | function | Description
------|--------|----------|------------
`/info` | POST | CalendarV2Controller::class . ':calendarInfo' | Get infos for a calendar

* `{ref}`->`array` :: calIDs
* `{ref}`->`string` :: type

---
Route | Method | function | Description
------|--------|----------|------------
`/setcolor` | POST | CalendarV2Controller::class . ':setCalendarColor' | Set color for a calendar

* `{ref}`->`array` :: calIDs
* `{ref}`->`hex` :: color : #FFF

---
Route | Method | function | Description
------|--------|----------|------------
`/setckecked` | POST | CalendarV2Controller::class . ':setCheckedCalendar' | Check the calendar to make it visible

* `{ref}`->`array` :: calIDs
* `{ref}`->`bool` :: isChecked

---
Route | Method | function | Description
------|--------|----------|------------
`/setckeckedselected` | POST | CalendarV2Controller::class . ':setCheckedSelectedCalendar' | Check the calendar to make it visible

* `{ref}`->`array` :: calIDs
* `{ref}`->`bool` :: isChecked

---
Route | Method | function | Description
------|--------|----------|------------
`/new` | POST | CalendarV2Controller::class . ':newCalendar' | Create a new calendar

* `{ref}`->`string` :: title

---
Route | Method | function | Description
------|--------|----------|------------
`/newReservation` | POST | CalendarV2Controller::class . ':newCalendarReservation' | Create new calendar reservation

* `{ref}`->`string` :: title
* `{ref}`->`string` :: type
* `{ref}`->`string` :: desc

---
Route | Method | function | Description
------|--------|----------|------------
`/modifyname` | POST | CalendarV2Controller::class . ':modifyCalendarName' | Change calendar name

* `{ref}`->`array` :: calIDs
* `{ref}`->`string` :: title

---
Route | Method | function | Description
------|--------|----------|------------
`/getinvites` | POST | CalendarV2Controller::class . ':getCalendarInvites' | get attendees for a calendar

* `{ref}`->`array` :: calIDs

---
Route | Method | function | Description
------|--------|----------|------------
`/sharedelete` | POST | CalendarV2Controller::class . ':shareCalendarDelete' | Delete a share calendar for a person

* `{ref}`->`array` :: calIDs
* `{ref}`->`int` :: principal

---
Route | Method | function | Description
------|--------|----------|------------
`/sharefamily` | POST | CalendarV2Controller::class . ':shareCalendarFamily' | Share a calendar with a person

* `{ref}`->`array` :: calIDs
* `{id}`->`int` :: person ID
* `{ref}`->`bool` :: notification
* `{ref}`->`array` :: calIDs
* `{id}`->`int` :: family ID
* `{ref}`->`bool` :: notification

---
Route | Method | function | Description
------|--------|----------|------------
`/sharegroup` | POST | CalendarV2Controller::class . ':shareCalendarGroup' | Share a calendar with an entire group

* `{ref}`->`array` :: calIDs
* `{id}`->`int` :: group ID
* `{ref}`->`bool` :: notification

---
Route | Method | function | Description
------|--------|----------|------------
`/setrights` | POST | CalendarV2Controller::class . ':setCalendarRights' | Set right access for a calendar

* `{ref}`->`array` :: calIDs
* `{ref}`->`array` :: calIDs
* `{ref}`->`int` :: principal
* `{ref}`->`int` :: rightAccess

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | CalendarV2Controller::class . ':deleteCalendar' | Delete a calendar

* `{ref}`->`array` :: calIDs

---
## API "events"

   in route : "/api/routes/calendar/calendar-eventsV2.php

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | CalendarEventV2Controller::class . ":getAllEvents" | Get all events for all calendars for a specified range

---
Route | Method | function | Description
------|--------|----------|------------
`/notDone` | GET | CalendarEventV2Controller::class . ":getNotDoneEvents" | Get all events after now

---
Route | Method | function | Description
------|--------|----------|------------
`/types` | GET | CalendarEventV2Controller::class . ":getEventTypes" | Get all event type

---
Route | Method | function | Description
------|--------|----------|------------
`/names` | GET | CalendarEventV2Controller::class . ":eventNames" | Get all event names

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteeventtype` | POST | CalendarEventV2Controller::class . ":deleteeventtype" | delete event type

* `{id}`->`int` :: type ID

---
Route | Method | function | Description
------|--------|----------|------------
`/info` | POST | CalendarEventV2Controller::class . ":eventInfo" | get event info

* `{id}`->`int` :: event ID

---
Route | Method | function | Description
------|--------|----------|------------
`/person` | POST | CalendarEventV2Controller::class . ":personCheckIn" | Set a person for the event + check

* `{id}`->`int` :: event ID
* `{id}`->`int` :: person ID

---
Route | Method | function | Description
------|--------|----------|------------
`/group` | POST | CalendarEventV2Controller::class . ":groupCheckIn" | Set the group persons for the event + check

* `{id}`->`int` :: event ID
* `{id}`->`int` :: group ID

---
Route | Method | function | Description
------|--------|----------|------------
`/family` | POST | CalendarEventV2Controller::class . ":familyCheckIn" | Set the family persons for the event + check

* `{id}`->`int` :: event ID
* `{id}`->`int` :: family ID

---
Route | Method | function | Description
------|--------|----------|------------
`/attendees` | POST | CalendarEventV2Controller::class . ":eventCount" | get event count

* `{id}`->`int` :: event ID
* `{id}`->`int` :: type ID

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | CalendarEventV2Controller::class . ":manageEvent" | manage an event eventAction, [createEvent,moveEvent,resizeEvent,attendeesCheckinEvent,suppress,modifyEvent]

* `{id}`->`int` :: eventID
* `{id}`->`int` :: type ID
* `{ref}`->`array` :: calendarID
* `{id}`->`int` :: reccurenceID
* `{ref}`->`start` :: the start date : YYYY-MM-DD
* `{ref}`->`start` :: the end date : YYYY-MM-DD
* `{ref}`->`location` :: location

---
## API "Cart"

   in route : "/api/routes/cart.php

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | CartController::class . ':getAllPeopleInCart' | Get all people in Cart

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | CartController::class . ':cartOperation' | Get user info by id

* `{ref}`->`array` :: Persons id in array ref
* `{id}`->`int` :: Family id
* `{id}`->`int` :: Group id
* `{id}`->`int` :: removeFamily id
* `{id}`->`int` :: studentGroup id
* `{id}`->`int` :: teacherGroup id

---
Route | Method | function | Description
------|--------|----------|------------
`/interectPerson` | POST | CartController::class . ':cartIntersectPersons' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/emptyToGroup` | POST | CartController::class . ':emptyCartToGroup' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/emptyToEvent` | POST | CartController::class . ':emptyCartToEvent' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/emptyToNewGroup` | POST | CartController::class . ':emptyCartToNewGroup' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeGroup` | POST | CartController::class . ':removeGroupFromCart' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeGroups` | POST | CartController::class . ':removeGroupsFromCart' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeStudentGroup` | POST | CartController::class . ':removeStudentsGroupFromCart' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeTeacherGroup` | POST | CartController::class . ':removeTeachersGroupFromCart' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addAllStudents` | POST | CartController::class . ':addAllStudentsToCart' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addAllTeachers` | POST | CartController::class . ':addAllTeachersToCart' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeAllStudents` | POST | CartController::class . ':removeAllStudentsFromCart' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeAllTeachers` | POST | CartController::class . ':removeAllTeachersFromCart' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | CartController::class . ':deletePersonCart' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deactivate` | POST | CartController::class . ':deactivatePersonCart' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/` | DELETE | CartController::class . ':removePersonCart' | Remove all People in the Cart

---
## API "ckeditor"

   in route : "/api/routes/documents/documents-ckeditor.php

Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/templates` | GET | DocumentCKEditorController::class . ':templates' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/alltemplates` | POST | DocumentCKEditorController::class . ':alltemplates' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletetemplate` | POST | DocumentCKEditorController::class . ':deleteTemplate' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/renametemplate` | POST | DocumentCKEditorController::class . ':renametemplate' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/savetemplate` | POST | DocumentCKEditorController::class . ':saveTemplate' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/saveAsWordFile` | POST | DocumentCKEditorController::class . ':saveAsWordFile' | No description

---
## API "document"

   in route : "/api/routes/documents/documents-document.php

Route | Method | function | Description
------|--------|----------|------------
`/create` | POST | DocumentDocumentController::class . ':createDocument' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/get` | POST | DocumentDocumentController::class . ':getDocument' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/update` | POST | DocumentDocumentController::class . ':updateDocument' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | DocumentDocumentController::class . ':deleteDocument' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/leave` | POST | DocumentDocumentController::class . ':leaveDocument' | No description

---
## API "filemanager"

   in route : "/api/routes/documents/documents-filemanager.php

Route | Method | function | Description
------|--------|----------|------------
`/{personID:[0-9]+}` | POST | DocumentFileManagerController::class . ':getAllFileNoteForPerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getFile/{personID:[0-9]+}/[{path:.*}]` | GET | DocumentFileManagerController::class . ':getRealFile' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getPreview` | POST | DocumentFileManagerController::class . ':getPreview' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/changeFolder` | POST | DocumentFileManagerController::class . ':changeFolder' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/folderBack` | POST | DocumentFileManagerController::class . ':folderBack' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteOneFolder` | POST | DocumentFileManagerController::class . ':deleteOneFolder' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteOneFile` | POST | DocumentFileManagerController::class . ':deleteOneFile' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteFiles` | POST | DocumentFileManagerController::class . ':deleteFiles' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/movefiles` | POST | DocumentFileManagerController::class . ':movefiles' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/newFolder` | POST | DocumentFileManagerController::class . ':newFolder' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/rename` | POST | DocumentFileManagerController::class . ':renameFile' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/uploadFile/{personID:[0-9]+}` | POST | DocumentFileManagerController::class . ':uploadFile' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getRealLink` | POST | DocumentFileManagerController::class . ':getRealLink' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/setpathtopublicfolder` | POST | DocumentFileManagerController::class . ':setpathtopublicfolder' | No description

---
## API "sharedocument"

   in route : "/api/routes/documents/documents-sharedocument.php

Route | Method | function | Description
------|--------|----------|------------
`/getallperson` | POST | DocumentShareController::class . ':getAllShareForPerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addperson` | POST | DocumentShareController::class . ':addPersonToShare' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addfamily` | POST | DocumentShareController::class . ':addFamilyToShare' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addgroup` | POST | DocumentShareController::class . ':addGroupToShare' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteperson` | POST | DocumentShareController::class . ':deletePersonFromShare' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/setrights` | POST | DocumentShareController::class . ':setRightsForPerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/cleardocument` | POST | DocumentShareController::class . ':clearDocument' | No description

---
## API "deposits"

   in route : "/api/routes/finance/finance-deposits.php

## API "donationfunds"

   in route : "/api/routes/finance/finance-donationfunds.php

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | FinanceDonationFundController::class . ':getAllDonationFunds' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/edit` | POST | FinanceDonationFundController::class . ':editDonationFund' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/set` | POST | FinanceDonationFundController::class . ':setDonationFund' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | FinanceDonationFundController::class . ':deleteDonationFund' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/create` | POST | FinanceDonationFundController::class . ':createDonationFund' | No description

---
## API "payments"

   in route : "/api/routes/finance/finance-payments.php

Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}` | GET | FinancePaymentController::class . ':getPayment' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | FinancePaymentController::class . ':getSubmitOrPayement' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/byGroupKey` | DELETE | FinancePaymentController::class . ':deletePaymentByGroupKey' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family` | POST | FinancePaymentController::class . ':getAllPayementsForFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/info` | POST | FinancePaymentController::class . ':getAutoPaymentInfo' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/families` | POST | FinancePaymentController::class . ':getAllPayementsForFamilies' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | FinancePaymentController::class . ':deletePaymentForFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete/{authID:[0-9]+}` | GET | FinancePaymentController::class . ':deleteAutoPayment' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/invalidate` | POST | FinancePaymentController::class . ':invalidatePledge' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/validate` | POST | FinancePaymentController::class . ':validatePledge' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getchartsarrays` | POST | FinancePaymentController::class . ':getDepositSlipChartsArrays' | No description

---
## API "pledges"

   in route : "/api/routes/finance/finance-pledges.php

Route | Method | function | Description
------|--------|----------|------------
`/detail` | POST | FinancePledgeController::class . ':pledgeDetail' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family` | POST | FinancePledgeController::class . ':familyPledges' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | FinancePledgeController::class . ':deletePledge' | No description

---
## API "geocoder"

   in route : "/api/routes/geocoder.php

Route | Method | function | Description
------|--------|----------|------------
`/address` | POST | GeocoderController::class . ':getGeoLocals' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/address/` | POST | GeocoderController::class . ':getGeoLocals' | No description

---
## API "kiosks"

   in route : "/api/routes/kiosks.php

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | KiosksController::class . ':getKioskDevices' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/allowRegistration` | POST | KiosksController::class . ':allowDeviceRegistration' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/reloadKiosk` | POST | KiosksController::class . ':reloadKiosk' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/identifyKiosk` | POST | KiosksController::class . ':identifyKiosk' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/acceptKiosk` | POST | KiosksController::class . ':acceptKiosk' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/setAssignment` | POST | KiosksController::class . ':setKioskAssignment' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}` | DELETE | KiosksController::class . ':deleteKiosk' | No description

---
## API "mailchimp"

   in route : "/api/routes/mailchimp.php

Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | MailchimpController::class . ':searchList' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/{listID}` | GET | MailchimpController::class . ':oneList' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/lists` | GET | MailchimpController::class . ':lists' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/listmembers/{listID}` | GET | MailchimpController::class . ':listmembers' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/createlist` | POST | MailchimpController::class . ':createList' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/modifylist` | POST | MailchimpController::class . ':modifyList' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteallsubscribers` | POST | MailchimpController::class . ':deleteallsubscribers' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletelist` | POST | MailchimpController::class . ':deleteList' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/removeTag` | POST | MailchimpController::class . ':removeTag' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/removeAllTagsForMembers` | POST | MailchimpController::class . ':removeAllTagsForMembers' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/addTag` | POST | MailchimpController::class . ':addTag' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/getAllTags` | POST | MailchimpController::class . ':getAllTags' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/removeTagForMembers` | POST | MailchimpController::class . ':removeTagForMembers' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/create` | POST | MailchimpController::class . ':campaignCreate' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/delete` | POST | MailchimpController::class . ':campaignDelete' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/send` | POST | MailchimpController::class . ':campaignSend' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/save` | POST | MailchimpController::class . ':campaignSave' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/{campaignID}/content` | GET | MailchimpController::class . ':campaignContent' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/status` | POST | MailchimpController::class . ':statusList' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/suppress` | POST | MailchimpController::class . ':suppress' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/suppressMembers` | POST | MailchimpController::class . ':suppressMembers' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addallnewsletterpersons` | POST | MailchimpController::class . ':addallnewsletterpersons' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addallpersons` | POST | MailchimpController::class . ':addallpersons' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addperson` | POST | MailchimpController::class . ':addPerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addfamily` | POST | MailchimpController::class . ':addFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addAllFamilies` | POST | MailchimpController::class . ':addAllFamilies' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addgroup` | POST | MailchimpController::class . ':addGroup' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/testConnection` | POST | MailchimpController::class . ':testEmailConnectionMVC' | No description

---
## API "people"

   in route : "/api/routes/people/people.php

Route | Method | function | Description
------|--------|----------|------------
`/searchonlyperson/{query}` | GET | PeopleController::class . ':searchonlyperson' | Returns a list of the person who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | PeopleController::class . ':searchpeople' | Returns a list of the members/families/groups who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/classifications/all` | GET | PeopleController::class . ':getAllClassifications' | Returns all classifications

---
Route | Method | function | Description
------|--------|----------|------------
`/person/classification/assign` | POST | PeopleController::class . ':postPersonClassification' | Returns all classifications

---
## API "attendees"

   in route : "/api/routes/people/people-attendees.php

Route | Method | function | Description
------|--------|----------|------------
`/event/{eventID:[0-9]+}` | GET | PeopleAttendeesController::class . ':attendeesEvent' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/checkin` | POST | PeopleAttendeesController::class . ':attendeesCheckIn' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/checkout` | POST | PeopleAttendeesController::class . ':attendeesCheckOut' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/student` | POST | PeopleAttendeesController::class . ':attendeesStudent' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | PeopleAttendeesController::class . ':attendeesDelete' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteAll` | POST | PeopleAttendeesController::class . ':attendeesDeleteAll' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/checkAll` | POST | PeopleAttendeesController::class . ':attendeesCheckAll' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/uncheckAll` | POST | PeopleAttendeesController::class . ':attendeesUncheckAll' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/groups` | POST | PeopleAttendeesController::class . ':attendeesGroups' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletePerson` | POST | PeopleAttendeesController::class . ':deleteAttendeesPerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addPerson` | POST | PeopleAttendeesController::class . ':addAttendeesPerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/validate` | POST | PeopleAttendeesController::class . ':validateAttendees' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addFreeAttendees` | POST | PeopleAttendeesController::class . ':addFreeAttendees' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/qrcodeCall` | POST | PeopleAttendeesController::class . ':qrcodeCallAttendees' | No description

---
## API "families"

   in route : "/api/routes/people/people-families.php

Route | Method | function | Description
------|--------|----------|------------
`/familyproperties/{familyID:[0-9]+}` | POST | PeopleFamilyController::class . ":postfamilyproperties" | Return family properties for familyID

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/isMailChimpActive` | POST | PeopleFamilyController::class . ":isMailChimpActiveFamily" | Return if mailchimp is activated for family

* `{id}`->`int` :: familyId as id
* `{ref}`->`string` :: email as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}` | GET | PeopleFamilyController::class . ":getFamily" | Return the family as json

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/info` | POST | PeopleFamilyController::class . ":familyInfo" | Return the family info as json

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/numbers` | GET | PeopleFamilyController::class . ":numbersOfAnniversaries" | Return the numbers of Anniversaries for MenuEvent

---
Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | PeopleFamilyController::class . ":searchFamily" | Returns a list of the families who's name matches the :query parameter

* `{ref}`->`string` :: query as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/self-register` | GET | PeopleFamilyController::class . ":selfRegisterFamily" | Returns a list of the self-registered families

---
Route | Method | function | Description
------|--------|----------|------------
`/self-verify` | GET | PeopleFamilyController::class . ":selfVerifyFamily" | Returns a list of the self-verified families

---
Route | Method | function | Description
------|--------|----------|------------
`/pending-self-verify` | GET | PeopleFamilyController::class . ":pendingSelfVerify" | Returns a list of the pending self-verified families

---
Route | Method | function | Description
------|--------|----------|------------
`/byCheckNumber/{scanString}` | GET | PeopleFamilyController::class . ":byCheckNumberScan" | Returns a family string based on the scan string of an MICR reader containing a routing and account number

* `{ref}`->`string` :: scanString as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/photo` | GET | PeopleFamilyController::class . ":photo" | Returns the photo for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/thumbnail` | GET | PeopleFamilyController::class . ":thumbnail" | Returns the thumbnail for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/photo` | POST | PeopleFamilyController::class . ":postFamilyPhoto" | Post the photo for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/photo` | DELETE | PeopleFamilyController::class . ":deleteFamilyPhoto" | Delete the photo for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/verify` | POST | PeopleFamilyController::class . ":verifyFamily" | Verify the family for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/verifyPDF` | POST | PeopleFamilyController::class . ":verifyFamilyPDF" | Verify the family for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/verify/{familyId:[0-9]+}/now` | POST | PeopleFamilyController::class . ":verifyFamilyNow" | Verify the family for the familyId now

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/verify/url` | POST | PeopleFamilyController::class . ':verifyFamilyURL' | Verify the family for the familyId now

* `{id}`->`int` :: family

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/activate/{status}` | POST | PeopleFamilyController::class . ":familyActivateStatus" | Update the family status to activated or deactivated with :familyId and :status true/false. Pass true to activate and false to deactivate.

* `{id}`->`int` :: familyId as id
* `{ref}`->`bool` :: status as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/geolocation` | GET | PeopleFamilyController::class . ":familyGeolocation" | Return the location for the family

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/deletefield` | POST | PeopleFamilyController::class . ":deleteFamilyField" | delete familyField custom field

* `{id}`->`int` :: orderID as id
* `{id}`->`int` :: field as id

---
Route | Method | function | Description
------|--------|----------|------------
`/upactionfield` | POST | PeopleFamilyController::class . ":upactionFamilyField" | Move up the family custom field

* `{id}`->`int` :: orderID as id
* `{id}`->`int` :: field as id

---
Route | Method | function | Description
------|--------|----------|------------
`/downactionfield` | POST | PeopleFamilyController::class . ":downactionFamilyField" | Move down the family custom field

* `{id}`->`int` :: orderID as id
* `{id}`->`int` :: field as id

---
## API "groups"

   in route : "/api/routes/people/people-groups.php

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | PeopleGroupController::class . ":getAllGroups" | Get all the Groups

---
Route | Method | function | Description
------|--------|----------|------------
`/groupproperties/{groupID:[0-9]+}` | POST | PeopleGroupController::class . ":groupproperties" | Get all the properties of a group

---
Route | Method | function | Description
------|--------|----------|------------
`/addressbook/extract/{groupId:[0-9]+}` | GET | PeopleGroupController::class . ":addressBook" | get addressbook from a groupID through the url

---
Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | PeopleGroupController::class . ":searchGroup" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteAllManagers` | POST | PeopleGroupController::class . ":deleteAllManagers" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteManager` | POST | PeopleGroupController::class . ":deleteManager" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getmanagers` | POST | PeopleGroupController::class . ":getManagers" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addManager` | POST | PeopleGroupController::class . ":addManager" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/groupsInCart` | GET | PeopleGroupController::class . ":groupsInCart" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | PeopleGroupController::class . ":newGroup" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}` | POST | PeopleGroupController::class . ":updateGroup" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}` | GET | PeopleGroupController::class . ":groupInfo" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/cartStatus` | GET | PeopleGroupController::class . ":groupCartStatus" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}` | DELETE | PeopleGroupController::class . ":deleteGroup" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/members` | GET | PeopleGroupController::class . ":groupMembers" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/events` | GET | PeopleGroupController::class . ":groupEvents" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/removeperson/{userID:[0-9]+}` | DELETE | PeopleGroupController::class . ":removePersonFromGroup" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/addperson/{userID:[0-9]+}` | POST | PeopleGroupController::class . ":addPersonToGroup" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/addteacher/{userID:[0-9]+}` | POST | PeopleGroupController::class . ":addTeacherToGroup" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/userRole/{userID:[0-9]+}` | POST | PeopleGroupController::class . ":userRoleByUserId" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles/{roleID:[0-9]+}` | POST | PeopleGroupController::class . ":rolesByRoleId" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles` | GET | PeopleGroupController::class . ":allRoles" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/defaultRole` | POST | PeopleGroupController::class . ":defaultRoleForGroup" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles/{roleID:[0-9]+}` | DELETE | PeopleGroupController::class . ":deleteRole" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles` | POST | PeopleGroupController::class . ":roles" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/setGroupSpecificPropertyStatus` | POST | PeopleGroupController::class . ":setGroupSepecificPropertyStatus" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/settings/active/{value}` | POST | PeopleGroupController::class . ":settingsActiveValue" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/settings/email/export/{value}` | POST | PeopleGroupController::class . ":settingsEmailExportVvalue" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletefield` | POST | PeopleGroupController::class . ":deleteGroupField" | delete Group Specific property custom field

* `{id}`->`int` :: PropID as id
* `{id}`->`int` :: Field as id
* `{id}`->`int` :: GroupId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/upactionfield` | POST | PeopleGroupController::class . ":upactionGroupField" | delete Group Specific property custom field

* `{id}`->`int` :: PropID as id
* `{id}`->`int` :: Field as id
* `{id}`->`int` :: GroupId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/downactionfield` | POST | PeopleGroupController::class . ":downactionGroupField" | delete Group Specific property custom field

* `{id}`->`int` :: PropID as id
* `{id}`->`int` :: Field as id
* `{id}`->`int` :: GroupId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/sundayschool` | GET | PeopleGroupController::class . ":groupSundaySchool" | get all sundayschool teachers

* `{id}`->`int` :: groupID as id

---
## API "persons"

   in route : "/api/routes/people/people-persons.php

Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | PeoplePersonController::class . ":searchPerson" | Returns a list of the persons who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/cart/view` | GET | PeoplePersonController::class . ":personCartView" | Returns a list of the persons who are in the cart

---
Route | Method | function | Description
------|--------|----------|------------
`/volunteers/{personID:[0-9]+}` | POST | PeoplePersonController::class . ":volunteersPerPersonId" | Returns all the volunteers opportunities

* `{id}`->`int` :: personId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/volunteers/delete` | POST | PeoplePersonController::class . ":volunteersDelete" | delete a volunteer opportunity for a user

* `{id1}`->`int` :: personId as id1
* `{id2}`->`int` :: volunteerOpportunityId as id2

---
Route | Method | function | Description
------|--------|----------|------------
`/volunteers/add` | POST | PeoplePersonController::class . ":volunteersAdd" | Add volunteers opportunity

* `{id1}`->`int` :: personId as id1
* `{id2}`->`int` :: volID as id2

---
Route | Method | function | Description
------|--------|----------|------------
`/isMailChimpActive` | POST | PeoplePersonController::class . ":isMailChimpActivePerson" | Return if MailChimp is activated

* `{id}`->`int` :: personId as id
* `{ref}`->`string` :: email as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/activate/{status}` | POST | PeoplePersonController::class . ":activateDeacticate" | Return if MailChimp is activated

* `{id}`->`int` :: personId as id
* `{ref}`->`string` :: email as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/personproperties/{personID:[0-9]+}` | POST | PeoplePersonController::class . ":personpropertiesPerPersonId" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/numbers` | GET | PeoplePersonController::class . ":numbersOfBirthDates" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/photo` | GET | PeoplePersonController::class . ":photo" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/thumbnail` | GET | PeoplePersonController::class . ":thumbnail" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/photo` | POST | PeoplePersonController::class . ":postPersonPhoto" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/photo` | DELETE | PeoplePersonController::class . ":deletePersonPhoto" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/addToCart` | POST | PeoplePersonController::class . ":addPersonToCart" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}` | DELETE | PeoplePersonController::class . ":deletePerson" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletefield` | POST | PeoplePersonController::class . ":deletePersonField" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/upactionfield` | POST | PeoplePersonController::class . ":upactionPersonfield" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/downactionfield` | POST | PeoplePersonController::class . ":downactionPersonfield" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/duplicate/emails` | GET | PeoplePersonController::class . ":duplicateEmails" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/NotInMailChimp/emails/{type}` | GET | PeoplePersonController::class . ":notInMailChimpEmails" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/saveNoteAsWordFile` | POST | PeoplePersonController::class . ":saveNoteAsWordFile" | No description

---
## API "data"

   in route : "/api/routes/public/public-data.php

Route | Method | function | Description
------|--------|----------|------------
`/countries` | GET | PublicDataController::class . ':getCountries' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/countries/` | GET | PublicDataController::class . ':getCountries' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/countries/{countryCode}/states` | GET | PublicDataController::class . ':getStates' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/countries/{countryCode}/states/` | GET | PublicDataController::class . ':getStates' | No description

---
## API "register"

   in route : "/api/routes/public/public-register.php

Route | Method | function | Description
------|--------|----------|------------
`` | POST | PublicRegisterController::class . ':registerEcclesiaCRM' | No description

---
## API "search"

   in route : "/api/routes/search.php

Route | Method | function | Description
------|--------|----------|------------
`/{query}` | GET | SearchController::class . ':quickSearch' | a search query. Returns all instances of Persons, Families, Groups, Deposits, Checks, Payments that match the search query

* `{ref}`->`string` :: query string as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/getresultbyname/{query}` | GET | SearchController::class . ':getSearchResultByName' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/comboElements/` | POST | SearchController::class . ':comboElements' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getGroupForTypeID/` | POST | SearchController::class . ':getGroupForTypeID' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getGroupRoleForGroupID/` | POST | SearchController::class . ':getGroupRoleForGroupID' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getresult/` | POST | SearchController::class . ':getSearchResult' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getresult/` | GET | SearchController::class . ':getSearchResult' | No description

---
## API "mapicons"

   in route : "/api/routes/sidebar/sidebar-mapicons.php

Route | Method | function | Description
------|--------|----------|------------
`/getall` | POST | SidebarMapIconsController::class . ':getAllMapIcons' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/checkOnlyPersonView` | POST | SidebarMapIconsController::class . ':checkOnlyPersonView' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/setIconName` | POST | SidebarMapIconsController::class . ':setIconName' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeIcon` | POST | SidebarMapIconsController::class . ':removeIcon' | No description

---
## API "menulinks"

   in route : "/api/routes/sidebar/sidebar-menulinks.php

## API "properties"

   in route : "/api/routes/sidebar/sidebar-properties.php

Route | Method | function | Description
------|--------|----------|------------
`/persons/assign` | POST | SidebarPropertiesController::class . ':propertiesPersonsAssign' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/persons/unassign` | DELETE | SidebarPropertiesController::class . ':propertiesPersonsUnAssign' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/families/assign` | POST | SidebarPropertiesController::class . ':propertiesFamiliesAssign' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/families/unassign` | DELETE | SidebarPropertiesController::class . ':propertiesFamiliesUnAssign' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/groups/assign` | POST | SidebarPropertiesController::class . ':propertiesGroupsAssign' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/groups/unassign` | DELETE | SidebarPropertiesController::class . ':propertiesGroupsUnAssign' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists` | POST | SidebarPropertiesController::class . ':getAllPropertyTypes' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/edit` | POST | SidebarPropertiesController::class . ':editPropertyType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/set` | POST | SidebarPropertiesController::class . ':setPropertyType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/create` | POST | SidebarPropertiesController::class . ':createPropertyType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/delete` | POST | SidebarPropertiesController::class . ':deletePropertyType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/edit` | POST | SidebarPropertiesController::class . ':editProperty' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/set` | POST | SidebarPropertiesController::class . ':setProperty' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/delete` | POST | SidebarPropertiesController::class . ':deleteProperty' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/create` | POST | SidebarPropertiesController::class . ':createProperty' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/{type}` | POST | SidebarPropertiesController::class . ':getAllProperties' | No description

---
## API "roles"

   in route : "/api/routes/sidebar/sidebar-roles.php

Route | Method | function | Description
------|--------|----------|------------
`/all` | GET | SidebarRolesController::class . ':getAllRoles' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/persons/assign` | POST | SidebarRolesController::class . ':rolePersonAssign' | No description

---
## API "volunteeropportunity"

   in route : "/api/routes/sidebar/sidebar-volunteeropportunity.php

## API "pastoralcare"

   in route : "/api/routes/pastoralcare/pastoralcare.php

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | PastoralCareController::class . ':getAllPastoralCare' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletetype` | POST | PastoralCareController::class . ':deletePastoralCareType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/createtype` | POST | PastoralCareController::class . ':createPastoralCareType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/settype` | POST | PastoralCareController::class . ':setPastoralCareType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/edittype` | POST | PastoralCareController::class . ':editPastoralCareType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/add` | POST | PastoralCareController::class . ':addPastoralCarePerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/delete` | POST | PastoralCareController::class . ':deletePastoralCarePerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/getinfo` | POST | PastoralCareController::class . ':getPastoralCareInfoPerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/modify` | POST | PastoralCareController::class . ':modifyPastoralCarePerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/add` | POST | PastoralCareController::class . ':addPastoralCareFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/delete` | POST | PastoralCareController::class . ':deletePastoralCareFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/getinfo` | POST | PastoralCareController::class . ':getPastoralCareInfoFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/modify` | POST | PastoralCareController::class . ':modifyPastoralCareFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/members` | POST | PastoralCareController::class . ':pastoralcareMembersDashboard' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/personNeverBeenContacted` | POST | PastoralCareController::class . ':personNeverBeenContacted' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/familyNeverBeenContacted` | POST | PastoralCareController::class . ':familyNeverBeenContacted' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/singleNeverBeenContacted` | POST | PastoralCareController::class . ':singleNeverBeenContacted' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/retiredNeverBeenContacted` | POST | PastoralCareController::class . ':retiredNeverBeenContacted' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/youngNeverBeenContacted` | POST | PastoralCareController::class . ':youngNeverBeenContacted' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getPersonByClassification` | POST | PastoralCareController::class . ':getPersonByClassificationPastoralCare' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getPersonByClassification/{type:[0-9]+}` | POST | PastoralCareController::class . ':getPersonByClassificationPastoralCare' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getlistforuser/{UserID:[0-9]+}` | GET | PastoralCareController::class . ':getPastoralCareListForUser' | No description

---
## API "pastoralcare"

   in route : "/api/routes/pastoralcare/pastoralcare.php

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | PastoralCareController::class . ':getAllPastoralCare' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletetype` | POST | PastoralCareController::class . ':deletePastoralCareType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/createtype` | POST | PastoralCareController::class . ':createPastoralCareType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/settype` | POST | PastoralCareController::class . ':setPastoralCareType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/edittype` | POST | PastoralCareController::class . ':editPastoralCareType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/add` | POST | PastoralCareController::class . ':addPastoralCarePerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/delete` | POST | PastoralCareController::class . ':deletePastoralCarePerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/getinfo` | POST | PastoralCareController::class . ':getPastoralCareInfoPerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/modify` | POST | PastoralCareController::class . ':modifyPastoralCarePerson' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/add` | POST | PastoralCareController::class . ':addPastoralCareFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/delete` | POST | PastoralCareController::class . ':deletePastoralCareFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/getinfo` | POST | PastoralCareController::class . ':getPastoralCareInfoFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/modify` | POST | PastoralCareController::class . ':modifyPastoralCareFamily' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/members` | POST | PastoralCareController::class . ':pastoralcareMembersDashboard' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/personNeverBeenContacted` | POST | PastoralCareController::class . ':personNeverBeenContacted' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/familyNeverBeenContacted` | POST | PastoralCareController::class . ':familyNeverBeenContacted' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/singleNeverBeenContacted` | POST | PastoralCareController::class . ':singleNeverBeenContacted' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/retiredNeverBeenContacted` | POST | PastoralCareController::class . ':retiredNeverBeenContacted' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/youngNeverBeenContacted` | POST | PastoralCareController::class . ':youngNeverBeenContacted' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getPersonByClassification` | POST | PastoralCareController::class . ':getPersonByClassificationPastoralCare' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getPersonByClassification/{type:[0-9]+}` | POST | PastoralCareController::class . ':getPersonByClassificationPastoralCare' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getlistforuser/{UserID:[0-9]+}` | GET | PastoralCareController::class . ':getPastoralCareListForUser' | No description

---
## API "sundayschool"

   in route : "/api/routes/sundayschool.php

Route | Method | function | Description
------|--------|----------|------------
`/getallstudents/{groupId:[0-9]+}` | POST | SundaySchoolController::class . ':getallstudentsForGroup' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getAllGendersForDonut/{groupId:[0-9]+}` | POST | SundaySchoolController::class . ':getAllGendersForDonut' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getAllStudentsForChart/{groupId:[0-9]+}` | POST | SundaySchoolController::class . ':getAllStudentsForChart' | No description

---
## API "system"

   in route : "/api/routes/system/system.php

Route | Method | function | Description
------|--------|----------|------------
`/csp-report` | POST | SystemController::class . ':cspReport' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletefile` | POST | SystemController::class . ':deleteFile' | No description

---
## API "custom-fields"

   in route : "/api/routes/system/system-custom-fields.php

Route | Method | function | Description
------|--------|----------|------------
`/person` | GET | SystemCustomFieldController::class . ':getPersonFieldsByType' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/` | GET | SystemCustomFieldController::class . ':getPersonFieldsByType' | No description

---
## API "synchronize"

   in route : "/api/routes/system/system-synchronize.php

Route | Method | function | Description
------|--------|----------|------------
`/page` | POST | SystemSynchronizeController::class . ':synchronize' | Returns the dashboard items in function of the current page name : for CRMJsom.js

* `{page}`->`string` :: current page name

---
## API "database"

   in route : "/api/routes/system/system-database.php

Route | Method | function | Description
------|--------|----------|------------
`/restore` | POST | SystemBackupRestoreController::class . ':restore' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/download/{filename}` | GET | SystemBackupRestoreController::class . ':download' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/people/clear` | DELETE | SystemBackupRestoreController::class . ':clearPeopleTables' | No description

---
## API "gdrp"

   in route : "/api/routes/system/system-gdrp.php

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | SystemGDRPController::class . ':getAllGdprNotes' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/setComment` | POST | SystemGDRPController::class . ':setGdprComment' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeperson` | POST | SystemGDRPController::class . ':removePersonGdpr' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeallpersons` | POST | SystemGDRPController::class . ':removeAllPersonsGdpr' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removefamily` | POST | SystemGDRPController::class . ':removeFamilyGdpr' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeallfamilies` | POST | SystemGDRPController::class . ':removeAllFamiliesGdpr' | No description

---
## API "issues"

   in route : "/api/routes/system/system-issues.php

Route | Method | function | Description
------|--------|----------|------------
`/issues` | POST | SystemIssueController::class . ':issues' | No description

---
## API "systemupgrade"

   in route : "/api/routes/system/system-system-upgrade.php

Route | Method | function | Description
------|--------|----------|------------
`/downloadlatestrelease` | GET | SystemUpgradeController::class . ':downloadlatestrelease' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/doupgrade` | POST | SystemUpgradeController::class . ':doupgrade' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/isUpdateRequired` | POST | SystemUpgradeController::class . ':isUpdateRequired' | No description

---
## API "timerjobs"

   in route : "/api/routes/system/system-timerjobs.php

Route | Method | function | Description
------|--------|----------|------------
`/run` | POST | TimerJobsController::class . ':runTimerJobs' | No description

---
## API "userrole"

   in route : "/api/routes/user/user-role.php

Route | Method | function | Description
------|--------|----------|------------
`/add` | POST | UserRoleController::class . ':addUserRole' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/get` | POST | UserRoleController::class . ':getUserRole' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/rename` | POST | UserRoleController::class . ':renameUserRole' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getall` | POST | UserRoleController::class . ':getAllUserRoles' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | UserRoleController::class . ':deleteUserRole' | No description

---
## API "users"

   in route : "/api/routes/user/user-users.php

Route | Method | function | Description
------|--------|----------|------------
`/{userId:[0-9]+}/password/reset` | POST | UserUsersController::class . ':passwordReset' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/controlAccount` | POST | UserUsersController::class . ':controlAccount' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/exitControlAccount` | POST | UserUsersController::class . ':exitControlAccount' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/lockunlock` | POST | UserUsersController::class . ':lockUnlock' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/showsince` | POST | UserUsersController::class . ':showSince' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/showto` | POST | UserUsersController::class . ':showTo' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{userId:[0-9]+}/login/reset` | POST | UserUsersController::class . ':loginReset' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{userId:[0-9]+}` | DELETE | UserUsersController::class . ':deleteUser' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/2fa/remove` | POST | UserUsersController::class . ':userstwofaremove' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/2fa/pending` | POST | UserUsersController::class . ':userstwofapending' | No description

---
