# <center><big>Ecclesia**CRM** documentation de l'API</big></center>
----

Ecclesia**CRM** use Slim 3.9.2 which allow to make api call to the restricted area of the CRM.



## API "calendar"

   in route : "/api/routes/calendar/calendar-calendarV2.php

Route | Method | function | Description
------|--------|----------|------------
`/getallevents` | POST | getallCalendarEvents | Get all events for all calendars for a specified range

* `{ref}`->`start` :: the start date : YYYY-MM-DD
* `{ref}`->`end` :: the end date : YYYY-MM-DD

---
Route | Method | function | Description
------|--------|----------|------------
`/numberofcalendars` | POST | numberOfCalendars | get all the number of calendar for the current user

---
Route | Method | function | Description
------|--------|----------|------------
`/showhidecalendars` | POST | showHideCalendars | Show Hide calendar

* `{ref}`->`array` :: calIDs
* `{id}`->`bool` :: isPresent

---
Route | Method | function | Description
------|--------|----------|------------
`/setDescriptionType` | POST | setCalendarDescriptionType | set Description type for a calendar

* `{ref}`->`array` :: calIDs
* `{ref}`->`string` :: desc
* `{ref}`->`string` :: type

---
Route | Method | function | Description
------|--------|----------|------------
`/getallforuser` | POST | getAllCalendarsForUser | Get all calendars for a specified user

* `{ref}`->`string` :: type
* `{ref}`->`bool` :: onlyvisible
* `{ref}`->`bool` :: allCalendars

---
Route | Method | function | Description
------|--------|----------|------------
`/info` | POST | calendarInfo | Get infos for a calendar

* `{ref}`->`array` :: calIDs
* `{ref}`->`string` :: type

---
Route | Method | function | Description
------|--------|----------|------------
`/setcolor` | POST | setCalendarColor | Set color for a calendar

* `{ref}`->`array` :: calIDs
* `{ref}`->`hex` :: color : #FFF

---
Route | Method | function | Description
------|--------|----------|------------
`/setckecked` | POST | setCheckedCalendar | Check the calendar to make it visible

* `{ref}`->`array` :: calIDs
* `{ref}`->`bool` :: isChecked

---
Route | Method | function | Description
------|--------|----------|------------
`/new` | POST | newCalendar | Create a new calendar

* `{ref}`->`string` :: title

---
Route | Method | function | Description
------|--------|----------|------------
`/newReservation` | POST | newCalendarReservation | Create new calendar reservation

* `{ref}`->`string` :: title
* `{ref}`->`string` :: type
* `{ref}`->`string` :: desc

---
Route | Method | function | Description
------|--------|----------|------------
`/modifyname` | POST | modifyCalendarName | Change calendar name

* `{ref}`->`array` :: calIDs
* `{ref}`->`string` :: title

---
Route | Method | function | Description
------|--------|----------|------------
`/getinvites` | POST | getCalendarInvites | get attendees for a calendar

* `{ref}`->`array` :: calIDs

---
Route | Method | function | Description
------|--------|----------|------------
`/sharedelete` | POST | shareCalendarDelete | Delete a share calendar for a person

* `{ref}`->`array` :: calIDs
* `{ref}`->`int` :: principal

---
Route | Method | function | Description
------|--------|----------|------------
`/sharefamily` | POST | shareCalendarFamily | Share a calendar with a person

* `{ref}`->`array` :: calIDs
* `{id}`->`int` :: person ID
* `{ref}`->`bool` :: notification
* `{ref}`->`array` :: calIDs
* `{id}`->`int` :: family ID
* `{ref}`->`bool` :: notification

---
Route | Method | function | Description
------|--------|----------|------------
`/sharegroup` | POST | shareCalendarGroup | Share a calendar with an entire group

* `{ref}`->`array` :: calIDs
* `{id}`->`int` :: group ID
* `{ref}`->`bool` :: notification

---
Route | Method | function | Description
------|--------|----------|------------
`/setrights` | POST | setCalendarRights | Set right access for a calendar

* `{ref}`->`array` :: calIDs
* `{ref}`->`array` :: calIDs
* `{ref}`->`int` :: principal
* `{ref}`->`int` :: rightAccess

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | deleteCalendar | Delete a calendar

* `{ref}`->`array` :: calIDs

