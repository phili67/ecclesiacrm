# <center><big>Ecclesia**CRM** documentation de l'API</big></center>
----

Ecclesia**CRM** use Slim 4.10.0 which allow to make api call to the restricted area of the CRM.



## EVENTS & CALENDAR
## API "calendar"

   in route : "/api/routes/calendar/calendar-calendarV2.php"

Route | Method | function | Description
------|--------|----------|------------
`/getallevents` | POST | CalendarV2Controller::class . ':getallCalendarEvents' | Get all events for all calendars for a specified range

* `{ref}`->`date` :: the start date : YYYY-MM-DD
* `{ref}`->`date` :: the end date : YYYY-MM-DD

---
Route | Method | function | Description
------|--------|----------|------------
`/getalleventsForEventsList` | POST | CalendarV2Controller::class . ':getallCalendarEventsForEventsList' | Get all events for all calendars for a specified range

* `{ref}`->`date` :: the start date : YYYY-MM-DD
* `{ref}`->`date` :: the end date : YYYY-MM-DD

---
Route | Method | function | Description
------|--------|----------|------------
`/numberofcalendars` | POST | CalendarV2Controller::class . ':numberOfCalendars' | get all the number of calendar for the current user

---
Route | Method | function | Description
------|--------|----------|------------
`/showhidecalendars` | POST | CalendarV2Controller::class . ':showHideCalendars' | Show Hide calendar

* `{ref}`->`array` :: calIDs
* `{ref}`->`bool` :: isPresent

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
* `{ref}`->`int` :: person ID
* `{ref}`->`bool` :: notification
* `{ref}`->`array` :: calIDs
* `{ref}`->`int` :: family ID
* `{ref}`->`bool` :: notification

---
Route | Method | function | Description
------|--------|----------|------------
`/sharegroup` | POST | CalendarV2Controller::class . ':shareCalendarGroup' | Share a calendar with an entire group

* `{ref}`->`array` :: calIDs
* `{ref}`->`int` :: group ID
* `{ref}`->`bool` :: notification

---
Route | Method | function | Description
------|--------|----------|------------
`/setrights` | POST | CalendarV2Controller::class . ':setCalendarRights' | Share a calendar with an entire group

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

   in route : "/api/routes/calendar/calendar-eventsV2.php"

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
`/types` | GET | CalendarEventV2Controller::class . ":getEventTypes" | Get all events from today

---
Route | Method | function | Description
------|--------|----------|------------
`/names` | GET | CalendarEventV2Controller::class . ":eventNames" | Get all event names

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteeventtype` | POST | CalendarEventV2Controller::class . ":deleteeventtype" | delete event type

* `{ref}`->`int` :: type ID

---
Route | Method | function | Description
------|--------|----------|------------
`/info` | POST | CalendarEventV2Controller::class . ":eventInfo" | get event info

* `{ref}`->`int` :: event ID

---
Route | Method | function | Description
------|--------|----------|------------
`/person` | POST | CalendarEventV2Controller::class . ":personCheckIn" | Set a person for the event + check

* `{ref}`->`int` :: event ID
* `{ref}`->`int` :: person ID

---
Route | Method | function | Description
------|--------|----------|------------
`/group` | POST | CalendarEventV2Controller::class . ":groupCheckIn" | Set the group persons for the event + check

* `{ref}`->`int` :: event ID
* `{ref}`->`int` :: group ID

---
Route | Method | function | Description
------|--------|----------|------------
`/family` | POST | CalendarEventV2Controller::class . ":familyCheckIn" | Set the family persons for the event + check

* `{ref}`->`int` :: event ID
* `{ref}`->`int` :: family ID

---
Route | Method | function | Description
------|--------|----------|------------
`/attendees` | POST | CalendarEventV2Controller::class . ":eventCount" | get event count

* `{ref}`->`int` :: event ID
* `{ref}`->`int` :: type ID

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | CalendarEventV2Controller::class . ":manageEvent" | manage an event eventAction, [createEvent,moveEvent,resizeEvent,attendeesCheckinEvent,suppress,modifyEvent]

* `{ref}`->`int` :: eventID
* `{ref}`->`int` :: type ID
* `{ref}`->`array` :: calendarID
* `{ref}`->`int` :: reccurenceID
* `{ref}`->`start` :: the start date : YYYY-MM-DD
* `{ref}`->`start` :: the end date : YYYY-MM-DD
* `{ref}`->`location` :: location

---
## DOCUMENTS FILES
## API "ckeditor"

   in route : "/api/routes/documents/documents-ckeditor.php"

Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/templates` | GET | DocumentCKEditorController::class . ':templates' | get all templates

---
Route | Method | function | Description
------|--------|----------|------------
`/alltemplates` | POST | DocumentCKEditorController::class . ':alltemplates' | get all templates

* `{ref}`->`id` :: personID

---
Route | Method | function | Description
------|--------|----------|------------
`/deletetemplate` | POST | DocumentCKEditorController::class . ':deleteTemplate' | delete template

* `{ref}`->`int` :: templateID

---
Route | Method | function | Description
------|--------|----------|------------
`/renametemplate` | POST | DocumentCKEditorController::class . ':renametemplate' | rename template

* `{ref}`->`int` :: templateID
* `{ref}`->`string` :: title
* `{ref}`->`string` :: desc

---
Route | Method | function | Description
------|--------|----------|------------
`/savetemplate` | POST | DocumentCKEditorController::class . ':saveTemplate' | save template

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: title
* `{ref}`->`string` :: desc
* `{ref}`->`string` :: text

---
Route | Method | function | Description
------|--------|----------|------------
`/saveAsWordFile` | POST | DocumentCKEditorController::class . ':saveAsWordFile' | save template as word file

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: title
* `{ref}`->`string` :: text

---
## API "document"

   in route : "/api/routes/documents/documents-document.php"

Route | Method | function | Description
------|--------|----------|------------
`/create` | POST | DocumentDocumentController::class . ':createDocument' | create a document

* `{ref}`->`int` :: personID
* `{ref}`->`int` :: famID
* `{ref}`->`string` :: type
* `{ref}`->`string` :: text
* `{ref}`->`bool` :: bPrivate

---
Route | Method | function | Description
------|--------|----------|------------
`/get` | POST | DocumentDocumentController::class . ':getDocument' | get a document

* `{ref}`->`int` :: docID
* `{ref}`->`int` :: personID
* `{ref}`->`int` :: famID

---
Route | Method | function | Description
------|--------|----------|------------
`/update` | POST | DocumentDocumentController::class . ':updateDocument' | update a document

* `{ref}`->`int` :: docID
* `{ref}`->`string` :: title
* `{ref}`->`string` :: type
* `{ref}`->`string` :: text
* `{ref}`->`bool` :: bPrivate

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | DocumentDocumentController::class . ':deleteDocument' | delete a document

* `{ref}`->`int` :: docID

---
Route | Method | function | Description
------|--------|----------|------------
`/leave` | POST | DocumentDocumentController::class . ':leaveDocument' | leave a document (in case of a share document)

* `{ref}`->`int` :: docID

---
## API "filemanager"

   in route : "/api/routes/documents/documents-filemanager.php"

Route | Method | function | Description
------|--------|----------|------------
`/{personID:[0-9]+}` | POST | DocumentFileManagerController::class . ':getAllFileNoteForPerson' | get All the files for personID user

* `{ref}`->`int` :: personID

---
Route | Method | function | Description
------|--------|----------|------------
`/getFile/{personID:[0-9]+}/[{path:.*}]` | GET | DocumentFileManagerController::class . ':getRealFile' | get real file

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: path

---
Route | Method | function | Description
------|--------|----------|------------
`/getPreview` | POST | DocumentFileManagerController::class . ':getPreview' | get preview for file name

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: name

---
Route | Method | function | Description
------|--------|----------|------------
`/changeFolder` | POST | DocumentFileManagerController::class . ':changeFolder' | change to folder name for personID

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: folder

---
Route | Method | function | Description
------|--------|----------|------------
`/folderBack` | POST | DocumentFileManagerController::class . ':folderBack' | change to folder back