---
## API "events"

   in route : "/api/routes/calendar/calendar-eventsV2.php

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | getAllEvents | Get all events for all calendars for a specified range

---
Route | Method | function | Description
------|--------|----------|------------
`/notDone` | GET | getNotDoneEvents | Get all events after now

---
Route | Method | function | Description
------|--------|----------|------------
`/types` | GET | getEventTypes | Get all event type

---
Route | Method | function | Description
------|--------|----------|------------
`/names` | GET | eventNames | Get all event names

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteeventtype` | POST | deleteeventtype | delete event type

* `{id}`->`int` :: type ID

---
Route | Method | function | Description
------|--------|----------|------------
`/info` | POST | eventInfo | get event info

* `{id}`->`int` :: event ID

---
Route | Method | function | Description
------|--------|----------|------------
`/person` | POST | personCheckIn | Set a person for the event + check

* `{id}`->`int` :: event ID
* `{id}`->`int` :: person ID

---
Route | Method | function | Description
------|--------|----------|------------
`/group` | POST | groupCheckIn | Set the group persons for the event + check

* `{id}`->`int` :: event ID
* `{id}`->`int` :: group ID

---
Route | Method | function | Description
------|--------|----------|------------
`/family` | POST | familyCheckIn | Set the family persons for the event + check

* `{id}`->`int` :: event ID
* `{id}`->`int` :: family ID

---
Route | Method | function | Description
------|--------|----------|------------
`/attendees` | POST | eventCount | get event count

* `{id}`->`int` :: event ID
* `{id}`->`int` :: type ID

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | manageEvent | manage an event eventAction, [createEvent,moveEvent,resizeEvent,attendeesCheckinEvent,suppress,modifyEvent]

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
`/` | GET | getAllPeopleInCart | Get all people in Cart

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | cartOperation | Get user info by id

* `{ref}`->`array` :: Persons id in array ref
* `{id}`->`int` :: Family id
* `{id}`->`int` :: Group id
* `{id}`->`int` :: removeFamily id
* `{id}`->`int` :: studentGroup id
* `{id}`->`int` :: teacherGroup id

---
Route | Method | function | Description
------|--------|----------|------------
`/interectPerson` | POST | cartIntersectPersons | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/emptyToGroup` | POST | emptyCartToGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/emptyToEvent` | POST | emptyCartToEvent | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/emptyToNewGroup` | POST | emptyCartToNewGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeGroup` | POST | removeGroupFromCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeGroups` | POST | removeGroupsFromCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeStudentGroup` | POST | removeStudentsGroupFromCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeTeacherGroup` | POST | removeTeachersGroupFromCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addAllStudents` | POST | addAllStudentsToCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addAllTeachers` | POST | addAllTeachersToCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeAllStudents` | POST | removeAllStudentsFromCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeAllTeachers` | POST | removeAllTeachersFromCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | deletePersonCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deactivate` | POST | deactivatePersonCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/` | DELETE | removePersonCart | Remove all People in the Cart

---
## API "ckeditor"

   in route : "/api/routes/documents/documents-ckeditor.php

Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/templates` | GET | templates | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/alltemplates` | POST | alltemplates | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletetemplate` | POST | deleteTemplate | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/renametemplate` | POST | renametemplate | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/savetemplate` | POST | saveTemplate | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/saveAsWordFile` | POST | saveAsWordFile | No description

---
## API "document"

   in route : "/api/routes/documents/documents-document.php

Route | Method | function | Description
------|--------|----------|------------
`/create` | POST | createDocument | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/get` | POST | getDocument | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/update` | POST | updateDocument | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | deleteDocument | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/leave` | POST | leaveDocument | No description

---
## API "filemanager"

   in route : "/api/routes/documents/documents-filemanager.php

Route | Method | function | Description
------|--------|----------|------------
`/{personID:[0-9]+}` | POST | getAllFileNoteForPerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getFile/{personID:[0-9]+}/[{path:.*}]` | GET | getRealFile | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getPreview` | POST | getPreview | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/changeFolder` | POST | changeFolder | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/folderBack` | POST | folderBack | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteOneFolder` | POST | deleteOneFolder | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteOneFile` | POST | deleteOneFile | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteFiles` | POST | deleteFiles | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/movefiles` | POST | movefiles | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/newFolder` | POST | newFolder | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/rename` | POST | renameFile | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/uploadFile/{personID:[0-9]+}` | POST | uploadFile | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getRealLink` | POST | getRealLink | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/setpathtopublicfolder` | POST | setpathtopublicfolder | No description

---
## API "sharedocument"

   in route : "/api/routes/documents/documents-sharedocument.php

Route | Method | function | Description
------|--------|----------|------------
`/getallperson` | POST | getAllShareForPerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addperson` | POST | addPersonToShare | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addfamily` | POST | addFamilyToShare | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addgroup` | POST | addGroupToShare | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteperson` | POST | deletePersonFromShare | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/setrights` | POST | setRightsForPerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/cleardocument` | POST | clearDocument | No description

---
## API "deposits"

   in route : "/api/routes/finance/finance-deposits.php

Route | Method | function | Description
------|--------|----------|------------
`` | POST | createDeposit | No description

---
Route | Method | function | Description
------|--------|----------|------------
`` | GET | getAllDeposits | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}` | GET | getOneDeposit | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}` | POST | modifyOneDeposit | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}/ofx` | GET | createDepositOFX | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}/pdf` | GET | createDepositPDF | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}/csv` | GET | createDepositCSV | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}` | DELETE | deleteDeposit | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}/pledges` | GET | getAllPledgesForDeposit | No description

---
## API "donationfunds"

   in route : "/api/routes/finance/finance-donationfunds.php

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | getAllDonationFunds | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/edit` | POST | editDonationFund | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/set` | POST | setDonationFund | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | deleteDonationFund | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/create` | POST | createDonationFund | No description

---
## API "payments"

   in route : "/api/routes/finance/finance-payments.php

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupKey}` | DELETE | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family` | POST | getAllPayementsForFamily | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/info` | POST | getAutoPaymentInfo | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/families` | POST | getAllPayementsForFamilies | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | deletePaymentForFamily | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete/{authID:[0-9]+}` | GET | deleteAutoPayment | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/invalidate` | POST | invalidatePledge | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/validate` | POST | validatePledge | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getchartsarrays` | POST | getDepositSlipChartsArrays | No description

---
## API "pledges"

   in route : "/api/routes/finance/finance-pledges.php

Route | Method | function | Description
------|--------|----------|------------
`/detail` | POST | pledgeDetail | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family` | POST | familyPledges | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | deletePledge | No description

---
## API "geocoder"

   in route : "/api/routes/geocoder.php

Route | Method | function | Description
------|--------|----------|------------
`/address` | POST | getGeoLocals | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/address/` | POST | getGeoLocals | No description

---
## API "kiosks"

   in route : "/api/routes/kiosks.php

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | getKioskDevices | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/allowRegistration` | POST | allowDeviceRegistration | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/reloadKiosk` | POST | reloadKiosk | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/identifyKiosk` | POST | identifyKiosk | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/acceptKiosk` | POST | acceptKiosk | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/setAssignment` | POST | setKioskAssignment | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}` | DELETE | deleteKiosk | No description

---
## API "mailchimp"

   in route : "/api/routes/mailchimp.php

Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | searchList | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/{listID}` | GET | oneList | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/lists` | GET | lists | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/listmembers/{listID}` | GET | listmembers | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/createlist` | POST | createList | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/modifylist` | POST | modifyList | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteallsubscribers` | POST | deleteallsubscribers | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletelist` | POST | deleteList | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/removeTag` | POST | removeTag | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/removeAllTagsForMembers` | POST | removeAllTagsForMembers | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/addTag` | POST | addTag | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/getAllTags` | POST | getAllTags | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/list/removeTagForMembers` | POST | removeTagForMembers | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/create` | POST | campaignCreate | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/delete` | POST | campaignDelete | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/send` | POST | campaignSend | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/save` | POST | campaignSave | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/{campaignID}/content` | GET | campaignContent | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/status` | POST | statusList | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/suppress` | POST | suppress | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/suppressMembers` | POST | suppressMembers | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addallnewsletterpersons` | POST | addallnewsletterpersons | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addallpersons` | POST | addallpersons | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addperson` | POST | addPerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addfamily` | POST | addFamily | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addAllFamilies` | POST | addAllFamilies | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addgroup` | POST | addGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/testConnection` | POST | testEmailConnectionMVC | No description

---
## API "people"

   in route : "/api/routes/people/people.php

Route | Method | function | Description
------|--------|----------|------------
`/searchonlyperson/{query}` | GET | function($request,$response,$args) | Returns a list of the person who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | function($request,$response,$args) | Returns a list of the members/families/groups who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/classifications/all` | GET | getAllClassifications | Returns all classifications