* `{ref}`->`int` :: personID

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteOneFolder` | POST | DocumentFileManagerController::class . ':deleteOneFolder' | delete folder

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: folder

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteOneFile` | POST | DocumentFileManagerController::class . ':deleteOneFile' | delete one file

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: file

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteFiles` | POST | DocumentFileManagerController::class . ':deleteFiles' | delete files

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: files

---
Route | Method | function | Description
------|--------|----------|------------
`/movefiles` | POST | DocumentFileManagerController::class . ':movefiles' | move a file to another folder

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: files
* `{ref}`->`string` :: folder

---
Route | Method | function | Description
------|--------|----------|------------
`/newFolder` | POST | DocumentFileManagerController::class . ':newFolder' | create new folder

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: folder

---
Route | Method | function | Description
------|--------|----------|------------
`/rename` | POST | DocumentFileManagerController::class . ':renameFile' | rename file

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: oldName
* `{ref}`->`string` :: newName
* `{ref}`->`string` :: type

---
Route | Method | function | Description
------|--------|----------|------------
`/uploadFile/{personID:[0-9]+}` | POST | DocumentFileManagerController::class . ':uploadFile' | upload file to current folder, everything is contained in $_FILES

---
Route | Method | function | Description
------|--------|----------|------------
`/getRealLink` | POST | DocumentFileManagerController::class . ':getRealLink' | upload : get file to file path

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: pathFile

---
Route | Method | function | Description
------|--------|----------|------------
`/setpathtopublicfolder` | POST | DocumentFileManagerController::class . ':setpathtopublicfolder' | set current path to public folder

---
## API "sharedocument"

   in route : "/api/routes/documents/documents-sharedocument.php"

Route | Method | function | Description
------|--------|----------|------------
`/getallperson` | POST | DocumentShareController::class . ':getAllShareForPerson' | get all shared persons for a noteID (unusefull)

* `{ref}`->`int` :: noteId

---
Route | Method | function | Description
------|--------|----------|------------
`/getallpersonsabre` | POST | DocumentShareController::class . ':getAllShareForPersonSabre' | get all shared persons for all the selected rows (sabre)

* `{ref}`->`int` :: currentPersonID
* `{ref}`->`array` :: rows

---
Route | Method | function | Description
------|--------|----------|------------
`/addperson` | POST | DocumentShareController::class . ':addPersonToShare' | share a note to a personID from currentPersonID 

* `{ref}`->`int` :: personID
* `{ref}`->`int` :: noteId
* `{ref}`->`int` :: currentPersonID
* `{ref}`->`bool` :: notification

---
Route | Method | function | Description
------|--------|----------|------------
`/addpersonsabre` | POST | DocumentShareController::class . ':addPersonSabreToShare' | share a note to a personID from currentPersonID for sabre

* `{ref}`->`int` :: personID
* `{ref}`->`int` :: currentPersonID
* `{ref}`->`array` :: all the rows

---
Route | Method | function | Description
------|--------|----------|------------
`/addfamily` | POST | DocumentShareController::class . ':addFamilyToShare' | share a note to a familyID from currentPersonID 

* `{ref}`->`int` :: familyID
* `{ref}`->`int` :: noteId
* `{ref}`->`int` :: currentPersonID
* `{ref}`->`bool` :: notification

---
Route | Method | function | Description
------|--------|----------|------------
`/addgroup` | POST | DocumentShareController::class . ':addGroupToShare' | share a note to a groupID from currentPersonID 

* `{ref}`->`int` :: groupID
* `{ref}`->`int` :: noteId
* `{ref}`->`int` :: currentPersonID
* `{ref}`->`bool` :: notification

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteperson` | POST | DocumentShareController::class . ':deletePersonFromShare' | remove a personID from a share note 

* `{ref}`->`int` :: personID
* `{ref}`->`array` :: rows

---
Route | Method | function | Description
------|--------|----------|------------
`/deletepersonsabre` | POST | DocumentShareController::class . ':deletePersonSabreFromShare' | remove a personID from a share note 

* `{ref}`->`string` :: personPrincipal
* `{ref}`->`array` :: rows
* `{ref}`->`int` :: currentPersonID

---
Route | Method | function | Description
------|--------|----------|------------
`/setrights` | POST | DocumentShareController::class . ':setRightsForPerson' | set right access to a note 

* `{ref}`->`int` :: personID
* `{ref}`->`int` :: noteId
* `{ref}`->`int` :: rightAccess

---
Route | Method | function | Description
------|--------|----------|------------
`/setrightssabre` | POST | DocumentShareController::class . ':setRightsSabreForPerson' | set right access to a note (sabre)

* `{ref}`->`string` :: currentPersonID : principal/admin
* `{ref}`->`int` :: personID
* `{ref}`->`array` :: rows (the lines)
* `{ref}`->`int` :: rightAccess

---
Route | Method | function | Description
------|--------|----------|------------
`/cleardocument` | POST | DocumentShareController::class . ':clearDocument' | delete a note

* `{ref}`->`int` :: noteId

---
Route | Method | function | Description
------|--------|----------|------------
`/cleardocumentsabre` | POST | DocumentShareController::class . ':cleardocumentsabre' | cleardocument

* `{ref}`->`int` :: personID
* `{ref}`->`int` :: noteId
* `{ref}`->`int` :: rightAccess

---
Route | Method | function | Description
------|--------|----------|------------
`/getShareInfosSabre` | POST | DocumentShareController::class . ':getShareInfosSabre' | get all shared persons for all the selected rows (sabre)

* `{ref}`->`int` :: currentPersonID
* `{ref}`->`array` :: rows

---
## API "deposits"

   in route : "/api/routes/finance/finance-deposits.php"

Route | Method | function | Description
------|--------|----------|------------
`` | POST | FinanceDepositController::class . ':createDeposit' | create a deposit type

* `{ref}`->`string` :: depositType
* `{ref}`->`string` :: depositComment
* `{ref}`->`string` :: depositDate

---
Route | Method | function | Description
------|--------|----------|------------
`` | GET | FinanceDepositController::class . ':getAllDeposits' | get All the deposits if you're a financial

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}` | GET | FinanceDepositController::class . ':getOneDeposit' | get information about one deposit

* `{ref}`->`int` :: id (deposit id)

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}` | POST | FinanceDepositController::class . ':modifyOneDeposit' | modify a deposit

* `{ref}`->`int` :: id (deposit id)
* `{ref}`->`string` :: depositType
* `{ref}`->`string` :: depositComment
* `{ref}`->`string` :: depositDate
* `{ref}`->`bool` :: depositClosed

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}/ofx` | GET | FinanceDepositController::class . ':createDepositOFX' | create an OFX deposit export

* `{ref}`->`int` :: id (deposit id)

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}/pdf` | GET | FinanceDepositController::class . ':createDepositPDF' | create a pdf deposit export

* `{ref}`->`int` :: id (deposit id)

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}/csv` | GET | FinanceDepositController::class . ':createDepositCSV' | create a CSV deposit export

* `{ref}`->`int` :: id (deposit id)

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}` | DELETE | FinanceDepositController::class . ':deleteDeposit' | delete deposit

* `{ref}`->`int` :: id (deposit id)

---
Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}/pledges` | GET | FinanceDepositController::class . ':getAllPledgesForDeposit' | get all the pledges associated to the deposit

* `{ref}`->`int` :: id (deposit id)

---
## API "donationfunds"

   in route : "/api/routes/finance/finance-donationfunds.php"

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | FinanceDonationFundController::class . ':getAllDonationFunds' | get all donation funds

---
Route | Method | function | Description
------|--------|----------|------------
`/edit` | POST | FinanceDonationFundController::class . ':editDonationFund' | get all infos of donation fund to edit a donation fund

* `{ref}`->`int` :: fundId

---
Route | Method | function | Description
------|--------|----------|------------
`/set` | POST | FinanceDonationFundController::class . ':setDonationFund' | set donation fund informations

* `{ref}`->`int` :: fundId
* `{ref}`->`string` :: Name
* `{ref}`->`string` :: Description
* `{ref}`->`bool` :: Activ

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | FinanceDonationFundController::class . ':deleteDonationFund' | remove donation fund by fundId

* `{ref}`->`int` :: fundId

---
Route | Method | function | Description
------|--------|----------|------------
`/create` | POST | FinanceDonationFundController::class . ':createDonationFund' | create donation fund

* `{ref}`->`string` :: Name
* `{ref}`->`string` :: Description
* `{ref}`->`bool` :: Activ

---
## API "payments"

   in route : "/api/routes/finance/finance-payments.php"

Route | Method | function | Description
------|--------|----------|------------
`/{id:[0-9]+}` | GET | FinancePaymentController::class . ':getPayment' | get payment for Id as JSON

* `{ref}`->`int` :: Id

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | FinancePaymentController::class . ':getSubmitOrPayement' | Get submit or Payment

---
Route | Method | function | Description
------|--------|----------|------------
`/byGroupKey` | DELETE | FinancePaymentController::class . ':deletePaymentByGroupKey' | Delete Payment par GroupKey

* `{ref}`->`string` :: Groupkey

---
Route | Method | function | Description
------|--------|----------|------------
`/family` | POST | FinancePaymentController::class . ':getAllPayementsForFamily' | Get all payments for familyId

* `{ref}`->`int` :: famId

---
Route | Method | function | Description
------|--------|----------|------------
`/info` | POST | FinancePaymentController::class . ':getAutoPaymentInfo' | Get auto payment for the author ID

* `{ref}`->`int` :: autID

---
Route | Method | function | Description
------|--------|----------|------------
`/families` | POST | FinancePaymentController::class . ':getAllPayementsForFamilies' | Get all payments for a family

* `{ref}`->`int` :: famId

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | FinancePaymentController::class . ':deletePaymentForFamily' | Delete paymentId for Family

* `{ref}`->`int` :: famId
* `{ref}`->`int` :: paymentId

---
Route | Method | function | Description
------|--------|----------|------------
`/delete/{authID:[0-9]+}` | GET | FinancePaymentController::class . ':deleteAutoPayment' | Delete auto payment

* `{ref}`->`int` :: authID

---
Route | Method | function | Description
------|--------|----------|------------
`/invalidate` | POST | FinancePaymentController::class . ':invalidatePledge' | Invalidate Pledge by Id

* `{ref}`->`int` :: Id

---
Route | Method | function | Description
------|--------|----------|------------
`/validate` | POST | FinancePaymentController::class . ':validatePledge' | Validate Pledge by Id

* `{ref}`->`int` :: Id

---
Route | Method | function | Description
------|--------|----------|------------
`/getchartsarrays` | POST | FinancePaymentController::class . ':getDepositSlipChartsArrays' | Get depositSlip Charts in the View

* `{ref}`->`int` :: depositSlipID

---
## API "pledges"

   in route : "/api/routes/finance/finance-pledges.php"

Route | Method | function | Description
------|--------|----------|------------
`/detail` | POST | FinancePledgeController::class . ':pledgeDetail' | Get Pledge details by groupKey

* `{ref}`->`string` :: groupKey

---
Route | Method | function | Description
------|--------|----------|------------
`/family` | POST | FinancePledgeController::class . ':familyPledges' | Get Family pledges by famId

* `{ref}`->`int` :: famId

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | FinancePledgeController::class . ':deletePledge' | Delete Pledge by payment ID

* `{ref}`->`int` :: paymentId

---
## PEOPLE
## API "attendees"

   in route : "/api/routes/people/people-attendees.php"

Route | Method | function | Description
------|--------|----------|------------
`/event/{eventID:[0-9]+}` | GET | PeopleAttendeesController::class . ':attendeesEvent' | Returns event attendees for eventID

* `{ref}`->`int` :: eventID

---
Route | Method | function | Description
------|--------|----------|------------
`/checkin` | POST | PeopleAttendeesController::class . ':attendeesCheckIn' | checkin a person ID for event ID

* `{ref}`->`int` :: personID
* `{ref}`->`int` :: eventID
* `{ref}`->`bool` :: checked

---
Route | Method | function | Description
------|--------|----------|------------
`/checkout` | POST | PeopleAttendeesController::class . ':attendeesCheckOut' | checkout a person ID for event ID

* `{ref}`->`int` :: personID
* `{ref}`->`int` :: eventID
* `{ref}`->`bool` :: checked

---
Route | Method | function | Description
------|--------|----------|------------
`/student` | POST | PeopleAttendeesController::class . ':attendeesStudent' | Add attendees to current Event or create one with the student groupID + rangeInhours (for the predefined eventTypeID : ie time day)

* `{ref}`->`int` :: eventTypeID
* `{ref}`->`int` :: groupID
* `{ref}`->`string` :: rangeInHours

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | PeopleAttendeesController::class . ':attendeesDelete' | delete Attendee for person ID in event ID

* `{ref}`->`int` :: eventID
* `{ref}`->`int` :: personID

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteAll` | POST | PeopleAttendeesController::class . ':attendeesDeleteAll' | delete all Attendees for event ID