---
Route | Method | function | Description
------|--------|----------|------------
`/person/classification/assign` | POST | postPersonClassification | Returns all classifications

---
## API "attendees"

   in route : "/api/routes/people/people-attendees.php

Route | Method | function | Description
------|--------|----------|------------
`/event/{eventID:[0-9]+}` | GET | attendeesEvent | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/checkin` | POST | attendeesCheckIn | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/checkout` | POST | attendeesCheckOut | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/student` | POST | attendeesStudent | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | attendeesDelete | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteAll` | POST | attendeesDeleteAll | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/checkAll` | POST | attendeesCheckAll | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/uncheckAll` | POST | attendeesUncheckAll | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/groups` | POST | attendeesGroups | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletePerson` | POST | deleteAttendeesPerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addPerson` | POST | addAttendeesPerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/validate` | POST | validateAttendees | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addFreeAttendees` | POST | addFreeAttendees | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/qrcodeCall` | POST | qrcodeCallAttendees | No description

---
## API "families"

   in route : "/api/routes/people/people-families.php

Route | Method | function | Description
------|--------|----------|------------
`/familyproperties/{familyID:[0-9]+}` | POST | postfamilyproperties | Return family properties for familyID

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/isMailChimpActive` | POST | isMailChimpActiveFamily | Return if mailchimp is activated for family

* `{id}`->`int` :: familyId as id
* `{ref}`->`string` :: email as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}` | GET | getFamily | Return the family as json

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/info` | POST | familyInfo | Return the family info as json

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/numbers` | GET | numbersOfAnniversaries | Return the numbers of Anniversaries for MenuEvent

---
Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | searchFamily | Returns a list of the families who's name matches the :query parameter

* `{ref}`->`string` :: query as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/self-register` | GET | selfRegisterFamily | Returns a list of the self-registered families

---
Route | Method | function | Description
------|--------|----------|------------
`/self-verify` | GET | selfVerifyFamily | Returns a list of the self-verified families

---
Route | Method | function | Description
------|--------|----------|------------
`/pending-self-verify` | GET | pendingSelfVerify | Returns a list of the pending self-verified families

---
Route | Method | function | Description
------|--------|----------|------------
`/byCheckNumber/{scanString}` | GET | byCheckNumberScan | Returns a family string based on the scan string of an MICR reader containing a routing and account number

* `{ref}`->`string` :: scanString as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/photo` | GET | function ($request, $response, $args) | Returns the photo for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/thumbnail` | GET | function ($request, $response, $args) | Returns the thumbnail for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/photo` | POST | postFamilyPhoto | Post the photo for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/photo` | DELETE | deleteFamilyPhoto | Delete the photo for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/verify` | POST | verifyFamily | Verify the family for the familyId

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/verify/{familyId:[0-9]+}/now` | POST | verifyFamilyNow | Verify the family for the familyId now

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/verify/url` | POST | verifyFamilyURL | Verify the family for the familyId now

* `{id}`->`int` :: family

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/activate/{status}` | POST | familyActivateStatus | Update the family status to activated or deactivated with :familyId and :status true/false. Pass true to activate and false to deactivate.

* `{id}`->`int` :: familyId as id
* `{ref}`->`bool` :: status as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/{familyId:[0-9]+}/geolocation` | GET | familyGeolocation | Return the location for the family

* `{id}`->`int` :: familyId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/deletefield` | POST | deleteFamilyField | delete familyField custom field

* `{id}`->`int` :: orderID as id
* `{id}`->`int` :: field as id

---
Route | Method | function | Description
------|--------|----------|------------
`/upactionfield` | POST | upactionFamilyField | Move up the family custom field

* `{id}`->`int` :: orderID as id
* `{id}`->`int` :: field as id

---
Route | Method | function | Description
------|--------|----------|------------
`/downactionfield` | POST | downactionFamilyField | Move down the family custom field

* `{id}`->`int` :: orderID as id
* `{id}`->`int` :: field as id

---
## API "groups"

   in route : "/api/routes/people/people-groups.php

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | getAllGroups | Get all the Groups

---
Route | Method | function | Description
------|--------|----------|------------
`/groupproperties/{groupID:[0-9]+}` | POST | groupproperties | Get all the properties of a group

---
Route | Method | function | Description
------|--------|----------|------------
`/addressbook/extract/{groupId:[0-9]+}` | GET | function ($request, $response, $args) | get addressbook from a groupID through the url

---
Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | searchGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteAllManagers` | POST | deleteAllManagers | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteManager` | POST | deleteManager | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getmanagers` | POST | getManagers | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/addManager` | POST | addManager | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/groupsInCart` | GET | groupsInCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | newGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}` | POST | updateGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}` | GET | groupInfo | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/cartStatus` | GET | groupCartStatus | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}` | DELETE | deleteGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/members` | GET | groupMembers | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/events` | GET | groupEvents | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/removeperson/{userID:[0-9]+}` | DELETE | removePersonFromGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/addperson/{userID:[0-9]+}` | POST | addPersonToGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/addteacher/{userID:[0-9]+}` | POST | addTeacherToGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/userRole/{userID:[0-9]+}` | POST | userRoleByUserId | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles/{roleID:[0-9]+}` | POST | rolesByRoleId | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles` | GET | allRoles | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/defaultRole` | POST | defaultRoleForGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles/{roleID:[0-9]+}` | DELETE | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles` | POST | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/setGroupSpecificPropertyStatus` | POST | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/settings/active/{value}` | POST | settingsActiveValue | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/settings/email/export/{value}` | POST | settingsEmailExportVvalue | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletefield` | POST | deleteGroupField | delete Group Specific property custom field

* `{id}`->`int` :: PropID as id
* `{id}`->`int` :: Field as id
* `{id}`->`int` :: GroupId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/upactionfield` | POST | upactionGroupField | delete Group Specific property custom field

* `{id}`->`int` :: PropID as id
* `{id}`->`int` :: Field as id
* `{id}`->`int` :: GroupId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/downactionfield` | POST | downactionGroupField | delete Group Specific property custom field

* `{id}`->`int` :: PropID as id
* `{id}`->`int` :: Field as id
* `{id}`->`int` :: GroupId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/sundayschool` | GET | groupSundaySchool | get all sundayschool teachers

* `{id}`->`int` :: groupID as id

---
## API "persons"

   in route : "/api/routes/people/people-persons.php

Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | searchPerson | Returns a list of the persons who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/cart/view` | GET | personCartView | Returns a list of the persons who are in the cart

---
Route | Method | function | Description
------|--------|----------|------------
`/volunteers/{personID:[0-9]+}` | POST | volunteersPerPersonId | Returns all the volunteers opportunities

* `{id}`->`int` :: personId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/volunteers/delete` | POST | volunteersDelete | delete a volunteer opportunity for a user

* `{id1}`->`int` :: personId as id1
* `{id2}`->`int` :: volunteerOpportunityId as id2

---
Route | Method | function | Description
------|--------|----------|------------
`/volunteers/add` | POST | volunteersAdd | Add volunteers opportunity

* `{id1}`->`int` :: personId as id1
* `{id2}`->`int` :: volID as id2

---
Route | Method | function | Description
------|--------|----------|------------
`/isMailChimpActive` | POST | isMailChimpActivePerson | Return if MailChimp is activated

* `{id}`->`int` :: personId as id
* `{ref}`->`string` :: email as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/activate/{status}` | POST | activateDeacticate | Return if MailChimp is activated

* `{id}`->`int` :: personId as id
* `{ref}`->`string` :: email as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/personproperties/{personID:[0-9]+}` | POST | personpropertiesPerPersonId | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/numbers` | GET | numbersOfBirthDates | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/photo` | GET | function ($request, $response, $args ) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/thumbnail` | GET | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/photo` | POST | postPersonPhoto | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/photo` | DELETE | deletePersonPhoto | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/addToCart` | POST | addPersonToCart | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}` | DELETE | deletePerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletefield` | POST | deletePersonField | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/upactionfield` | POST | upactionPersonfield | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/downactionfield` | POST | downactionPersonfield | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/duplicate/emails` | GET | duplicateEmails | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/NotInMailChimp/emails/{type}` | GET | notInMailChimpEmails | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/saveNoteAsWordFile` | POST | saveNoteAsWordFile | No description

---
## API "data"

   in route : "/api/routes/public/public-data.php

Route | Method | function | Description
------|--------|----------|------------
`/countries` | GET | getCountries | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/countries/` | GET | getCountries | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/countries/{countryCode}/states` | GET | getStates | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/countries/{countryCode}/states/` | GET | getStates | No description