* `{ref}`->`int` :: eventID

---
Route | Method | function | Description
------|--------|----------|------------
`/checkAll` | POST | PeopleAttendeesController::class . ':attendeesCheckAll' | check all Attendees for event ID

* `{ref}`->`int` :: eventID
* `{ref}`->`int` :: type (1: checkin only, 2: checkin+checkout if $eventAttent->getCheckinDate() )

---
Route | Method | function | Description
------|--------|----------|------------
`/uncheckAll` | POST | PeopleAttendeesController::class . ':attendeesUncheckAll' | uncheck all Attendees for event ID

* `{ref}`->`int` :: eventID
* `{ref}`->`int` :: type (1: un-checkin only, 2: un-checkin+un-checkout)

---
Route | Method | function | Description
------|--------|----------|------------
`/groups` | POST | PeopleAttendeesController::class . ':attendeesGroups' | Add attendees all the sunday groups with eventTypeID + rangeInhours at dateTime (for the predefined eventTypeID : ie time day)

* `{ref}`->`int` :: eventTypeID
* `{ref}`->`string` :: dateTime
* `{ref}`->`string` :: rangeInHours

---
Route | Method | function | Description
------|--------|----------|------------
`/deletePerson` | POST | PeopleAttendeesController::class . ':deleteAttendeesPerson' | remove a person ID attendee from event ID

* `{ref}`->`int` :: personID
* `{ref}`->`string` :: eventID

---
Route | Method | function | Description
------|--------|----------|------------
`/addPerson` | POST | PeopleAttendeesController::class . ':addAttendeesPerson' | Add a person ID attendee to event ID (with the two possibilities iChildID | iAdultID)

* `{ref}`->`int` :: iChildID
* `{ref}`->`int` :: iAdultID
* `{ref}`->`string` :: eventID

---
Route | Method | function | Description
------|--------|----------|------------
`/validate` | POST | PeopleAttendeesController::class . ':validateAttendees' | validate the event to close it definitely

* `{ref}`->`int` :: eventID
* `{ref}`->`string` :: noteText

---
Route | Method | function | Description
------|--------|----------|------------
`/checkoutValidate` | POST | PeopleAttendeesController::class . ':checkoutValidateAttendees' | validate with checkout the event to close it definitely

* `{ref}`->`int` :: eventID

---
Route | Method | function | Description
------|--------|----------|------------
`/addFreeAttendees` | POST | PeopleAttendeesController::class . ':addFreeAttendees' | add free attendees to the event

* `{ref}`->`int` :: eventID
* `{ref}`->`string` :: fieldText
* `{ref}`->`int` :: counts

---
Route | Method | function | Description
------|--------|----------|------------
`/qrcodeCall` | POST | PeopleAttendeesController::class . ':qrcodeCallAttendees' | checkin or checkout a person in group ID in reference of the current event ($_SESSION['EventID'] or if the event is create in the same day)

* `{ref}`->`int` :: groupID
* `{ref}`->`string` :: personID

---
## API "families"

   in route : "/api/routes/people/people-families.php"

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
Route | Method | function | Description
------|--------|----------|------------
`/reset/{state}` | POST | PeopleFamilyController::class . ":resetConfirmDatas" | Move down the family custom field

* `{id}`->`int` :: orderID as id
* `{id}`->`int` :: field as id
* `{id}`->`string` :: state (pending | done)

---
## API "groups"

   in route : "/api/routes/people/people-groups.php"

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | PeopleGroupController::class . ":getAllGroups" | Get all the Groups

---
Route | Method | function | Description
------|--------|----------|------------
`/groupproperties/{groupID:[0-9]+}` | POST | PeopleGroupController::class . ":groupproperties" | Get the first Group of the list

---
Route | Method | function | Description
------|--------|----------|------------
`/addressbook/extract/{groupId:[0-9]+}` | GET | PeopleGroupController::class . ":addressBook" | get addressbook from a groupID through the url

* `{id}`->`int` :: groupId

---
Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | PeopleGroupController::class . ":searchGroup" | search informations in the group

* `{id}`->`string` :: query

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteAllManagers` | POST | PeopleGroupController::class . ":deleteAllManagers" | delete all managers of a groupId

* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteManager` | POST | PeopleGroupController::class . ":deleteManager" | delete a manager (personID) of a group (groupId)

* `{id}`->`int` :: personID
* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/getmanagers` | POST | PeopleGroupController::class . ":getManagers" | get group managers of a group (groupId)

* `{id}`->`int` :: personID

---
Route | Method | function | Description
------|--------|----------|------------
`/addManager` | POST | PeopleGroupController::class . ":addManager" | get group managers of a group (groupId)

* `{id}`->`int` :: personID
* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/groupsInCart` | GET | PeopleGroupController::class . ":groupsInCart" | get group managers of a group (groupId)

* `{id}`->`int` :: personID
* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | PeopleGroupController::class . ":newGroup" | create a new group

* `{id}`->`int` :: isSundaySchool
* `{id}`->`string` :: groupName

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}` | POST | PeopleGroupController::class . ":updateGroup" | create a new group

* `{id}`->`int` :: groupID
* `{id}`->`int` :: isSundaySchool
* `{id}`->`int` :: groupType
* `{id}`->`string` :: description

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}` | GET | PeopleGroupController::class . ":groupInfo" | group info

* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/cartStatus` | GET | PeopleGroupController::class . ":groupCartStatus" | get group cart status

* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}` | DELETE | PeopleGroupController::class . ":deleteGroup" | delete a group

* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/members` | GET | PeopleGroupController::class . ":groupMembers" | get all group members

* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/events` | GET | PeopleGroupController::class . ":groupEvents" | get all group members

* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/removeperson/{personID:[0-9]+}` | DELETE | PeopleGroupController::class . ":removePersonFromGroup" | remove one person from the group

* `{id}`->`int` :: groupID
* `{ref}`->`int` :: personID

---
Route | Method | function | Description
------|--------|----------|------------
`/removeselectedpersons` | DELETE | PeopleGroupController::class . ":removeSelectedPersons" | remove all selected members of the group

* `{id}`->`int` :: groupID
* `{ref}`->`array` :: Persons id in array ref (possible value)

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/addperson/{personID:[0-9]+}` | POST | PeopleGroupController::class . ":addPersonToGroup" | add a member of the group

* `{id}`->`int` :: groupID
* `{ref}`->`int` :: person id 

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/addteacher/{personID:[0-9]+}` | POST | PeopleGroupController::class . ":addTeacherToGroup" | add a add teacher of the group

* `{id}`->`int` :: groupID
* `{ref}`->`int` :: person id 

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/userRole/{personID:[0-9]+}` | POST | PeopleGroupController::class . ":userRoleByPersonId" | set person role in the group

* `{id}`->`int` :: groupID
* `{ref}`->`int` :: person id 

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles/{roleID:[0-9]+}` | POST | PeopleGroupController::class . ":rolesByRoleId" | set role id in the group

* `{id}`->`int` :: groupID
* `{ref}`->`int` :: role id 

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles` | GET | PeopleGroupController::class . ":allRoles" | get all role the group

* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/defaultRole` | POST | PeopleGroupController::class . ":defaultRoleForGroup" | get default role in the group

* `{id}`->`int` :: groupID

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles/{roleID:[0-9]+}` | DELETE | PeopleGroupController::class . ":deleteRole" | delete role id in the group

* `{id}`->`int` :: groupID
* `{ref}`->`int` :: role id 

---
Route | Method | function | Description
------|--------|----------|------------
`/{groupID:[0-9]+}/roles` | POST | PeopleGroupController::class . ":roles" | add group role name

* `{id}`->`int` :: groupID
* `{ref}`->`string` :: roleName

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
## API "people"

   in route : "/api/routes/people/people.php"

Route | Method | function | Description
------|--------|----------|------------
`/searchonlyperson/{query}` | GET | PeopleController::class . ':searchonlyperson' | Returns a list of the person who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/searchonlyuser/{query}` | GET | PeopleController::class . ':searchonlyuser' | Returns a list of the person who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/searchonlyuserwithedrive/{query}` | GET | PeopleController::class . ':searchonlyuserwithedrive' | Returns a list of the person who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | PeopleController::class . ':searchpeople' | Returns a list of the members/families/groups who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/search/{query}/{type}` | GET | PeopleController::class . ':searchpeople' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/classifications/all` | GET | PeopleController::class . ':getAllClassifications' | Returns all classifications