---
## API "register"

   in route : "/api/routes/public/public-register.php

Route | Method | function | Description
------|--------|----------|------------
`` | POST | registerEcclesiaCRM | No description

---
## API "search"

   in route : "/api/routes/search.php

Route | Method | function | Description
------|--------|----------|------------
`/{query}` | GET | quickSearch | a search query. Returns all instances of Persons, Families, Groups, Deposits, Checks, Payments that match the search query

* `{ref}`->`string` :: query string as ref

---
Route | Method | function | Description
------|--------|----------|------------
`/comboElements/` | POST | comboElements | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getGroupForTypeID/` | POST | getGroupForTypeID | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getGroupRoleForGroupID/` | POST | getGroupRoleForGroupID | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getresult/` | POST | getSearchResult | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getresult/` | GET | getSearchResult | No description

---
## API "mapicons"

   in route : "/api/routes/sidebar/sidebar-mapicons.php

Route | Method | function | Description
------|--------|----------|------------
`/getall` | POST | getAllMapIcons | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/checkOnlyPersonView` | POST | checkOnlyPersonView | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/setIconName` | POST | setIconName | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeIcon` | POST | removeIcon | No description

---
## API "menulinks"

   in route : "/api/routes/sidebar/sidebar-menulinks.php

Route | Method | function | Description
------|--------|----------|------------
`/{userId:[0-9]+}` | POST | getMenuLinksForUser | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | deleteMenuLink | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/upaction` | POST | upMenuLink | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/downaction` | POST | downMenuLink | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/create` | POST | createMenuLink | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/set` | POST | setMenuLink | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/edit` | POST | editMenuLink | No description

---
## API "properties"

   in route : "/api/routes/sidebar/sidebar-properties.php

Route | Method | function | Description
------|--------|----------|------------
`/persons/assign` | POST | propertiesPersonsAssign | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/persons/unassign` | DELETE | propertiesPersonsUnAssign | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/families/assign` | POST | propertiesFamiliesAssign | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/families/unassign` | DELETE | propertiesFamiliesUnAssign | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/groups/assign` | POST | propertiesGroupsAssign | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/groups/unassign` | DELETE | propertiesGroupsUnAssign | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists` | POST | getAllPropertyTypes | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/edit` | POST | editPropertyType | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/set` | POST | setPropertyType | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/create` | POST | createPropertyType | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/delete` | POST | deletePropertyType | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/edit` | POST | editProperty | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/set` | POST | setProperty | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/delete` | POST | deleteProperty | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/create` | POST | createProperty | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/{type}` | POST | getAllProperties | No description

---
## API "roles"

   in route : "/api/routes/sidebar/sidebar-roles.php

Route | Method | function | Description
------|--------|----------|------------
`/all` | GET | getAllRoles | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/persons/assign` | POST | rolePersonAssign | No description