---
Route | Method | function | Description
------|--------|----------|------------
`/person/classification/assign` | POST | PeopleController::class . ':postPersonClassification' | Returns all classifications

---
## API "persons"

   in route : "/api/routes/people/people-persons.php"

Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | PeoplePersonController::class . ":searchPerson" | Returns a list of the persons who's first name or last name matches the :query parameter

* `{ref}`->`string` :: query string ref

---
Route | Method | function | Description
------|--------|----------|------------
`/sundayschool/search/{query}` | GET | PeoplePersonController::class . ":searchSundaySchoolPerson" | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/cart/view` | GET | PeoplePersonController::class . ":personCartView" | Returns a list of the persons who are in the cart

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/verify` | POST | PeoplePersonController::class . ":verifyPerson" | Verify the person for the personId

* `{id}`->`int` :: personId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/verifyPDF` | POST | PeoplePersonController::class . ":verifyPersonPDF" | Verify the person for the personId

* `{id}`->`int` :: personId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/verify/{personId:[0-9]+}/now` | POST | PeoplePersonController::class . ":verifyPersonNow" | Verify the person for the personId now

* `{id}`->`int` :: personId as id

---
Route | Method | function | Description
------|--------|----------|------------
`/verify/url` | POST | PeoplePersonController::class . ':verifyPersonURL' | Verify the family for the familyId now

* `{id}`->`int` :: family

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
`/personproperties/{personID:[0-9]+}` | POST | PeoplePersonController::class . ":personpropertiesPerPersonId" | Return assigned properties for a person

* `{id}`->`int` :: personId

---
Route | Method | function | Description
------|--------|----------|------------
`/numbers` | GET | PeoplePersonController::class . ":numbersOfBirthDates" | Return Number of BirthDates

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/photo` | GET | PeoplePersonController::class . ":photo" | get person photo

* `{id}`->`int` :: personId

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/thumbnail` | GET | PeoplePersonController::class . ":thumbnail" | get person thumbnail

* `{id}`->`int` :: personId

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/photo` | POST | PeoplePersonController::class . ":postPersonPhoto" | Set person photo

* `{id}`->`int` :: personId
* `{id}`->`string` :: imgBase64

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/photo` | DELETE | PeoplePersonController::class . ":deletePersonPhoto" | delete person photo

* `{id}`->`int` :: personId

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}/addToCart` | POST | PeoplePersonController::class . ":addPersonToCart" | add person to cart

* `{id}`->`int` :: personId

---
Route | Method | function | Description
------|--------|----------|------------
`/{personId:[0-9]+}` | DELETE | PeoplePersonController::class . ":deletePerson" | delete person

* `{id}`->`int` :: personId

---
Route | Method | function | Description
------|--------|----------|------------
`/deletefield` | POST | PeoplePersonController::class . ":deletePersonField" | delete person field

* `{id}`->`int` :: orderID
* `{id}`->`int` :: field

---
Route | Method | function | Description
------|--------|----------|------------
`/upactionfield` | POST | PeoplePersonController::class . ":upactionPersonfield" | up action person field

* `{id}`->`int` :: orderID
* `{id}`->`int` :: field

---
Route | Method | function | Description
------|--------|----------|------------
`/downactionfield` | POST | PeoplePersonController::class . ":downactionPersonfield" | down action person field

* `{id}`->`int` :: orderID
* `{id}`->`int` :: field

---
Route | Method | function | Description
------|--------|----------|------------
`/duplicate/emails` | GET | PeoplePersonController::class . ":duplicateEmails" | duplicate emails in mailchimp

---
Route | Method | function | Description
------|--------|----------|------------
`/NotInMailChimp/emails/{type}` | GET | PeoplePersonController::class . ":notInMailChimpEmails" | not in email for mailchimp

---
Route | Method | function | Description
------|--------|----------|------------
`/saveNoteAsWordFile` | POST | PeoplePersonController::class . ":saveNoteAsWordFile" | Export note as word file

* `{id}`->`int` :: personId
* `{id}`->`int` :: noteId

---
Route | Method | function | Description
------|--------|----------|------------
`/reset/{state}` | POST | PeoplePersonController::class . ":resetConfirmDatas" | Export vCard for the current user

* `{id}`->`int` :: personId
* `{id}`->`string` :: state (pending | done)

---
## PUBLIC API
## API "data"

   in route : "/api/routes/public/public-data.php"

Route | Method | function | Description
------|--------|----------|------------
`/countries` | GET | PublicDataController::class . ':getCountries' | get all countries

---
Route | Method | function | Description
------|--------|----------|------------
`/countries/` | GET | PublicDataController::class . ':getCountries' | get all countries

---
Route | Method | function | Description
------|--------|----------|------------
`/countries/{countryCode}/states` | GET | PublicDataController::class . ':getStates' | Get all States

* `{ref}`->`int` :: countryCode

---
Route | Method | function | Description
------|--------|----------|------------
`/countries/{countryCode}/states/` | GET | PublicDataController::class . ':getStates' | Get all States

* `{ref}`->`int` :: countryCode

---
## API "register"

   in route : "/api/routes/public/public-register.php"

Route | Method | function | Description
------|--------|----------|------------
`` | POST | PublicRegisterController::class . ':registerEcclesiaCRM' | register EcclesiaCRM (Admin role)

* `{ref}`->`string` :: EcclesiaCRMURL
* `{ref}`->`string` :: emailmessage

---
## SEARCH MANAGEMENT
## API "search"

   in route : "/api/routes/search.php"

Route | Method | function | Description
------|--------|----------|------------
`/{query}` | GET | SearchController::class . ':quickSearch' | a search query. Returns all instances of Persons, Families, Groups, Deposits, Checks, Payments that match the search query

* `{ref}`->`string` :: query string as ref
* `{ref}`->`string` :: query

---
Route | Method | function | Description
------|--------|----------|------------
`/getresultbyname/{query}` | GET | SearchController::class . ':getSearchResultByName' | Main search for all options : *, famillies, persons, etc ...

* `{ref}`->`string` :: query

---
Route | Method | function | Description
------|--------|----------|------------
`/comboElements/` | POST | SearchController::class . ':comboElements' | Combo elements : whe we search by *, you can add options like Gender, Classification, FamilyRole, etc ....

* `{ref}`->`string` :: query

---
Route | Method | function | Description
------|--------|----------|------------
`/getGroupForTypeID/` | POST | SearchController::class . ':getGroupForTypeID' | Search for group typ

* `{ref}`->`string` :: GroupType

---
Route | Method | function | Description
------|--------|----------|------------
`/getGroupRoleForGroupID/` | POST | SearchController::class . ':getGroupRoleForGroupID' | Get group role for Group ID

* `{ref}`->`int` :: Group

---
Route | Method | function | Description
------|--------|----------|------------
`/getresult/` | POST | SearchController::class . ':getSearchResult' | Get search result for the main seach view

* `{ref}`->`string` :: query

---
Route | Method | function | Description
------|--------|----------|------------
`/getresult/` | GET | SearchController::class . ':getSearchResult' | No description

---
## SIDE BAR ADMIN
## API "general roles"

   in route : "/api/routes/sidebar/sidebar-general-roles.php"

Route | Method | function | Description
------|--------|----------|------------
`/all/{mode}` | GET | SidebarGeneralRolesController::class . ':getAllGeneralRoles' | get all general roles

* `{ref}`->`str` :: mode 'famroles' 'classes' 'grptypes' 'grptypesSundSchool' 'famcustom' 'groupcustom' ('grproles' dead code)

---
Route | Method | function | Description
------|--------|----------|------------
`/action` | POST | SidebarGeneralRolesController::class . ':generalRoleAssign' | set gerneral role for the family, classification, etc ...

* `{ref}`->`str` :: mode 'famroles' 'classes' 'grptypes' 'grptypesSundSchool' 'famcustom' 'groupcustom' ('grproles' dead code)
* `{ref}`->`int` :: Order
* `{id}`->`int` :: ID as id
* `{res}`->`str` :: Action 'up' 'down'

---
## API "mapicons"

   in route : "/api/routes/sidebar/sidebar-mapicons.php"

Route | Method | function | Description
------|--------|----------|------------
`/getall` | POST | SidebarMapIconsController::class . ':getAllMapIcons' | get all map icons

---
Route | Method | function | Description
------|--------|----------|------------
`/checkOnlyPersonView` | POST | SidebarMapIconsController::class . ':checkOnlyPersonView' | check only person view

* `{ref}`->`bool` :: onlyPersonView
* `{ref}`->`int` :: lstID
* `{ref}`->`int` :: lstOptionID

---
Route | Method | function | Description
------|--------|----------|------------
`/setIconName` | POST | SidebarMapIconsController::class . ':setIconName' | set Icon By name

* `{ref}`->`str` :: name
* `{ref}`->`int` :: lstID
* `{ref}`->`int` :: lstOptionID

---
Route | Method | function | Description
------|--------|----------|------------
`/removeIcon` | POST | SidebarMapIconsController::class . ':removeIcon' | remove icon

* `{ref}`->`int` :: lstID
* `{ref}`->`int` :: lstOptionID

---
## API "menulinks"

   in route : "/api/routes/sidebar/sidebar-menulinks.php"

## API "properties"

   in route : "/api/routes/sidebar/sidebar-properties.php"

Route | Method | function | Description
------|--------|----------|------------
`/persons/assign` | POST | SidebarPropertiesController::class . ':propertiesPersonsAssign' | Assign property to a person

* `{ref}`->`int` :: PersonId
* `{ref}`->`int` :: PropertyId
* `{ref}`->`string` :: PropertyValue

---
Route | Method | function | Description
------|--------|----------|------------
`/persons/unassign` | DELETE | SidebarPropertiesController::class . ':propertiesPersonsUnAssign' | Delete : un-assign property to a person

* `{ref}`->`int` :: PersonId
* `{ref}`->`int` :: PropertyId

---
Route | Method | function | Description
------|--------|----------|------------
`/families/assign` | POST | SidebarPropertiesController::class . ':propertiesFamiliesAssign' | Assign property to a family

* `{ref}`->`int` :: FamilyId
* `{ref}`->`int` :: PropertyId
* `{ref}`->`string` :: PropertyValue

---
Route | Method | function | Description
------|--------|----------|------------
`/families/unassign` | DELETE | SidebarPropertiesController::class . ':propertiesFamiliesUnAssign' | Delete : un-assign property to a family

* `{ref}`->`int` :: FamilyId
* `{ref}`->`int` :: PropertyId

---
Route | Method | function | Description
------|--------|----------|------------
`/groups/assign` | POST | SidebarPropertiesController::class . ':propertiesGroupsAssign' | Assign property to a Group

* `{ref}`->`int` :: GroupId
* `{ref}`->`int` :: PropertyId
* `{ref}`->`string` :: PropertyValue

---
Route | Method | function | Description
------|--------|----------|------------
`/groups/unassign` | DELETE | SidebarPropertiesController::class . ':propertiesGroupsUnAssign' | Delete : un-assign property to a group

* `{ref}`->`int` :: GroupId
* `{ref}`->`int` :: PropertyId

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists` | POST | SidebarPropertiesController::class . ':getAllPropertyTypes' | get all propery types

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/edit` | POST | SidebarPropertiesController::class . ':editPropertyType' | get all datas for a property type ID

* `{ref}`->`int` :: typeId

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/set` | POST | SidebarPropertiesController::class . ':setPropertyType' | set all datas for a property type ID

* `{ref}`->`int` :: typeId
* `{ref}`->`string` :: Name
* `{ref}`->`string` :: Description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/create` | POST | SidebarPropertiesController::class . ':createPropertyType' | create property type

* `{ref}`->`string` :: Class
* `{ref}`->`string` :: Name
* `{ref}`->`string` :: Description

---
Route | Method | function | Description
------|--------|----------|------------
`/propertytypelists/delete` | POST | SidebarPropertiesController::class . ':deletePropertyType' | delete property type

* `{ref}`->`id` :: typeId

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/edit` | POST | SidebarPropertiesController::class . ':editProperty' | get property datas for type Id

* `{ref}`->`id` :: typeId

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/set` | POST | SidebarPropertiesController::class . ':setProperty' | get property datas for type Id

* `{ref}`->`int` :: typeId
* `{ref}`->`string` :: Name
* `{ref}`->`string` :: Description
* `{ref}`->`string` :: Prompt

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/delete` | POST | SidebarPropertiesController::class . ':deleteProperty' | delete property

* `{ref}`->`id` :: typeId

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/create` | POST | SidebarPropertiesController::class . ':createProperty' | create property

* `{ref}`->`string` :: Class
* `{ref}`->`string` :: Name
* `{ref}`->`string` :: Description
* `{ref}`->`string` :: Prompt

---
Route | Method | function | Description
------|--------|----------|------------
`/typelists/{type}` | POST | SidebarPropertiesController::class . ':getAllProperties' | get all properties

---
## API "roles"

   in route : "/api/routes/sidebar/sidebar-roles.php"

Route | Method | function | Description
------|--------|----------|------------
`/all` | GET | SidebarRolesController::class . ':getAllRoles' | get all roles

---
Route | Method | function | Description
------|--------|----------|------------
`/persons/assign` | POST | SidebarRolesController::class . ':rolePersonAssign' | get all roles

* `{ref}`->`string` :: Description

---
## API "volunteeropportunity"

   in route : "/api/routes/sidebar/sidebar-volunteeropportunity.php"

## PASTORAL CARE
## API "pastoralcare"

   in route : "/api/routes/pastoralcare/pastoralcare.php"

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | PastoralCareController::class . ':getAllPastoralCare' | Get all pastoral care for User ID (person)

* `{ref}`->`int` :: UserID

---
Route | Method | function | Description
------|--------|----------|------------
`/deletetype` | POST | PastoralCareController::class . ':deletePastoralCareType' | delete pastoral care type

* `{ref}`->`int` :: pastoralCareTypeId

---
Route | Method | function | Description
------|--------|----------|------------
`/createtype` | POST | PastoralCareController::class . ':createPastoralCareType' | create pastoral care type

* `{ref}`->`bool` :: Visible
* `{ref}`->`string` :: Title
* `{ref}`->`string` :: Description

---
Route | Method | function | Description
------|--------|----------|------------
`/settype` | POST | PastoralCareController::class . ':setPastoralCareType' | modify and set pastoral care type

* `{ref}`->`int` :: pastoralCareTypeId
* `{ref}`->`bool` :: Visible
* `{ref}`->`string` :: Title
* `{ref}`->`string` :: Description

---
Route | Method | function | Description
------|--------|----------|------------
`/edittype` | POST | PastoralCareController::class . ':editPastoralCareType' | get pastoral care type infos

* `{ref}`->`int` :: pastoralCareTypeId

---
Route | Method | function | Description
------|--------|----------|------------
`/person/add` | POST | PastoralCareController::class . ':addPastoralCarePerson' | create new pastoral care for a person

* `{ref}`->`int` :: typeID
* `{ref}`->`int` :: personID
* `{ref}`->`int` :: currentPastorId
* `{ref}`->`bool` :: visibilityStatus
* `{ref}`->`string` :: noteText

---
Route | Method | function | Description
------|--------|----------|------------
`/person/delete` | POST | PastoralCareController::class . ':deletePastoralCarePerson' | delete pastoral care for a person ID

* `{ref}`->`int` :: ID

---
Route | Method | function | Description
------|--------|----------|------------
`/person/getinfo` | POST | PastoralCareController::class . ':getPastoralCareInfoPerson' | get pastoral care infos for a person ID

* `{ref}`->`int` :: ID

---
Route | Method | function | Description
------|--------|----------|------------
`/person/modify` | POST | PastoralCareController::class . ':modifyPastoralCarePerson' | get pastoral care for a person ID

* `{ref}`->`int` :: ID
* `{ref}`->`int` :: typeID
* `{ref}`->`int` :: personID
* `{ref}`->`int` :: currentPastorId
* `{ref}`->`bool` :: visibilityStatus
* `{ref}`->`string` :: noteText

---
Route | Method | function | Description
------|--------|----------|------------
`/family/add` | POST | PastoralCareController::class . ':addPastoralCareFamily' | create new pastoral care for a family

* `{ref}`->`int` :: typeID
* `{ref}`->`int` :: familyID
* `{ref}`->`int` :: currentPastorId
* `{ref}`->`bool` :: visibilityStatus
* `{ref}`->`string` :: noteText
* `{ref}`->`bool` :: includeFamMembers

---
Route | Method | function | Description
------|--------|----------|------------
`/family/delete` | POST | PastoralCareController::class . ':deletePastoralCareFamily' | delete pastoral care for a family ID

* `{ref}`->`int` :: ID

---
Route | Method | function | Description
------|--------|----------|------------
`/family/getinfo` | POST | PastoralCareController::class . ':getPastoralCareInfoFamily' | get pastoral care for a family ID

* `{ref}`->`int` :: ID

---
Route | Method | function | Description
------|--------|----------|------------
`/family/modify` | POST | PastoralCareController::class . ':modifyPastoralCareFamily' | modify pastoral care for a family ID

* `{ref}`->`int` :: ID
* `{ref}`->`int` :: typeID
* `{ref}`->`int` :: familyID
* `{ref}`->`int` :: currentPastorId
* `{ref}`->`bool` :: visibilityStatus
* `{ref}`->`string` :: noteText

---
Route | Method | function | Description
------|--------|----------|------------
`/members` | POST | PastoralCareController::class . ':pastoralcareMembersDashboard' | get all pastoral cares for all the members in the sPastoralcarePeriod (see for this the settings infos)

---
Route | Method | function | Description
------|--------|----------|------------
`/personNeverBeenContacted` | POST | PastoralCareController::class . ':personNeverBeenContacted' | get the persons never been contacted sPastoralcarePeriod (see for this the settings infos)

---
Route | Method | function | Description
------|--------|----------|------------
`/familyNeverBeenContacted` | POST | PastoralCareController::class . ':familyNeverBeenContacted' | get the families never been contacted sPastoralcarePeriod (see for this the settings infos)

---
Route | Method | function | Description
------|--------|----------|------------
`/singleNeverBeenContacted` | POST | PastoralCareController::class . ':singleNeverBeenContacted' | get the single persons never been contacted sPastoralcarePeriod (see for this the settings infos)

---
Route | Method | function | Description
------|--------|----------|------------
`/retiredNeverBeenContacted` | POST | PastoralCareController::class . ':retiredNeverBeenContacted' | get the retired persons never been contacted sPastoralcarePeriod (see for this the settings infos)

---
Route | Method | function | Description
------|--------|----------|------------
`/youngNeverBeenContacted` | POST | PastoralCareController::class . ':youngNeverBeenContacted' | get the young persons never been contacted sPastoralcarePeriod (see for this the settings infos)

---
Route | Method | function | Description
------|--------|----------|------------
`/getPersonByClassification` | POST | PastoralCareController::class . ':getPersonByClassificationPastoralCare' | get the young persons never been contacted sPastoralcarePeriod (see for this the settings infos)

* `{ref}`->`int` :: typeID (1 : person, 2: family, 3: retired, 4: young person, 5: single person

---
Route | Method | function | Description
------|--------|----------|------------
`/getPersonByClassification/{type:[0-9]+}` | POST | PastoralCareController::class . ':getPersonByClassificationPastoralCare' | get the persons never been reached for the last period (sPastoralcarePeriod)

* `{ref}`->`int` :: type (1: yet contacted)

---
Route | Method | function | Description
------|--------|----------|------------
`/getlistforuser/{UserID:[0-9]+}` | GET | PastoralCareController::class . ':getPastoralCareListForUser' | get the pastoral care user in period for pastor current user ID in current period (sPastoralcarePeriod)

* `{ref}`->`int` :: UserID

---
## SUNDAY SCHOOL
## API "sundayschool"

   in route : "/api/routes/sundayschool.php"

Route | Method | function | Description
------|--------|----------|------------
`/getallstudents/{groupId:[0-9]+}` | POST | SundaySchoolController::class . ':getallstudentsForGroup' | Get all students for Group ID

* `{ref}`->`int` :: groupId

---
Route | Method | function | Description
------|--------|----------|------------
`/getAllGendersForDonut/{groupId:[0-9]+}` | POST | SundaySchoolController::class . ':getAllGendersForDonut' | Get all genders for Group ID (to draw the donut)

* `{ref}`->`int` :: groupId

---
Route | Method | function | Description
------|--------|----------|------------
`/getAllStudentsForChart/{groupId:[0-9]+}` | POST | SundaySchoolController::class . ':getAllStudentsForChart' | Get all students for Group ID (to draw the chart)

* `{ref}`->`int` :: groupId

---
## SYSTEM
## API "custom-fields"

   in route : "/api/routes/system/system-custom-fields.php"

Route | Method | function | Description
------|--------|----------|------------
`/person` | GET | SystemCustomFieldController::class . ':getPersonFieldsByType' | Get person field type (public)

* `{ref}`->`int` :: typeId

---
Route | Method | function | Description
------|--------|----------|------------
`/person/` | GET | SystemCustomFieldController::class . ':getPersonFieldsByType' | Get person field type (public)

* `{ref}`->`int` :: typeId

---
## API "database"

   in route : "/api/routes/system/system-database.php"

Route | Method | function | Description
------|--------|----------|------------
`/restore` | POST | SystemBackupRestoreController::class . ':restore' | backup crm (admin)

* `{ref}`->`int` :: iArchiveType
* `{ref}`->`int` :: iRemote
* `{ref}`->`int` :: iArchiveType
* `{ref}`->`bool` :: bEncryptBackup,
* `{ref}`->`string` :: password
* `{ref}`->`string` :: restoreFile

---
Route | Method | function | Description
------|--------|----------|------------
`/download/{filename}` | GET | SystemBackupRestoreController::class . ':download' | Download update (admin)

* `{ref}`->`string` :: filename

---
Route | Method | function | Description
------|--------|----------|------------
`/people/clear` | DELETE | SystemBackupRestoreController::class . ':clearPeopleTables' | Clear all people from the database (admin)

* `{ref}`->`string` :: filename

---
Route | Method | function | Description
------|--------|----------|------------
`/backup/result` | GET | SystemBackupRestoreController::class . ':getBackupResult' | Clear all people from the database (admin)

* `{ref}`->`string` :: filename

---
## API "gdrp"

   in route : "/api/routes/system/system-gdrp.php"

Route | Method | function | Description
------|--------|----------|------------
`/` | POST | SystemGDRPController::class . ':getAllGdprNotes' | Get all GDPR notes for each custom fields

---
Route | Method | function | Description
------|--------|----------|------------
`/setComment` | POST | SystemGDRPController::class . ':setGdprComment' | Set GDPR note (comment)

* `{ref}`->`int` :: custom_id
* `{ref}`->`string` :: comment
* `{ref}`->`int` :: type 'person', 'personCustom', 'personProperty', 'family', 'familyCustom'

---
Route | Method | function | Description
------|--------|----------|------------
`/removeperson` | POST | SystemGDRPController::class . ':removePersonGdpr' | remove a person for gdpr by person ID

* `{ref}`->`int` :: personId

---
Route | Method | function | Description
------|--------|----------|------------
`/removeallpersons` | POST | SystemGDRPController::class . ':removeAllPersonsGdpr' | Remove all persons

---
Route | Method | function | Description
------|--------|----------|------------
`/removefamily` | POST | SystemGDRPController::class . ':removeFamilyGdpr' | remove a fmaily for gdpr by family ID

* `{ref}`->`int` :: familyId

---
Route | Method | function | Description
------|--------|----------|------------
`/removeallfamilies` | POST | SystemGDRPController::class . ':removeAllFamiliesGdpr' | Remove all families

---
## API "individual settings"

   in route : "/api/routes/system/system-setting-individual.php"

Route | Method | function | Description
------|--------|----------|------------
`/get2FA` | POST | SystemSettingsIndividualController::class . ':get2FA' | Get 2FA key

---
Route | Method | function | Description
------|--------|----------|------------
`/verify2FA` | POST | SystemSettingsIndividualController::class . ':verify2FA' | Verify 2FA

* `{ref}`->`string` :: code

---
Route | Method | function | Description
------|--------|----------|------------
`/remove2FA` | POST | SystemSettingsIndividualController::class . ':remove2FA' | Remove 2FA for session user

---
## API "issues"

   in route : "/api/routes/system/system-issues.php"

Route | Method | function | Description
------|--------|----------|------------
`/issues` | POST | SystemIssueController::class . ':issues' | Sending an issue (public)

* `{ref}`->`int` :: iArchiveType

---
## API "synchronize"

   in route : "/api/routes/system/system-synchronize.php"

Route | Method | function | Description
------|--------|----------|------------
`/page` | POST | SystemSynchronizeController::class . ':synchronize' | Returns the dashboard items in function of the current page name : for CRMJsom.js

* `{page}`->`string` :: current page name

---
## API "system"

   in route : "/api/routes/system/system.php"

Route | Method | function | Description
------|--------|----------|------------
`/csp-report` | POST | SystemController::class . ':cspReport' | send csp report

---
Route | Method | function | Description
------|--------|----------|------------
`/deletefile` | POST | SystemController::class . ':deleteFile' | delete a file

* `{ref}`->`string` :: name
* `{ref}`->`string` :: path

---
Route | Method | function | Description
------|--------|----------|------------
`/testEmailConnection` | POST | SystemController::class . ':testEmailConnectionMVC' | Test if email connection is available

---
## API "systemupgrade"

   in route : "/api/routes/system/system-system-upgrade.php"

Route | Method | function | Description
------|--------|----------|------------
`/downloadlatestrelease` | GET | SystemUpgradeController::class . ':downloadlatestrelease' | Download latest release

---
Route | Method | function | Description
------|--------|----------|------------
`/doupgrade` | POST | SystemUpgradeController::class . ':doupgrade' | Do upgrade system to latest

* `{ref}`->`string` :: fullPath
* `{ref}`->`string` :: sha1

---
Route | Method | function | Description
------|--------|----------|------------
`/isUpdateRequired` | POST | SystemUpgradeController::class . ':isUpdateRequired' | Test if update is required : return

---
## API "timerjobs"

   in route : "/api/routes/system/system-timerjobs.php"

Route | Method | function | Description
------|--------|----------|------------
`/run` | POST | TimerJobsController::class . ':runTimerJobs' | get all running timer jobs

---
## USER PROFILE
## API "userrole"

   in route : "/api/routes/user/user-role.php"

Route | Method | function | Description
------|--------|----------|------------
`/add` | POST | UserRoleController::class . ':addUserRole' | Add new role by name, global etc ...

---
Route | Method | function | Description
------|--------|----------|------------
`/get` | POST | UserRoleController::class . ':getUserRole' | Get role by name, global etc ...

---
Route | Method | function | Description
------|--------|----------|------------
`/rename` | POST | UserRoleController::class . ':renameUserRole' | Rename role id by name

---
Route | Method | function | Description
------|--------|----------|------------
`/getall` | POST | UserRoleController::class . ':getAllUserRoles' | Get all user roles

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | UserRoleController::class . ':deleteUserRole' | delete user role by id

---
## API "users"

   in route : "/api/routes/user/user-users.php"

Route | Method | function | Description
------|--------|----------|------------
`/{userId:[0-9]+}/password/reset` | POST | UserUsersController::class . ':passwordReset' | Reset password to random one

---
Route | Method | function | Description
------|--------|----------|------------
`/controlAccount` | POST | UserUsersController::class . ':controlAccount' | Apply role ID to user ID

---
Route | Method | function | Description
------|--------|----------|------------
`/exitControlAccount` | POST | UserUsersController::class . ':exitControlAccount' | Exit account control (admin)

---
Route | Method | function | Description
------|--------|----------|------------
`/lockunlock` | POST | UserUsersController::class . ':lockUnlock' | Lock/unlock account (admin)

---
Route | Method | function | Description
------|--------|----------|------------
`/showsince` | POST | UserUsersController::class . ':showSince' | Show since (every user)

---
Route | Method | function | Description
------|--------|----------|------------
`/showto` | POST | UserUsersController::class . ':showTo' | Show to (every user)

---
Route | Method | function | Description
------|--------|----------|------------
`/{userId:[0-9]+}/login/reset` | POST | UserUsersController::class . ':loginReset' | Reset login count to setFailedLogins(0) (Admin)

---
Route | Method | function | Description
------|--------|----------|------------
`/{userId:[0-9]+}` | DELETE | UserUsersController::class . ':deleteUser' | Delete user account (Admin)

---
Route | Method | function | Description
------|--------|----------|------------
`/2fa/remove` | POST | UserUsersController::class . ':userstwofaremove' | Remove 2FA code (Admin)

---
Route | Method | function | Description
------|--------|----------|------------
`/2fa/pending` | POST | UserUsersController::class . ':userstwofapending' | pending 2FA code (Admin)

---
## PLUGINS
## API "Plugins (global management)"

   in route : "/api/routes/plugins/plugins.php"

Route | Method | function | Description
------|--------|----------|------------
`/activate` | POST | PluginsController::class . ':activate' | Activate a plugin (admin role)

* `{ref}`->`int` :: Id

---
Route | Method | function | Description
------|--------|----------|------------
`/deactivate` | POST | PluginsController::class . ':deactivate' | Deactivate a plugin (admin role)

* `{ref}`->`int` :: Id

---
Route | Method | function | Description
------|--------|----------|------------
`/` | DELETE | PluginsController::class . ':remove' | Remove a plugin (admin role)

* `{ref}`->`int` :: Id

---
Route | Method | function | Description
------|--------|----------|------------
`/add` | POST | PluginsController::class . ':add' | Add a plugin (admin role), post $_FILES['pluginFile']

---
Route | Method | function | Description
------|--------|----------|------------
`/upgrade` | POST | PluginsController::class . ':upgrade' | update/upgrade a plugin (admin role), post $_FILES['pluginFile']

---
Route | Method | function | Description
------|--------|----------|------------
`/addDashboardPlaces` | POST | PluginsController::class . ':addDashboardPlaces' | Place dashboard items plugins on the dashboard

* `{ref}`->`array` :: dashBoardItems

---
Route | Method | function | Description
------|--------|----------|------------
`/removeFromDashboard` | POST | PluginsController::class . ':removeFromDashboard' | Add a dashboard plugin from the dashboard by his name

* `{ref}`->`string` :: name

---
Route | Method | function | Description
------|--------|----------|------------
`/collapseFromDashboard` | POST | PluginsController::class . ':collapseFromDashboard' | Remove a dashboard plugin from the dashboard by his name

* `{ref}`->`string` :: name

---
## API "meeting (plugin)"

   in route : "/Plugins/MeetingJitsi/api/plgnapi.php"

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | MeetingController::class . ':getAllMettings' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/getLastMeeting` | GET | MeetingController::class . ':getLastMeeting' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/createMeetingRoom` | POST | MeetingController::class . ':createMeetingRoom' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/selectMeetingRoom` | POST | MeetingController::class . ':selectMeetingRoom' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteAllMeetingRooms` | DELETE | MeetingController::class . ':deleteAllMeetingRooms' | No description

---
Route | Method | function | Description
------|--------|----------|------------
`/changeSettings` | POST | MeetingController::class . ':changeSettings' | No description

---
## OTHERS
## API "Cart"

   in route : "/api/routes/cart.php"

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | CartController::class . ':getAllPeopleInCart' | Get all people in Cart

---
Route | Method | function | Description
------|--------|----------|------------
`/` | POST | CartController::class . ':cartOperation' | cart operations

* `{ref}`->`array` :: Persons arrray of ids (possible value)
* `{id}`->`int` :: Family (ID) of the person (possible value)
* `{id}`->`array` :: Families (array of ids) (possible value)
* `{id}`->`int` :: Group id (possible value)
* `{id}`->`int` :: removeFamily id (possible value)
* `{id}`->`array` :: removeFamilies (array of ids) (possible value)
* `{id}`->`int` :: studentGroup id
* `{id}`->`int` :: teacherGroup id

---
Route | Method | function | Description
------|--------|----------|------------
`/interectPerson` | POST | CartController::class . ':cartIntersectPersons' | Get user info by id

* `{ref}`->`array` :: Persons id in array ref (possible value)

---
Route | Method | function | Description
------|--------|----------|------------
`/emptyToGroup` | POST | CartController::class . ':emptyCartToGroup' | Empty cart to a group

* `{ref}`->`int` :: groupID
* `{ref}`->`int` :: groupRoleID

---
Route | Method | function | Description
------|--------|----------|------------
`/emptyToEvent` | POST | CartController::class . ':emptyCartToEvent' | Empty cart to event

* `{ref}`->`int` :: eventID

---
Route | Method | function | Description
------|--------|----------|------------
`/emptyToNewGroup` | POST | CartController::class . ':emptyCartToNewGroup' | Empty cart to a new group

* `{ref}`->`string` :: groupName

---
Route | Method | function | Description
------|--------|----------|------------
`/removeGroup` | POST | CartController::class . ':removeGroupFromCart' | Remove all group members Ids from the cart

* `{ref}`->`int` :: Group (Id)

---
Route | Method | function | Description
------|--------|----------|------------
`/removeGroups` | POST | CartController::class . ':removeGroupsFromCart' | Remove all groups members Ids from the cart

* `{ref}`->`array` :: Groups (array of group Id)

---
Route | Method | function | Description
------|--------|----------|------------
`/removeStudentGroup` | POST | CartController::class . ':removeStudentsGroupFromCart' | Remove students by group Id from the cart

* `{ref}`->`int` :: Group (group Id)

---
Route | Method | function | Description
------|--------|----------|------------
`/removeTeacherGroup` | POST | CartController::class . ':removeTeachersGroupFromCart' | Remove teachers by group Id from the cart

* `{ref}`->`int` :: Group (group Id)

---
Route | Method | function | Description
------|--------|----------|------------
`/addAllStudents` | POST | CartController::class . ':addAllStudentsToCart' | Add all students to cart

---
Route | Method | function | Description
------|--------|----------|------------
`/addAllTeachers` | POST | CartController::class . ':addAllTeachersToCart' | Add all teachers to cart

---
Route | Method | function | Description
------|--------|----------|------------
`/removeAllStudents` | POST | CartController::class . ':removeAllStudentsFromCart' | Remove all students from the cart

---
Route | Method | function | Description
------|--------|----------|------------
`/removeAllTeachers` | POST | CartController::class . ':removeAllTeachersFromCart' | Remove all teachers from the cart

---
Route | Method | function | Description
------|--------|----------|------------
`/delete` | POST | CartController::class . ':deletePersonCart' | Remove persons from the cart

* `{ref}`->`array` :: Persons (array of persons ids)

---
Route | Method | function | Description
------|--------|----------|------------
`/deactivate` | POST | CartController::class . ':deactivatePersonCart' | De-activate persons from the cart

* `{ref}`->`array` :: Persons (array of persons ids)

---
Route | Method | function | Description
------|--------|----------|------------
`/` | DELETE | CartController::class . ':removePersonCart' | Extract persons in the cart to vcard format

---
## API "fundraiser"

   in route : "/api/routes/fundraiser/fundraiser.php"

Route | Method | function | Description
------|--------|----------|------------
`/{FundRaiserID:[0-9]+}` | POST | FundraiserController::class . ':getAllFundraiserForID' | Get All fundraiser for FundRaiserID

* `{ref}`->`int` :: FundRaiserID

---
Route | Method | function | Description
------|--------|----------|------------
`/replicate` | POST | FundraiserController::class . ':replicateFundraiser' | Duplicate fundraiser

* `{ref}`->`int` :: DonatedItemID
* `{ref}`->`int` :: count

---
Route | Method | function | Description
------|--------|----------|------------
`/donatedItemSubmit` | POST | FundraiserController::class . ':donatedItemSubmitFundraiser' | create or update DonateItem with params

* `{ref}`->`int` :: currentFundraiser
* `{ref}`->`int` :: currentDonatedItemID
* `{ref}`->`string` :: Item
* `{ref}`->`int` :: Multibuy
* `{ref}`->`int` :: Donor
* `{ref}`->`string` :: Title
* `{ref}`->`html` :: Description
* `{ref}`->`float` :: EstPrice
* `{ref}`->`float` :: MaterialValue
* `{ref}`->`float` :: MinimumPrice
* `{ref}`->`int` :: Buyer
* `{ref}`->`float` :: SellPrice
* `{ref}`->`string` :: PictureURL

---
Route | Method | function | Description
------|--------|----------|------------
`/donateditem/currentpicture` | POST | FundraiserController::class . ':donatedItemCurrentPicture' | Return current url picture for the DonateItem ID

* `{ref}`->`int` :: DonatedItemID

---
Route | Method | function | Description
------|--------|----------|------------
`/donateditem` | DELETE | FundraiserController::class . ':deleteDonatedItem' | Delete donatedItem with the params below

* `{ref}`->`int` :: FundRaiserID
* `{ref}`->`int` :: DonatedItemID

---
Route | Method | function | Description
------|--------|----------|------------
`/donatedItem/submit/picture` | POST | FundraiserController::class . ':donatedItemSubmitPicture' | Submit picture for the Donated Item Id

* `{ref}`->`int` :: DonatedItemID
* `{ref}`->`string` :: pathFile

---
Route | Method | function | Description
------|--------|----------|------------
`/findFundRaiser/{fundRaiserID:[0-9]+}/{startDate}/{endDate}` | POST | FundraiserController::class . ':findFundRaiser' | Find a fund raiser by Id and in range of dates

* `{ref}`->`int` :: fundRaiserID
* `{ref}`->`string` :: startDate
* `{ref}`->`string` :: startDate

---
Route | Method | function | Description
------|--------|----------|------------
`/paddlenum` | DELETE | FundraiserController::class . ':deletePaddleNum' | delete PaddleNum

* `{ref}`->`int` :: fundraiserID
* `{ref}`->`int` :: pnID

---
Route | Method | function | Description
------|--------|----------|------------
`/paddlenum/list/{fundRaiserID:[0-9]+}` | POST | FundraiserController::class . ':getPaddleNumList' | Get PaddleNum list by fundraiser ID

* `{ref}`->`int` :: fundRaiserID

---
Route | Method | function | Description
------|--------|----------|------------
`/paddlenum/add/donnors` | POST | FundraiserController::class . ':addDonnors' | Add all Donnors from the fundraiserID and create associated PaddleNums

* `{ref}`->`int` :: fundraiserID

---
Route | Method | function | Description
------|--------|----------|------------
`/paddlenum/persons/all/{fundRaiserID:[0-9]+}` | GET | FundraiserController::class . ':getAllPersonsNum' | Returns a list of all the persons who are in the cart

* `{ref}`->`int` :: fundRaiserID

---
Route | Method | function | Description
------|--------|----------|------------
`/paddlenum/add` | POST | FundraiserController::class . ':addPaddleNum' | Add PaddleNum

* `{ref}`->`int` :: fundraiserID
* `{ref}`->`int` :: PerID
* `{ref}`->`int` :: PaddleNumID
* `{ref}`->`int` :: Num

---
Route | Method | function | Description
------|--------|----------|------------
`/paddlenum/info` | POST | FundraiserController::class . ':paddleNumInfo' | Get PaddleNum infos

* `{ref}`->`int` :: fundraiserID
* `{ref}`->`int` :: PerID
* `{ref}`->`int` :: Num

---
## API "geocoder"

   in route : "/api/routes/geocoder.php"

Route | Method | function | Description
------|--------|----------|------------
`/address` | POST | GeocoderController::class . ':getGeoLocals' | get address

* `{ref}`->`string` :: address

---
Route | Method | function | Description
------|--------|----------|------------
`/address/` | POST | GeocoderController::class . ':getGeoLocals' | get address

---
## API "kiosks"

   in route : "/api/routes/kiosks.php"

Route | Method | function | Description
------|--------|----------|------------
`/` | GET | KiosksController::class . ':getKioskDevices' | Get all Kiosk devices

* `{ref}`->`string` :: address

---
Route | Method | function | Description
------|--------|----------|------------
`/allowRegistration` | POST | KiosksController::class . ':allowDeviceRegistration' | Allow a Kiosk registration

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/reloadKiosk` | POST | KiosksController::class . ':reloadKiosk' | Reload kiosk for kioskId