---
## API "volunteeropportunity"

   in route : "/api/routes/sidebar/sidebar-volunteeropportunity.php

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | getAllVolunteerOpportunities | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | deleteVolunteerOpportunity | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/upaction` | POST | upActionVolunteerOpportunity | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/downaction` | POST | downActionVolunteerOpportunity | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/create` | POST | createVolunteerOpportunity | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/set` | POST | setVolunteerOpportunity | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/edit` | POST | editVolunteerOpportunity | No description

---
## API "pastoralcare"

   in route : "/api/routes/pastoralcare/pastoralcare.php

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | getAllPastoralCare | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletetype` | POST | deletePastoralCareType | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/createtype` | POST | createPastoralCareType | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/settype` | POST | setPastoralCareType | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/edittype` | POST | editPastoralCareType | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/add` | POST | addPastoralCarePerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/delete` | POST | deletePastoralCarePerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/getinfo` | POST | getPastoralCareInfoPerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/modify` | POST | modifyPastoralCarePerson | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/add` | POST | addPastoralCareFamily | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/delete` | POST | deletePastoralCareFamily | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/getinfo` | POST | getPastoralCareInfoFamily | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/family/modify` | POST | modifyPastoralCareFamily | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/members` | POST | pastoralcareMembersDashboard | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/personNeverBeenContacted` | POST | personNeverBeenContacted | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/familyNeverBeenContacted` | POST | familyNeverBeenContacted | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/singleNeverBeenContacted` | POST | singleNeverBeenContacted | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/retiredNeverBeenContacted` | POST | retiredNeverBeenContacted | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/youngNeverBeenContacted` | POST | youngNeverBeenContacted | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getPersonByClassification` | POST | getPersonByClassificationPastoralCare | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getPersonByClassification/{type:[0-9]+}` | POST | getPersonByClassificationPastoralCare | No description

---
## API "meeting"

   in route : "/api/routes/meeting/meeting.php

## API "sundayschool"

   in route : "/api/routes/sundayschool.php

Route | Method | function | Description
------|--------|----------|------------
`/getallstudents/{groupId:[0-9]+}` | POST | getallstudentsForGroup | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getAllGendersForDonut/{groupId:[0-9]+}` | POST | getAllGendersForDonut | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getAllStudentsForChart/{groupId:[0-9]+}` | POST | getAllStudentsForChart | No description

---
## API "system"

   in route : "/api/routes/system/system.php

Route | Method | function | Description
------|--------|----------|------------
`/csp-report` | POST | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deletefile` | POST | function ($request, $response, $args) | No description

---
## API "custom-fields"

   in route : "/api/routes/system/system-custom-fields.php

Route | Method | function | Description
------|--------|----------|------------
`/person` | GET | getPersonFieldsByType | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/person/` | GET | getPersonFieldsByType | No description

---
## API "synchronize"

   in route : "/api/routes/system/system-synchronize.php

Route | Method | function | Description
------|--------|----------|------------
`/page` | GET | function ($request,$response,$args) | Returns the dashboard items in function of the current page name : for CRMJsom.js

* `{page}`->`string` :: current page name

---
## API "database"

   in route : "/api/routes/system/system-database.php

Route | Method | function | Description
------|--------|----------|------------
`/backup` | POST | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/backupRemote` | POST | function($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/restore` | POST | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/download/{filename}` | GET | function ($request, $response, $args) | No description

---
## API "gdrp"

   in route : "/api/routes/system/system-gdrp.php

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | getAllGdprNotes | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/setComment` | POST | setGdprComment | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeperson` | POST | removePersonGdpr | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeallpersons` | POST | removeAllPersonsGdpr | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removefamily` | POST | removeFamilyGdpr | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/removeallfamilies` | POST | removeAllFamiliesGdpr | No description

---
## API "issues"

   in route : "/api/routes/system/system-issues.php

Route | Method | function | Description
------|--------|----------|------------
`/issues` | POST | function ($request, $response, $args) | No description

---
## API "systemupgrade"

   in route : "/api/routes/system/system-system-upgrade.php

Route | Method | function | Description
------|--------|----------|------------
`/downloadlatestrelease` | GET | function () | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/doupgrade` | POST | function ($request, $response, $args) | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/isUpdateRequired` | POST | function ($request, $response, $args) | No description

---
## API "timerjobs"

   in route : "/api/routes/system/system-timerjobs.php

Route | Method | function | Description
------|--------|----------|------------
`/run` | POST | function () | No description

---
## API "userrole"

   in route : "/api/routes/user/user-role.php

Route | Method | function | Description
------|--------|----------|------------
`/add` | POST | addUserRole | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/get` | POST | getUserRole | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/rename` | POST | renameUserRole | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getall` | POST | getAllUserRoles | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | deleteUserRole | No description

---
## API "users"

   in route : "/api/routes/user/user-users.php

Route | Method | function | Description
------|--------|----------|------------
`/{userId:[0-9]+}/password/reset` | POST | passwordReset | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/lockunlock` | POST | lockUnlock | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/showsince` | POST | showSince | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/showto` | POST | showTo | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{userId:[0-9]+}/login/reset` | POST | loginReset | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/{userId:[0-9]+}` | DELETE | deleteUser | No description

---