* `{ref}`->`int` :: kioskId

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/identifyKiosk` | POST | KiosksController::class . ':identifyKiosk' | Identify Kiosk by id

* `{ref}`->`int` :: kioskId

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/acceptKiosk` | POST | KiosksController::class . ':acceptKiosk' | Accept Kiosk by id

* `{ref}`->`int` :: kioskId

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/setAssignment` | POST | KiosksController::class . ':setKioskAssignment' | Set Kiosk assignement

* `{ref}`->`int` :: kioskId

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}` | DELETE | KiosksController::class . ':deleteKiosk' | Delete kiosk by id

* `{ref}`->`int` :: kioskId

---
## API "mailchimp"

   in route : "/api/routes/mailchimp.php"

Route | Method | function | Description
------|--------|----------|------------
`/search/{query}` | GET | MailchimpController::class . ':searchList' | Search in the list field : *, family name, group, etc ...

* `{ref}`->`string` :: query

---
Route | Method | function | Description
------|--------|----------|------------
`/list/{listID}` | GET | MailchimpController::class . ':oneList' | get one list info (['MailChimpList' => $list,'MailChimpCampaign' => $campaign,'membersCount' => count($mailchimp->getListMembersFromListId($args['listID']))])

* `{ref}`->`int` :: listID

---
Route | Method | function | Description
------|--------|----------|------------
`/lists` | GET | MailchimpController::class . ':lists' | get all lists ['MailChimpLists' => $lists,'MailChimpCampaigns' => $campaigns, 'firstLoaded' => !$isLoaded, 'isActive' => $isActive]

* `{ref}`->`int` :: listID

---
Route | Method | function | Description
------|--------|----------|------------
`/listmembers/{listID}` | GET | MailchimpController::class . ':listmembers' | get all members list for listID

* `{ref}`->`int` :: listID

---
Route | Method | function | Description
------|--------|----------|------------
`/createlist` | POST | MailchimpController::class . ':createList' | create a list

* `{ref}`->`string` :: ListTitle
* `{ref}`->`string` :: Subject
* `{ref}`->`string` :: PermissionReminder
* `{ref}`->`bool` :: ArchiveBars
* `{ref}`->`bool` :: Status (private | public)

---
Route | Method | function | Description
------|--------|----------|------------
`/modifylist` | POST | MailchimpController::class . ':modifyList' | modify list by list id

* `{ref}`->`int` :: list_id
* `{ref}`->`string` :: name
* `{ref}`->`string` :: subject
* `{ref}`->`string` :: permission_reminder

---
Route | Method | function | Description
------|--------|----------|------------
`/deleteallsubscribers` | POST | MailchimpController::class . ':deleteallsubscribers' | delete all subscribers

* `{ref}`->`int` :: list_id

---
Route | Method | function | Description
------|--------|----------|------------
`/deletelist` | POST | MailchimpController::class . ':deleteList' | delete list by list ID

* `{ref}`->`int` :: list_id

---
Route | Method | function | Description
------|--------|----------|------------
`/list/removeTag` | POST | MailchimpController::class . ':removeTag' | remove TagID in the List by list ID

* `{ref}`->`int` :: list_id
* `{ref}`->`int` :: tag_ID

---
Route | Method | function | Description
------|--------|----------|------------
`/list/removeAllTagsForMembers` | POST | MailchimpController::class . ':removeAllTagsForMembers' | remove all tags in list ID by an array of emails

* `{ref}`->`int` :: list_id
* `{ref}`->`array` :: emails

---
Route | Method | function | Description
------|--------|----------|------------
`/list/addTag` | POST | MailchimpController::class . ':addTag' | add a tag to all members by emails array or create a tag (-1) by name for all emails array.

* `{ref}`->`int` :: list_id
* `{ref}`->`string` :: tag (could be -1 : in this case, you'll create a new tag)
* `{ref}`->`string` :: name (in case tag is -1)
* `{ref}`->`array` :: emails

---
Route | Method | function | Description
------|--------|----------|------------
`/list/getAllTags` | POST | MailchimpController::class . ':getAllTags' | get all tags for for list by id

* `{ref}`->`int` :: list_id

---
Route | Method | function | Description
------|--------|----------|------------
`/list/removeTagForMembers` | POST | MailchimpController::class . ':removeTagForMembers' | remove tag for all members (emails array) in list Id

* `{ref}`->`int` :: list_id
* `{ref}`->`int` :: tag
* `{ref}`->`array` :: emails

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/create` | POST | MailchimpController::class . ':campaignCreate' | Create a campaign for tagID with subject etc ....

* `{ref}`->`int` :: list_id
* `{ref}`->`string` :: subject
* `{ref}`->`string` :: title
* `{ref}`->`string` :: tagId

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/delete` | POST | MailchimpController::class . ':campaignDelete' | Delete campaign by id

* `{ref}`->`int` :: campaign_id

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/send` | POST | MailchimpController::class . ':campaignSend' | Send campaign by id

* `{ref}`->`int` :: campaign_id

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/actions/save` | POST | MailchimpController::class . ':campaignSave' | Save a campaign

* `{ref}`->`int` :: campaign_id
* `{ref}`->`string` :: subject
* `{ref}`->`string` :: oldStatus ("save" | "paused" | scheduled)

---
Route | Method | function | Description
------|--------|----------|------------
`/campaign/{campaignID}/content` | GET | MailchimpController::class . ':campaignContent' | Get html contect of a campaign

* `{ref}`->`int` :: campaignID

---
Route | Method | function | Description
------|--------|----------|------------
`/status` | POST | MailchimpController::class . ':statusList' | update the list status

* `{ref}`->`int` :: list_id
* `{ref}`->`string` :: status ("save" | "paused" | scheduled)
* `{ref}`->`string` :: email

---
Route | Method | function | Description
------|--------|----------|------------
`/suppress` | POST | MailchimpController::class . ':suppress' | delete email in the list id

* `{ref}`->`int` :: list_id
* `{ref}`->`string` :: email (one email)

---
Route | Method | function | Description
------|--------|----------|------------
`/suppressMembers` | POST | MailchimpController::class . ':suppressMembers' | delete emails in the list id

* `{ref}`->`int` :: list_id
* `{ref}`->`array` :: array of emails

---
Route | Method | function | Description
------|--------|----------|------------
`/addallnewsletterpersons` | POST | MailchimpController::class . ':addallnewsletterpersons' | add all members checked by newsletter checkbox in the CRM

* `{ref}`->`int` :: list_id

---
Route | Method | function | Description
------|--------|----------|------------
`/addallpersons` | POST | MailchimpController::class . ':addallpersons' | add all persons in the CRM who have a email or work email to list ID

* `{ref}`->`int` :: list_id

---
Route | Method | function | Description
------|--------|----------|------------
`/addperson` | POST | MailchimpController::class . ':addPerson' | add one person ID to list ID

* `{ref}`->`int` :: list_id
* `{ref}`->`int` :: personID

---
Route | Method | function | Description
------|--------|----------|------------
`/addfamily` | POST | MailchimpController::class . ':addFamily' | add one family ID to list ID

* `{ref}`->`int` :: list_id
* `{ref}`->`int` :: familyID

---
Route | Method | function | Description
------|--------|----------|------------
`/addAllFamilies` | POST | MailchimpController::class . ':addAllFamilies' | add all families to list ID

* `{ref}`->`int` :: list_id

---
Route | Method | function | Description
------|--------|----------|------------
`/addgroup` | POST | MailchimpController::class . ':addGroup' | add all group members by ID to list ID

* `{ref}`->`int` :: list_id
* `{ref}`->`int` :: fgroupID

---
