# Changelog

## 7.6.0 (27/02/2023)
*No changelog for this release.*

Minor update.

---

## 7.5.0 (12/04/2021)
New CRM is rewritten for :
   * AdminLTE 3.1.0
   * bootStrap 4.6.0
   * php 8.0.3
   * slim 4.7.1
   * fullcalendar 5.6.0
   * Dark mode (automatic or not)
   * ckeditor in darkmode too
   * Setup enhancements

<img width="1742" alt="Capture d’écran 2021-04-14 à 00 22 25" src="https://user-images.githubusercontent.com/20263693/114628680-8b015280-9cb7-11eb-89d3-8272567c93df.png">

   
- vCard export for groups, cart, person, family
- search menu rewritten
- all the api are rewritten
- all the v2 route too.


Bugs correction

- Leaflet for safari
- CSV and PDF stats export is now efficient
- better cart management
- system backup/restore is rewritten too
- sunday school view bug resolution


Inner Beauty

- AdminLTE 3.1.0
- Jquery 3.6.0
- bootStrap is in the last version : 4.6.0
- FullCalendar is in version 5.6.0
- DataTable are now in 1.10.24


- Propelization
* GroupReport.php is propeled


etc ....

Inner Coherence

See full changelog here:
---

## 7.0.0 (24/11/2020)
New version 7.0.0

ATTENTION : You have to clear your cache on your browser.

New CRM
- Everything is rewritten in AdminLTE 3.0.5 and new bootStrap 4.5.3

Pastoral Care tools
- Pastoral care center with statistics for youngs, retired ....
- Full pastoral care integration for young, retired, etc ...

Customizations
- The CRM is now fully customizable accord to AdminLTE 3 (color, font size, etc .... see for this point the new person settings).

An more
- Fundraiser is completely rewritten with EDrive
- Mailchimp can now include all the headpeople for each families.
- A brand new calendar 4.4.2 with optimisations
- Search engine update for volunteer opportunity
- You can now search for all Volunteers, families, payments, .... , through the meta search engine.
- Kiosk re-introduction to make register call (completely rewritten)
- QR code badges
- QR code call the register
- You can add/remove item in the kiosk
- The personView and Family is rewritten with map groups etc ...
- Call the register for the groups is now available
- Call the register for sundayschool is completely refactored
- Meetings : Jitsi meet api integration for meeting (so you can make conferences inside the CRM and tchat too).
- Re-Introduction of Event type to create events
- New PersonView and Familyview.
- New Main Dashboard in v2 stage.
- security update (everything is new).
- Optimization of the meta search engine.

Bugs correction

- some bugs corrections.

Inner Beauty

- AdminLTE 3
- Jquery 3.5.1
- bootStrap is in the last version : 4.5.0
- FullCalendar is in version 4.4.2
- DataTable are now in 1.10.21
- everything is now in the latest version.
- everything is rewritten for the new js code.


- Propelization
* FamilyPledgeSummary.php propeled
* ReminderReport.php propeled
* confirmreportemail propeled
*  convert individual to family.php propeled
* confirm report propeled
* canvassreports propeled
* selectdelete propeled
* VotingMembers.php is propeled
* PersonService.php is propeled


etc ....

Inner Coherence

See full changelog here:
---

## 6.0.1 (20/10/2020)
This version is only available to solve a problem encountered with some versions of PHP.
---

## 6.0.0 (12/03/2020)
New version 6.0.0

- New Meta search engine (everything is now searchable).
- new entry in the calendar api : VAlarm
- pastoral care for persons and families
- pastoral search better supported
- New backup manager (NextCloud compatible)
- New encryptation backup manager
- The backup management now include the eDrive for each users
- sundayschool has now dashboard items
- people has now dashboard items too
- CartView is in v2 and optimized
- better support of the menu page with little configuration
- php 7.1 minimal requirement
- security update v4
- optimization in speed
- optimization of the menu dashboard page
- optimization of mailchimp in speed
- optimisation with the dashboard items
- security massive update

Bugs correction

- SelectList.js minor bug resolution
- Footer.js bug resolution
- QuerySQL.php bug resolution
- cart api bug resolution
- PersonEditor.php bug correction
- PersonView bug resolution
- browser bug resolution
- Upgrade script bug correction
- Browse bug resolution : bootbox
- AppIntegrityService upgrade
- Webdav update optimization for speed
- group person bug resolution

Inner Beauty

- Bootbox new version : 5.4.0
- CartView.php is propeled and in v2 stage
- Logger coherence
- new dashboard mailchimp item
- sabre.io update to 4.0.2
- new search way
- refactor of the dashboard items

etc ....

Inner Coherence

See full changelog here:
---

## 5.8.6 (07/10/2019)
New version 5.8.6

- GDPRDataStructureExport.php CSV enhancement
- MemberEmailExport.php CSV enhancement
- QueryView minor everything is now in JS code
- FamilyView js update
- SelectList cart js logic update
- PersonView cart JS logic update
- Add query cart operations : full in js
- Mailchimp user email address change
- Ensure Menu Dashboard render all times
- Profile Photo bug resolution

Inner Beauty

- PersonService.php is propeled
- VotingMembers.php is propeled
- Logger coherence

New version 5.8.5

- Menubar UTF9 changes
- SelectList enhancement
- Sundayschool enhancement
- GroupEditor role upgrade
- self-verify-updates.php + online-pending-verify.php update
- FamiliView.php url verification
- verify-family-info.php upgrade

New version 5.8.4

- SundaySchoolView Teacher props
- ClassList.php & PhotoBook.php group prop adds
- PersonCustomFieldsEditor.php and FamilyCustomFieldsEditor.php upgrade

Bugs correction
- GroupPropsFormEditor.php bug resolution
- PersonView Bug resolution
- SundaySchool teacher role Bug resolution
- Cart bug resolution

Inner Beauty
- OptionManager upgrade : v2
- GroupView v2
- no more OptionManagerRowOps.php
- no more GroupView.php

Propeled files and nom more : runquery function

New version 5.8.3

- FundRaiser GUI update
- PaddleNumEditor.php minor update
- CartView enhancement
- CartView js upgrade
- SelectList propeled
- PeopleDashboard is in v2
- QueryView GUI enhancement
- sundayschooldashboard age sorting bug resolution

Bugs correction

- PersonEditor.php bug resolution
- Boostraper bug resolution
- GroupView.js bug resolution

Inner Beauty
- Calendar api clarification
- ManageList.js enhancements
- GroupView.js bug resolution

Propeled files and nom more : runquery function
- CanvassUtilities.php no more RunQuery


New version 5.8.2

- Mailchimp tags
- FamilyList v2
- PersonList v2
- grouplist v2
- sundayschool full v2 version
- SundaySchoolView js upgrade (you can add a teacher directly from the view)
- sundayschooldashboard age sorting bug resolution

Bugs correction

- PersonEditor.php bug resolution
- Boostraper bug resolution
- GroupView.js bug resolution

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php

New version 5.8.1

- Directory report translation update
- Mailchimp personview familyview good information for the newsletter

Bugs correction

- GroupView.php bug resolution : type
- GroupEditor.php + GroupEditor.js bug resolution
- FamilyEditor.php bug resolution
- Map bug correction

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update 
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php 

New version 5.8.0

- SelectList is rewritten with js code for PersonToGroup.php, this file is now deleted
- Add Person To group enhancement the js part is rewritten
- new register method through JS
- newsletter for individual person not only for a family
- gpdr enhancements better person extraction and count (optimisation).
- PersonEditor better address support for individual person
- add ReminderReport in PeopleDashboard.php


Bugs correction

- FamilyEditor bug correction
- CSVExport.php visual bug correction

Inner Beauty

- The files are now deleted everything is relocated in EcclesiaCRM directory
- Include/CanvassUtilities.php
- Include/ReportFunctions.php
- Include/EnvelopeFunctions.php
- Functions.php : all the functions are now relocated in EcclesiaCRM directory

Propeled files and nom more : runquery function
- CanvasEditor.php
- FamilyEditor.php
- PersonEditor.php
- PledgeEditor.php
- PrintView.php
- PrintPastoralCare.php
- FinancialReports.php + bug resolution (filter)
- CSVExport.php
- GroupReports.php 
- CartView.php
- ManageEnvelopes.php
- CSVExport.php
- GroupReports.php
- CartView.php
- EcclesiaCRM/utils/OutputUtils.php
- Reports/PledgeSummary.php
- Reports/ZeroGivers.php
- Reports/GroupReport.php
- Reports/NameTags.php
- Reports/AdvancedDeposit.php
- Reports/TaxReport.php
- Reports/EnvelopeReport.php
- Reports/GroupReport.php propeled
- DirectoryReport.php
- PDF_Directory.php
- propeled PDFLabel.php
- propeled PledgeSummary.php
- OutputUtils.php
- DashboardItem.php
- GroupReports.php
- PDF_GroupDirectory.php 
- FamilyEditor
- CanvassEditor.php propeled
- CSVExport.php
- ManageEnvelopes.php propeled
- GroupView propeled
- PersonCustomFieldsEditor.php
- FamilyCustomFieldsEditor.php
- UpdateAllLatLon.php
- PersonEditor.php
- GeoPage.php
- PledgeEditor.php
- DepositSlipEditor.php
- peopledashboard nomore runquery

etc ....

Inner Coherence

See full changelog here:
---

## 5.8.5 (10/07/2019)
New version 5.8.5

- Menubar UTF8 changes
- SelectList enhancement
- Sundayschool enhancement
- GroupEditor role upgrade
- self-verify-updates.php + online-pending-verify.php update
- FamiliView.php url verification
- verify-family-info.php upgrade

New version 5.8.4

- SundaySchoolView Teacher props
- ClassList.php & PhotoBook.php group prop adds
- PersonCustomFieldsEditor.php and FamilyCustomFieldsEditor.php upgrade

Bugs correction
- GroupPropsFormEditor.php bug resolution
- PersonView Bug resolution
- SundaySchool teacher role Bug resolution
- Cart bug resolution

Inner Beauty
- OptionManager upgrade : v2
- GroupView v2
- no more OptionManagerRowOps.php
- no more GroupView.php

Propeled files and nom more : runquery function

New version 5.8.3

- FundRaiser GUI update
- PaddleNumEditor.php minor update
- CartView enhancement
- CartView js upgrade
- SelectList propeled
- PeopleDashboard is in v2
- QueryView GUI enhancement
- sundayschooldashboard age sorting bug resolution

Bugs correction

- PersonEditor.php bug resolution
- Boostraper bug resolution
- GroupView.js bug resolution

Inner Beauty
- Calendar api clarification
- ManageList.js enhancements
- GroupView.js bug resolution

Propeled files and nom more : runquery function
- CanvassUtilities.php no more RunQuery


New version 5.8.2

- Mailchimp tags
- FamilyList v2
- PersonList v2
- grouplist v2
- sundayschool full v2 version
- SundaySchoolView js upgrade (you can add a teacher directly from the view)
- sundayschooldashboard age sorting bug resolution

Bugs correction

- PersonEditor.php bug resolution
- Boostraper bug resolution
- GroupView.js bug resolution

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php

New version 5.8.1

- Directory report translation update
- Mailchimp personview familyview good information for the newsletter

Bugs correction

- GroupView.php bug resolution : type
- GroupEditor.php + GroupEditor.js bug resolution
- FamilyEditor.php bug resolution
- Map bug correction

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update 
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php 

New version 5.8.0

- SelectList is rewritten with js code for PersonToGroup.php, this file is now deleted
- Add Person To group enhancement the js part is rewritten
- new register method through JS
- newsletter for individual person not only for a family
- gpdr enhancements better person extraction and count (optimisation).
- PersonEditor better address support for individual person
- add ReminderReport in PeopleDashboard.php


Bugs correction

- FamilyEditor bug correction
- CSVExport.php visual bug correction

Inner Beauty

- The files are now deleted everything is relocated in EcclesiaCRM directory
- Include/CanvassUtilities.php
- Include/ReportFunctions.php
- Include/EnvelopeFunctions.php
- Functions.php : all the functions are now relocated in EcclesiaCRM directory

Propeled files and nom more : runquery function
- CanvasEditor.php
- FamilyEditor.php
- PersonEditor.php
- PledgeEditor.php
- PrintView.php
- PrintPastoralCare.php
- FinancialReports.php + bug resolution (filter)
- CSVExport.php
- GroupReports.php 
- CartView.php
- ManageEnvelopes.php
- CSVExport.php
- GroupReports.php
- CartView.php
- EcclesiaCRM/utils/OutputUtils.php
- Reports/PledgeSummary.php
- Reports/ZeroGivers.php
- Reports/GroupReport.php
- Reports/NameTags.php
- Reports/AdvancedDeposit.php
- Reports/TaxReport.php
- Reports/EnvelopeReport.php
- Reports/GroupReport.php propeled
- DirectoryReport.php
- PDF_Directory.php
- propeled PDFLabel.php
- propeled PledgeSummary.php
- OutputUtils.php
- DashboardItem.php
- GroupReports.php
- PDF_GroupDirectory.php 
- FamilyEditor
- CanvassEditor.php propeled
- CSVExport.php
- ManageEnvelopes.php propeled
- GroupView propeled
- PersonCustomFieldsEditor.php
- FamilyCustomFieldsEditor.php
- UpdateAllLatLon.php
- PersonEditor.php
- GeoPage.php
- PledgeEditor.php
- DepositSlipEditor.php
- peopledashboard nomore runquery

etc ....

Inner Coherence

See full changelog here:

---

## 5.8.4 (03/07/2019)
New version 5.8.4

- SundaySchoolView Teacher props
- ClassList.php & PhotoBook.php group prop adds
- PersonCustomFieldsEditor.php and FamilyCustomFieldsEditor.php upgrade

Bugs correction
- GroupPropsFormEditor.php bug resolution
- PersonView Bug resolution
- SundaySchool teacher role Bug resolution
- Cart bug resolution

Inner Beauty
- OptionManager upgrade : v2
- GroupView v2
- no more OptionManagerRowOps.php
- no more GroupView.php

Propeled files and nom more : runquery function

New version 5.8.3

- FundRaiser GUI update
- PaddleNumEditor.php minor update
- CartView enhancement
- CartView js upgrade
- SelectList propeled
- PeopleDashboard is in v2
- QueryView GUI enhancement
- sundayschooldashboard age sorting bug resolution

Bugs correction

- PersonEditor.php bug resolution
- Boostraper bug resolution
- GroupView.js bug resolution

Inner Beauty
- Calendar api clarification
- ManageList.js enhancements
- GroupView.js bug resolution

Propeled files and nom more : runquery function
- CanvassUtilities.php no more RunQuery


New version 5.8.2

- Mailchimp tags
- FamilyList v2
- PersonList v2
- grouplist v2
- sundayschool full v2 version
- SundaySchoolView js upgrade (you can add a teacher directly from the view)
- sundayschooldashboard age sorting bug resolution

Bugs correction

- PersonEditor.php bug resolution
- Boostraper bug resolution
- GroupView.js bug resolution

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php

New version 5.8.1

- Directory report translation update
- Mailchimp personview familyview good information for the newsletter

Bugs correction

- GroupView.php bug resolution : type
- GroupEditor.php + GroupEditor.js bug resolution
- FamilyEditor.php bug resolution
- Map bug correction

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update 
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php 

New version 5.8.0

- SelectList is rewritten with js code for PersonToGroup.php, this file is now deleted
- Add Person To group enhancement the js part is rewritten
- new register method through JS
- newsletter for individual person not only for a family
- gpdr enhancements better person extraction and count (optimisation).
- PersonEditor better address support for individual person
- add ReminderReport in PeopleDashboard.php


Bugs correction

- FamilyEditor bug correction
- CSVExport.php visual bug correction

Inner Beauty

- The files are now deleted everything is relocated in EcclesiaCRM directory
- Include/CanvassUtilities.php
- Include/ReportFunctions.php
- Include/EnvelopeFunctions.php
- Functions.php : all the functions are now relocated in EcclesiaCRM directory

Propeled files and nom more : runquery function
- CanvasEditor.php
- FamilyEditor.php
- PersonEditor.php
- PledgeEditor.php
- PrintView.php
- PrintPastoralCare.php
- FinancialReports.php + bug resolution (filter)
- CSVExport.php
- GroupReports.php 
- CartView.php
- ManageEnvelopes.php
- CSVExport.php
- GroupReports.php
- CartView.php
- EcclesiaCRM/utils/OutputUtils.php
- Reports/PledgeSummary.php
- Reports/ZeroGivers.php
- Reports/GroupReport.php
- Reports/NameTags.php
- Reports/AdvancedDeposit.php
- Reports/TaxReport.php
- Reports/EnvelopeReport.php
- Reports/GroupReport.php propeled
- DirectoryReport.php
- PDF_Directory.php
- propeled PDFLabel.php
- propeled PledgeSummary.php
- OutputUtils.php
- DashboardItem.php
- GroupReports.php
- PDF_GroupDirectory.php 
- FamilyEditor
- CanvassEditor.php propeled
- CSVExport.php
- ManageEnvelopes.php propeled
- GroupView propeled
- PersonCustomFieldsEditor.php
- FamilyCustomFieldsEditor.php
- UpdateAllLatLon.php
- PersonEditor.php
- GeoPage.php
- PledgeEditor.php
- DepositSlipEditor.php
- peopledashboard nomore runquery

etc ....

Inner Coherence

See full changelog here:

---

## 5.8.3 (01/07/2019)
new version 5.8.3
- FundRaiser GUI update
- PaddleNumEditor.php minor update
- CartView enhancement
- CartView js upgrade
- SelectList propeled
- PeopleDashboard is in v2
- QueryView GUI enhancement
- sundayschooldashboard age sorting bug resolution

Bugs correction

- PersonEditor.php bug resolution
- Boostraper bug resolution
- GroupView.js bug resolution

Inner Beauty
- Calendar api clarification
- ManageList.js enhancements
- GroupView.js bug resolution

Propeled files and nom more : runquery function
- CanvassUtilities.php no more RunQuery


New version 5.8.2

- Mailchimp tags
- FamilyList v2
- PersonList v2
- grouplist v2
- sundayschool full v2 version
- SundaySchoolView js upgrade (you can add a teacher directly from the view)
- sundayschooldashboard age sorting bug resolution

Bugs correction

- PersonEditor.php bug resolution
- Boostraper bug resolution
- GroupView.js bug resolution

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php

New version 5.8.1

- Directory report translation update
- Mailchimp personview familyview good information for the newsletter

Bugs correction

- GroupView.php bug resolution : type
- GroupEditor.php + GroupEditor.js bug resolution
- FamilyEditor.php bug resolution
- Map bug correction

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update 
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php 

New version 5.8.0

- SelectList is rewritten with js code for PersonToGroup.php, this file is now deleted
- Add Person To group enhancement the js part is rewritten
- new register method through JS
- newsletter for individual person not only for a family
- gpdr enhancements better person extraction and count (optimisation).
- PersonEditor better address support for individual person
- add ReminderReport in PeopleDashboard.php


Bugs correction

- FamilyEditor bug correction
- CSVExport.php visual bug correction

Inner Beauty

- The files are now deleted everything is relocated in EcclesiaCRM directory
- Include/CanvassUtilities.php
- Include/ReportFunctions.php
- Include/EnvelopeFunctions.php
- Functions.php : all the functions are now relocated in EcclesiaCRM directory

Propeled files and nom more : runquery function
- CanvasEditor.php
- FamilyEditor.php
- PersonEditor.php
- PledgeEditor.php
- PrintView.php
- PrintPastoralCare.php
- FinancialReports.php + bug resolution (filter)
- CSVExport.php
- GroupReports.php 
- CartView.php
- ManageEnvelopes.php
- CSVExport.php
- GroupReports.php
- CartView.php
- EcclesiaCRM/utils/OutputUtils.php
- Reports/PledgeSummary.php
- Reports/ZeroGivers.php
- Reports/GroupReport.php
- Reports/NameTags.php
- Reports/AdvancedDeposit.php
- Reports/TaxReport.php
- Reports/EnvelopeReport.php
- Reports/GroupReport.php propeled
- DirectoryReport.php
- PDF_Directory.php
- propeled PDFLabel.php
- propeled PledgeSummary.php
- OutputUtils.php
- DashboardItem.php
- GroupReports.php
- PDF_GroupDirectory.php 
- FamilyEditor
- CanvassEditor.php propeled
- CSVExport.php
- ManageEnvelopes.php propeled
- GroupView propeled
- PersonCustomFieldsEditor.php
- FamilyCustomFieldsEditor.php
- UpdateAllLatLon.php
- PersonEditor.php
- GeoPage.php
- PledgeEditor.php
- DepositSlipEditor.php
- peopledashboard nomore runquery

etc ....

Inner 
Coherence

See full changelog here:
---

## 5.8.2 (24/06/2019)
New version 5.8.2

- Mailchimp tags
- FamilyList v2
- PersonList v2
- grouplist v2
- sundayschool full v2 version
- SundaySchoolView js upgrade (you can add a teacher directly from the view)
- sundayschooldashboard age sorting bug resolution

Bugs correction

- PersonEditor.php bug resolution
- Boostraper bug resolution
- GroupView.js bug resolution

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php

New version 5.8.1

- Directory report translation update
- Mailchimp personview familyview good information for the newsletter

Bugs correction

- GroupView.php bug resolution : type
- GroupEditor.php + GroupEditor.js bug resolution
- FamilyEditor.php bug resolution
- Map bug correction

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update 
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php 

New version 5.8.0

- SelectList is rewritten with js code for PersonToGroup.php, this file is now deleted
- Add Person To group enhancement the js part is rewritten
- new register method through JS
- newsletter for individual person not only for a family
- gpdr enhancements better person extraction and count (optimisation).
- PersonEditor better address support for individual person
- add ReminderReport in PeopleDashboard.php


Bugs correction

- FamilyEditor bug correction
- CSVExport.php visual bug correction

Inner Beauty

- The files are now deleted everything is relocated in EcclesiaCRM directory
- Include/CanvassUtilities.php
- Include/ReportFunctions.php
- Include/EnvelopeFunctions.php
- Functions.php : all the functions are now relocated in EcclesiaCRM directory

Propeled files and nom more : runquery function
- CanvasEditor.php
- FamilyEditor.php
- PersonEditor.php
- PledgeEditor.php
- PrintView.php
- PrintPastoralCare.php
- FinancialReports.php + bug resolution (filter)
- CSVExport.php
- GroupReports.php 
- CartView.php
- ManageEnvelopes.php
- CSVExport.php
- GroupReports.php
- CartView.php
- EcclesiaCRM/utils/OutputUtils.php
- Reports/PledgeSummary.php
- Reports/ZeroGivers.php
- Reports/GroupReport.php
- Reports/NameTags.php
- Reports/AdvancedDeposit.php
- Reports/TaxReport.php
- Reports/EnvelopeReport.php
- Reports/GroupReport.php propeled
- DirectoryReport.php
- PDF_Directory.php
- propeled PDFLabel.php
- propeled PledgeSummary.php
- OutputUtils.php
- DashboardItem.php
- GroupReports.php
- PDF_GroupDirectory.php 
- FamilyEditor
- CanvassEditor.php propeled
- CSVExport.php
- ManageEnvelopes.php propeled
- GroupView propeled
- PersonCustomFieldsEditor.php
- FamilyCustomFieldsEditor.php
- UpdateAllLatLon.php
- PersonEditor.php
- GeoPage.php
- PledgeEditor.php
- DepositSlipEditor.php
- peopledashboard nomore runquery

etc ....

Inner Coherence

See full changelog here:

---

## 5.8.1 (06/06/2019)
New version 5.8.1

- Directory report translation update
- Mailchimp personview familyview good information for the newsletter

Bugs correction

- GroupView.php bug resolution : type
- GroupEditor.php + GroupEditor.js bug resolution
- FamilyEditor.php bug resolution
- Map bug correction

Inner Beauty

Propeled files and nom more : runquery function
- GroupPropsFormEditor.php propeled + api + js
- FamilyCustomFieldsEditor.php update 
- PersonCustomFieldsEditor.php update
- GroupPropsFormEditor.php
- GroupPropsEditor.php
- QueryList.php 

New version 5.8.0

- SelectList is rewritten with js code for PersonToGroup.php, this file is now deleted
- Add Person To group enhancement the js part is rewritten
- new register method through JS
- newsletter for individual person not only for a family
- gpdr enhancements better person extraction and count (optimisation).
- PersonEditor better address support for individual person
- add ReminderReport in PeopleDashboard.php


Bugs correction

- FamilyEditor bug correction
- CSVExport.php visual bug correction

Inner Beauty

- The files are now deleted everything is relocated in EcclesiaCRM directory
- Include/CanvassUtilities.php
- Include/ReportFunctions.php
- Include/EnvelopeFunctions.php
- Functions.php : all the functions are now relocated in EcclesiaCRM directory

Propeled files and nom more : runquery function
- CanvasEditor.php
- FamilyEditor.php
- PersonEditor.php
- PledgeEditor.php
- PrintView.php
- PrintPastoralCare.php
- FinancialReports.php + bug resolution (filter)
- CSVExport.php
- GroupReports.php 
- CartView.php
- ManageEnvelopes.php
- CSVExport.php
- GroupReports.php
- CartView.php
- EcclesiaCRM/utils/OutputUtils.php
- Reports/PledgeSummary.php
- Reports/ZeroGivers.php
- Reports/GroupReport.php
- Reports/NameTags.php
- Reports/AdvancedDeposit.php
- Reports/TaxReport.php
- Reports/EnvelopeReport.php
- Reports/GroupReport.php propeled
- DirectoryReport.php
- PDF_Directory.php
- propeled PDFLabel.php
- propeled PledgeSummary.php
- OutputUtils.php
- DashboardItem.php
- GroupReports.php
- PDF_GroupDirectory.php 
- FamilyEditor
- CanvassEditor.php propeled
- CSVExport.php
- ManageEnvelopes.php propeled
- GroupView propeled
- PersonCustomFieldsEditor.php
- FamilyCustomFieldsEditor.php
- UpdateAllLatLon.php
- PersonEditor.php
- GeoPage.php
- PledgeEditor.php
- DepositSlipEditor.php
- peopledashboard nomore runquery

etc ....

Inner Coherence

See full changelog here:

---

## 5.8.0 (03/06/2019)
New version 5.8.0

- SelectList is rewritten with js code for PersonToGroup.php, this file is now deleted
- Add Person To group enhancement the js part is rewritten
- new register method through JS
- newsletter for individual person not only for a family
- gpdr enhancements better person extraction and count (optimisation).
- PersonEditor better address support for individual person
- add ReminderReport in PeopleDashboard.php
- security upgrade : bootbox

Bugs correction

- FamilyEditor bug correction
- CSVExport.php visual bug correction
- mailchimp api for group add

Inner Beauty

- The files are now deleted everything is relocated in EcclesiaCRM directory
- Include/CanvassUtilities.php
- Include/ReportFunctions.php
- Include/EnvelopeFunctions.php
- Functions.php : all the functions are now relocated in EcclesiaCRM directory

Propeled files and nom more : runquery function
- CanvasEditor.php
- FamilyEditor.php
- PersonEditor.php
- PledgeEditor.php
- PrintView.php
- PrintPastoralCare.php
- FinancialReports.php + bug resolution (filter)
- CSVExport.php
- GroupReports.php 
- CartView.php
- ManageEnvelopes.php
- CSVExport.php
- GroupReports.php
- CartView.php
- EcclesiaCRM/utils/OutputUtils.php
- Reports/PledgeSummary.php
- Reports/ZeroGivers.php
- Reports/GroupReport.php
- Reports/NameTags.php
- Reports/AdvancedDeposit.php
- Reports/TaxReport.php
- Reports/EnvelopeReport.php
- Reports/GroupReport.php propeled
- DirectoryReport.php
- PDF_Directory.php
- propeled PDFLabel.php
- propeled PledgeSummary.php
- OutputUtils.php
- DashboardItem.php
- GroupReports.php
- PDF_GroupDirectory.php 
- FamilyEditor
- CanvassEditor.php propeled
- CSVExport.php
- ManageEnvelopes.php propeled
- GroupView propeled
- PersonCustomFieldsEditor.php
- FamilyCustomFieldsEditor.php
- UpdateAllLatLon.php
- PersonEditor.php
- GeoPage.php
- PledgeEditor.php
- DepositSlipEditor.php
- peopledashboard nomore runquery

etc ....

Inner Coherence

See full changelog here:
---

## 5.7.0 (06/05/2019)
New version 5.7.0
- EDrive role added (a user can or not have this role), everything is rewritter : drive, js code, etc ... menubar.
- Checkin.php enhancement (Edrive add in event edition).
- Group Menu classifications and sundayschool menuitem classifications update
- Roles redefinitions (CSV, PDF, sundayschool CSV and PDF export) are now general roles
- A  user can now change the AdminLTE style from himself
- UserRole enhancements
- User role notification (when an user role is assigned to a user)
- map external providers (apple map, bing map etc ...), usefull on a smartphone
- OutputUtils.php upgrade (for apple map, bing map and google map links).
- Mailchimp improvements (now when you add all the newsletter members, the list is reloaded)
- Mailchimp export contact as pdf/excels rows ....
- PersonEditor : newsletter improvements
- mailnotification update : role added (in the userlist).
- baseuseremail authorisation rewritten
- user upgrade : mail authorization (a user now has the right to send mail or not : GDPR).
- user list webdavkey infos
- SundaySchoolView security update (some user has the possibility to edit a group when the role wasn't assigned).
- CalendarV2 enhancement for smartphones (now part will scroll properly).
- GroupView enhancement (code clarifications).
- GroupProperty enhancements (a members can now answered to a request : like a doodle).
- Pastoral Care improvements (a member with the pastoral care role can now see the notes without to have the menu option role).
- MenuBar security update : dictionnary etc ...


Bugs correction
- Header_head_metatag : bug resolution.
- public home folder bug resolution.
- Setup bug correction with localisation CRM.
- Reservation Calendar bug resolution.
- SettingsIndividual.php bug resolution.
- bug correction in the usereditor part.
- profile role bug correction, in some case when a person has the groupe role, when the profile was saved, the code could crash.
- GroupEditor bug correction (you can now change the name of group role).
- group deletion is now corrected.

Inner Beauty
- UserEditor.php propeled
- SettingsIndividual.php Propeled

Inner Coherence

See full changelog here:

---

## 5.6.2 (20/04/2019)
new version 5.6.2
- Pastoral Care ckeditor update
- Group specific properties upgrade
- EventEditor.js translation
- EventEditor.js update for Cellular phones
- cart deactivate new menu item (You can now deactivate all the persons in the cart, and if a family is empty the family is emptied too).
- Deactivate button in the cartView

New version 5.6.0
- brand new Document editor on full js code
- v2 for the general and specific properties
- Map is now in v2 too
- PersonView and FamilyView more propel code
- Bing Map enhancement + OpenStreepMap enhancements too.
- Mailchimp Campaign window is now large by default
- Timelines for person and family upgrade

Bugs correction
- Bug et résolution de filemanager pour les familles
- in the timelines

Inner Beauty
- delete of the unusefull files
    - PropertyTypeEditor.php
    - PropertyTypeDelete.php
    - PropertyTypeList.php
    - PropertyEditor.php
    - PropertyDelete.php
    - DocumentDelete.php
    - DocumentEditor.php

Inner Coherence

See full changelog here:

---

## 5.6.1 (13/04/2019)
new version 5.6.1
- PersonView and FamilyView previous and next member
- bug resolution with PersonView.js
- bug resolution with add a person to a group
- bug resolution with add vcard to an addressbook

New version 5.6.0
- brand new Document editor on full js code
- v2 for the general and specific properties
- Map is now in v2 too
- PersonView and FamilyView more propel code
- Bing Map enhancement + OpenStreepMap enhancements too.
- Mailchimp Campaign window is now large by default
- Timelines for person and family upgrade

Bugs correction
- Bug et résolution de filemanager pour les familles
- in the timelines

Inner Beauty
- delete of the unusefull files
    - PropertyTypeEditor.php
    - PropertyTypeDelete.php
    - PropertyTypeList.php
    - PropertyEditor.php
    - PropertyDelete.php
    - DocumentDelete.php
    - DocumentEditor.php

Inner Coherence

See full changelog here:

---

## 5.6.0 (12/04/2019)
New version 5.6.0
- brand new Document editor on full js code
- v2 for the general and specific properties
- Map is now in v2 too
- PersonView and FamilyView more propel code
- Bing Map enhancement + OpenStreepMap enhancements too.
- Mailchimp Campaign window is now large by default
- Timelines for person and family upgrade

Bugs correction
- Bug et résolution de filemanager pour les familles
- in the timelines

Inner Beauty
- delete of the unusefull files
    - PropertyTypeEditor.php
    - PropertyTypeDelete.php
    - PropertyTypeList.php
    - PropertyEditor.php
    - PropertyDelete.php
    - DocumentDelete.php
    - DocumentEditor.php

Inner Coherence

See full changelog here:

---

## 5.5.6 (04/04/2019)
New version 5.5.6
- bug resolution with the file manager
- Note Term deletion Document
- Group Menu hierarchy classification
- OptionManager localisation
- PersonView coherence (Member list->Person List).
- Some more localisation terms
- Nom more NoteEditor and NoteDelete

New version 5.5.5
Functionality Improvements :
- ckeditor update for pastoral care : drag and drop is now supported
- ckeditor update for event : drag and drop is now supported
- filebrowser update
- Next/previous person In PersonView
- pastoral care V2 upgrade
- PersonEditor bug resolution

New version 5.5.4
Functionality Improvements :
- MailChimp upgrade v4 : adding permission reminder in ckeditor + managelist

New version 5.5.3
Functionality Improvements :
- title head upgrade with v2 api.
- MailChimp upgrade v3 (date and translation language).
- ckeditor bug resolution.

new version 5.5.2
Functionality Improvements :
- Bug managelist campaign creation.
- security update for browser and uploader images tool.

new version 5.5.1
Functionality Improvements :

Bug resolution in UserList.
- improvements in MailChimp.
- improvements in the public folder management.

5.5.0 version
- CKeditor image upload drag and drop
- full refactor of eDrive management
- MenuBar clarification
- public folder for MailChimp
- Mailchimp improvements and speed
- GDPR api
- mailchimp security user
- PHP 7.2 improvements
- Server scripts creation

Bugs correction
- GDPRListExport.php bug resolution
- WebDav bug resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:

---

## 5.5.5 (24/03/2019)
New version 5.5.5
Functionality Improvements :
- ckeditor update for pastoral care : drag and drop is now supported
- ckeditor update for event : drag and drop is now supported
- filebrowser update
- Next/previous person In PersonView
- pastoral care V2 upgrade
- PersonEditor bug resolution

New version 5.5.4
Functionality Improvements :
- MailChimp upgrade v4 : adding permission reminder in ckeditor + managelist

New version 5.5.3
Functionality Improvements :
- title head upgrade with v2 api.
- MailChimp upgrade v3 (date and translation language).
- ckeditor bug resolution.

new version 5.5.2
Functionality Improvements :
- Bug managelist campaign creation.
- security update for browser and uploader images tool.

new version 5.5.1
Functionality Improvements :

Bug resolution in UserList.
- improvements in MailChimp.
- improvements in the public folder management.

5.5.0 version
- CKeditor image upload drag and drop
- full refactor of eDrive management
- MenuBar clarification
- public folder for MailChimp
- Mailchimp improvements and speed
- GDPR api
- mailchimp security user
- PHP 7.2 improvements
- Server scripts creation

Bugs correction
- GDPRListExport.php bug resolution
- WebDav bug resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:

---

## 5.5.4 (16/03/2019)
New version 5.5.4
Functionality Improvements :
- MailChimp upgrade v4 : adding permission reminder in ckeditor + managelist

New version 5.5.3
Functionality Improvements :
- title head upgrade with v2 api.
- MailChimp upgrade v3 (date and translation language).
- ckeditor bug resolution.

new version 5.5.2
Functionality Improvements :
- Bug managelist campaign creation.
- security update for browser and uploader images tool.

new version 5.5.1
Functionality Improvements :

Bug resolution in UserList.
- improvements in MailChimp.
- improvements in the public folder management.

5.5.0 version
- CKeditor image upload drag and drop
- full refactor of eDrive management
- MenuBar clarification
- public folder for MailChimp
- Mailchimp improvements and speed
- GDPR api
- mailchimp security user
- PHP 7.2 improvements
- Server scripts creation

Bugs correction
- GDPRListExport.php bug resolution
- WebDav bug resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:

---

## 5.5.3 (13/03/2019)
new version 5.5.3
Functionality Improvements :
- title head upgrade with v2 api.
- MailChimp upgrade v3 (date and translation language).
- ckeditor bug resolution.

new version 5.5.2
Functionality Improvements :
- Bug managelist campaign creation.
- security update for browser and uploader images tool.

new version 5.5.1
Functionality Improvements :

Bug resolution in UserList.
- improvements in MailChimp.
- improvements in the public folder management.

5.5.0 version
- CKeditor image upload drag and drop
- full refactor of eDrive management
- MenuBar clarification
- public folder for MailChimp
- Mailchimp improvements and speed
- GDPR api
- mailchimp security user
- PHP 7.2 improvements
- Server scripts creation

Bugs correction
- GDPRListExport.php bug resolution
- WebDav bug resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:

---

## 5.5.2 (08/03/2019)
new version 5.5.2
Functionality Improvements :

- Bug managelist campaign creation.
- security update for browser and uploader images tool.

new version 5.5.1
Functionality Improvements :

Bug resolution in UserList.
- improvements in MailChimp.
- improvements in the public folder management.

5.5.0 version
- CKeditor image upload drag and drop
- full refactor of eDrive management
- MenuBar clarification
- public folder for MailChimp
- Mailchimp improvements and speed
- GDPR api
- mailchimp security user
- PHP 7.2 improvements
- Server scripts creation

Bugs correction
- GDPRListExport.php bug resolution
- WebDav bug resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:

---

## 5.5.1 (06/03/2019)
new version 5.5.1
Functionality Improvements :
- Bug resolution in UserList.
- improvements in MailChimp.
- improvements in the public folder management.

5.5.0 version
- CKeditor image upload drag and drop
- full refactor of eDrive management
- MenuBar clarification
- public folder for MailChimp
- Mailchimp improvements and speed
- GDPR api
- mailchimp security user
- PHP 7.2 improvements
- Server scripts creation

Bugs correction
- GDPRListExport.php bug resolution
- WebDav bug resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:

---

## 5.5.0 (24/02/2019)
new version 5.5.0
Functionality Improvements :

5.5.0 version
- CKeditor upload with drag and drop images
- new creditor image browser : drive
- full refactor of eDrive management
- MenuBar clarification
- public folder for MailChimp
- Mailchimp improvements and speed
- GDPR api
- mailchimp security user
- PHP 7.2 improvements
- Server scripts creation

Bugs correction
- GDPRListExport.php bug resolution
- WebDav bug resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:
---

## 5.4.3 (11/02/2019)
new version 5.4.3
Functionality Improvements :

5.4.3
- Ubuntu server 18.04 compatibilities.
- better support with php 7.2 and mariadb at installation.

5.4.2
- Mailchimp improvements (schedule campaign introduction)
- Mailchimp acceleration everything is now build with asynchronous system.
- CRMJSOM.js bug resolution and adds
- Event upgrade
- Phili67 volunteeropportunityeditor api
- EventEditor.js update
- MenuBar little bug resolution
- PersonView bug resolution

5.4.1 Only bug correction :
- CSP problem resolution : thank's to Erwan C.
- api with logger bug resolution (system + people-family).
- csp-report api bug correction.


5.4.0 version
- GDPR clarifications, we're working closely with lawers to finish the GDPR point.

- Security update :
  - EcclesiaCRM kernel is brand new inside (AdminLTE 2.4.8)
  - Full compatibility with the EcclesiaCRM theme update
  - all the npm packages are now at the last version
  - all the composer package too.
  - code clarifications in the api and the skin/js files.
  - introduction of the v2 api : proof of concept for security purpose.
  - when a person is de-activated the user is de-activated too.
  - calendar sidebar enhancement
  - Header-function security update.

- For finance show since et show for is rewritten
- The ckeditor icon merge tags update
- Age graph people in the People Dashboard.
- Better test to the BDD at install process.
- PersonView upgrade (security).
- ckeditor better support.
- better note and note share support.
- Login.php and Logout.php enhancements.
- Map css upgrade.

Bugs correction
- User management bug resolutions
- self-register.php date localisation resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:

---

## 5.4.2 (10/02/2019)
new version 5.4.2
Functionality Improvements :

5.4.2
- Mailchimp improvements (schedule campaign introduction)
- Mailchimp acceleration everything is now build with asynchronous system.
- CRMJSOM.js bug resolution and adds
- Event upgrade
- Phili67 volunteeropportunityeditor api
- EventEditor.js update
- MenuBar little bug resolution
- PersonView bug resolution

5.4.1 Only bug correction :
- CSP problem resolution : thank's to Erwan C.
- api with logger bug resolution (system + people-family).
- csp-report api bug correction.


5.4.0 version
- GDPR clarifications, we're working closely with lawers to finish the GDPR point.

- Security update :
  - EcclesiaCRM kernel is brand new inside (AdminLTE 2.4.8)
  - Full compatibility with the EcclesiaCRM theme update
  - all the npm packages are now at the last version
  - all the composer package too.
  - code clarifications in the api and the skin/js files.
  - introduction of the v2 api : proof of concept for security purpose.
  - when a person is de-activated the user is de-activated too.
  - calendar sidebar enhancement
  - Header-function security update.

- For finance show since et show for is rewritten
- The ckeditor icon merge tags update
- Age graph people in the People Dashboard.
- Better test to the BDD at install process.
- PersonView upgrade (security).
- ckeditor better support.
- better note and note share support.
- Login.php and Logout.php enhancements.
- Map css upgrade.

Bugs correction
- User management bug resolutions
- self-register.php date localisation resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:

---

## 5.4.1 (06/02/2019)
new version 5.4.1
Functionality Improvements :

Only bug correction :
- CSP problem resolution : thank's to Erwan C.
- api with logger bug resolution (system + people-family).
- csp-report api bug correction.


5.4.0 version
- GDPR clarifications, we're working closely with lawers to finish the GDPR point.

- Security update :a
  - EcclesiaCRM kernel is brand new inside (AdminLTE 2.4.8)
  - Full compatibility with the EcclesiaCRM theme update
  - all the npm packages are now at the last version
  - all the composer package too.
  - code clarifications in the api and the skin/js files.
  - introduction of the v2 api : proof of concept for security purpose.
  - when a person is de-activated the user is de-activated too.
  - calendar sidebar enhancement
  - Header-function security update.

- For finance show since et show for is rewritten
- The ckeditor icon merge tags update
- Age graph people in the People Dashboard.
- Better test to the BDD at install process.
- PersonView upgrade (security).
- ckeditor better support.
- better note and note share support.
- Login.php and Logout.php enhancements.
- Map css upgrade.

Bugs correction
- User management bug resolutions
- self-register.php date localisation resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:

---

## 5.4.0 (06/02/2019)
new version 5.4.0
Functionality Improvements :
- GDPR clarifications, we're working closely with lawers to finish the GDPR point.

- Security update :
  - EcclesiaCRM kernel is brand new inside (AdminLTE 2.4.8)
  - Full compatibility with the EcclesiaCRM theme update
  - all the npm packages are now at the last version
  - all the composer package too.
  - code clarifications in the api and the skin/js files.
  - introduction of the v2 api : proof of concept for security purpose.
  - when a person is de-activated the user is de-activated too.
  - calendar sidebar enhancement
  - Header-function security update.

- For finance show since et show for is rewritten
- The ckeditor icon merge tags update
- Age graph people in the People Dashboard.
- Better test to the BDD at install process.
- PersonView upgrade (security).
- ckeditor better support.
- better note and note share support.
- Login.php and Logout.php enhancements.
- Map css upgrade.

Bugs correction
- User management bug resolutions
- self-register.php date localisation resolution
- List event bug resolution for the number of people

Inner Beauty
- full new api v2
- new clarifications in the api and skin/js code.

Inner Coherence

See full changelog here:

---

## 5.3.1 (30/12/2018)
new version 5.3.1
Functionality Improvements :
- Bootstraper ChurchCRM upgrade
- CKeditor better support
- Note and Documents improvements
- FamilyView minor update
- PersonView Group improvements
- Better EDrive support
- Better Note to word export
- Better export managements

Bugs correction
- PersonView bug resolution
- self-register.php date localisation resolution

Inner Beauty

Inner Coherence

See full changelog here:

---

## 5.3.0 (25/12/2018)
new version 5.3.0
Functionality Improvements :
- MailChimp massive upgrade 2 : groups adds
- Vcard upgrade export for groups
- sidebar color light
- Update propel.php.dist
- Setup steps: slim application error
- CSS AdminLTE update
- Redirect logic rewritten
- SessionUser add
- User admin improvements : reservation admin manager improvements.
- user.php right upgrade
- SystemConfig clear code
- MenuBar update in full JS.
- Menubar custom items in full js too.

Bugs correction
- PersonView Bug resolution

Inner Beauty

Inner Coherence

See full changelog here:

---

## 5.2.0 (17/12/2018)
new version 5.2.0
Functionality Improvements :
- MailChimp upgrade
- mcrypt is deleted for php7.2 full compatibility
- UserEditor update code cleanup
- JS update no sort with classifications table
- C32019 PDF_Label add
- Calendar Reservation improvements
- PropertyEditor.php escape bug resolution
- Add badge format improvements
- OpenSSL requirement in the installation process
- Integrity check update
- CKeditor update (now with merge tags)
- PersonEditor NewsLetter update (a Person can now manage the newsletter flags)
- Mailchimp : Some improvements
- ManageList.js improvements
- MailChimp security upgrades
- Copyright upgrade
- CoutryDropdown stateDropDown update
- setup : Dropdown updates
- Dropdown update for CartToFamily.php PersonEditor.php FamilyEditor.php
- CSVImport.php update
- family-register.php update
- SelfRegister family : localisation update
- FamilyView minor update
- Family not in mailchimp title changes
- No more Runquery : FamilyView.php is full propeled
- No more Runquery : PersonView.php is full propeled


Bugs correction
- GDRP bug resolution
- Badges bug resolution

Inner Beauty
- adding two mysql constraints in person2volunteeropp_p2vo.
- property_pro has now a : pro_Comment column for GDRP.

Inner Coherence

See full changelog here:

---

## 5.1.0 (16/11/2018)
new version 5.1.0
Functionality Improvements :
- new role : Menu Query for In UserEditor.
- GDRP update you can now print the GDPR Data structure.
- GDRP update new fields (person properties and family properties).
- Security updates in the Settings.
- ManageEnvelopes.php is rewritten in boostraped a bug is corrected (classification).
- CKeditor can export now to word in EDrive
- Note is re-introduce to avoid a confusion with Edrive.
- VolunteerOpportunityEditor.php is completely in JS rewritten.
- financial security upgrade
- More Preview files for EDrive
- PersonView and FamilyView GUI update with the button delete and edit.
- ListEvent GUI upgrade.
- Sundayschool export translation.
- file manager and calendar share now send an email to the shared users.
- EDrive : FileManager enhancement (better drag behaviour).

Bugs correction
- Cart Family bug resolution.
- Family and person deletion solved.
- CSVCreateFile.php age bug resolution.
- Bug resolution in resize event.
- Bug resolution in CSV export for the sundayschool.

Inner Beauty
- adding two mysql constraints in person2volunteeropp_p2vo.
- property_pro has now a : pro_Comment column for GDRP.

Inner Coherence

See full changelog here:

---

## 5.0.0 (24/10/2018)
new version 5.0.0
Functionality Improvements :
- EDrive + real full file manager
![capture d ecran 2018-10-24 a 18 01 38](https://user-images.githubusercontent.com/20263693/47444601-24221300-d7b7-11e8-9ac8-e83debadc258.png)
- drag and drop
![capture d ecran 2018-10-24 a 18 03 02](https://user-images.githubusercontent.com/20263693/47444672-4d42a380-d7b7-11e8-9144-094b1b80a1ac.png)
- file preview
![capture d ecran 2018-10-24 a 18 01 50](https://user-images.githubusercontent.com/20263693/47444616-2b492100-d7b7-11e8-889d-0c6a98b18bca.png)
![capture d ecran 2018-10-24 a 18 01 56](https://user-images.githubusercontent.com/20263693/47444628-3308c580-d7b7-11e8-9dfe-86c66dce3d90.png)
- easy to rename a file, you've simply to double click a file
![capture d ecran 2018-10-24 a 18 02 23](https://user-images.githubusercontent.com/20263693/47444659-40be4b00-d7b7-11e8-925c-7510cdbe51a4.png)
- api filemanager
- etc ...

Bugs correction
- comma problem with numbers

Inner Beauty

Inner Coherence

See full changelog here:

---

## 4.9.2 (15/10/2018)
new version 4.9.2<br>Functionality Improvements :<br>- Calendar One recurrence event upgrade<br>- External api upgrade for the public calendar<br>- PHP 7.1 et 7.2 compatibility<br>- LettersAndLabels.php bug update<br>- PersonView and PersonEditor update<br>- Badge sticker update<br>- CartToBadge and SundaySchool Badge GUI update<br><br>Bugs correction<br>- NoteEditor.php bug resolution<br><br>Inner Beauty<br><br>Inner Coherence<br><br>See full changelog here:
---

## 4.9.1 (13/10/2018)
new version 4.9.1

Functionality Improvements :
- GDPR data structure
![capture d ecran 2018-10-12 a 23 50 42](https://user-images.githubusercontent.com/20263693/46901319-77d85680-ceb1-11e8-89ab-2210a791c44b.png)

Bugs correction
- IE11 compatibility again.
![capture d ecran 2018-10-12 a 23 48 28](https://user-images.githubusercontent.com/20263693/46901320-7c047400-ceb1-11e8-8f06-d5f159dd3cfe.png)



new version 4.9.0
Functionality Improvements :
- Calendar reservation : Room Video and computer + location too
Now you've , four different sort of calendars :
![capture d ecran 2018-10-04 a 11 32 58](https://user-images.githubusercontent.com/20263693/46465602-53052480-c7c9-11e8-976a-8bc348a8007a.png)
- personal
- group
- room, computer, and video projector
- share calendars
![capture d ecran 2018-10-04 a 11 24 23](https://user-images.githubusercontent.com/20263693/46465079-1dac0700-c7c8-11e8-84e5-bdf92d865b6b.png)

![capture d ecran 2018-10-04 a 11 24 33](https://user-images.githubusercontent.com/20263693/46465088-213f8e00-c7c8-11e8-93bb-b5585d4299df.png)

A calendar can have now a description in the case of a resource calendars :
![capture d ecran 2018-10-04 a 11 27 42](https://user-images.githubusercontent.com/20263693/46465311-927f4100-c7c8-11e8-8c36-18555695fdaf.png)

You can see the sort of events in the view :
![capture d ecran 2018-10-04 a 11 35 52](https://user-images.githubusercontent.com/20263693/46465752-b2fbcb00-c7c9-11e8-9546-4cdca1e1b970.png)


A resource can be located on the map with event location feature
![capture d ecran 2018-10-04 a 11 30 35](https://user-images.githubusercontent.com/20263693/46465477-fa358c00-c7c8-11e8-99a3-bbf3acf3e243.png)

![capture d ecran 2018-10-04 a 11 29 18](https://user-images.githubusercontent.com/20263693/46465397-c78b9380-c7c8-11e8-8c8b-28c4e579f049.png)

- File Manager
![capture d ecran 2018-10-11 a 00 26 44](https://user-images.githubusercontent.com/20263693/46769547-73af0c00-ccec-11e8-9896-0295cfe128dd.png)
![capture d ecran 2018-10-11 a 00 26 49](https://user-images.githubusercontent.com/20263693/46769850-5d558000-cced-11e8-85f0-695009c5755d.png)
![capture d ecran 2018-10-11 a 00 26 35](https://user-images.githubusercontent.com/20263693/46769856-61819d80-cced-11e8-89e5-203cd2aa10cd.png)

- personview familyview upgrade and optimisation
- properties api security options
- groups api upgrade
- MenuLinks upgrade

Bugs correction
- 

Inner Beauty

Inner Coherence

See full changelog here:

---

## 4.9.0 (11/10/2018)

new version 4.9.0
Functionality Improvements :
- Calendar reservation : Room Video and computer + location too
Now you've , four different sort of calendars :
![capture d ecran 2018-10-04 a 11 32 58](https://user-images.githubusercontent.com/20263693/46465602-53052480-c7c9-11e8-976a-8bc348a8007a.png)
- personal
- group
- room, computer, and video projector
- share calendars
![capture d ecran 2018-10-04 a 11 24 23](https://user-images.githubusercontent.com/20263693/46465079-1dac0700-c7c8-11e8-84e5-bdf92d865b6b.png)

![capture d ecran 2018-10-04 a 11 24 33](https://user-images.githubusercontent.com/20263693/46465088-213f8e00-c7c8-11e8-93bb-b5585d4299df.png)

A calendar can have now a description in the case of a resource calendars :
![capture d ecran 2018-10-04 a 11 27 42](https://user-images.githubusercontent.com/20263693/46465311-927f4100-c7c8-11e8-8c36-18555695fdaf.png)

You can see the sort of events in the view :
![capture d ecran 2018-10-04 a 11 35 52](https://user-images.githubusercontent.com/20263693/46465752-b2fbcb00-c7c9-11e8-9546-4cdca1e1b970.png)


A resource can be located on the map with event location feature
![capture d ecran 2018-10-04 a 11 30 35](https://user-images.githubusercontent.com/20263693/46465477-fa358c00-c7c8-11e8-99a3-bbf3acf3e243.png)

![capture d ecran 2018-10-04 a 11 29 18](https://user-images.githubusercontent.com/20263693/46465397-c78b9380-c7c8-11e8-8c8b-28c4e579f049.png)

- File Manager
![capture d ecran 2018-10-11 a 00 26 44](https://user-images.githubusercontent.com/20263693/46769547-73af0c00-ccec-11e8-9896-0295cfe128dd.png)
![capture d ecran 2018-10-11 a 00 26 49](https://user-images.githubusercontent.com/20263693/46769850-5d558000-cced-11e8-85f0-695009c5755d.png)
![capture d ecran 2018-10-11 a 00 26 35](https://user-images.githubusercontent.com/20263693/46769856-61819d80-cced-11e8-89e5-203cd2aa10cd.png)

- personview familyview upgrade and optimisation
- properties api security options
- groups api upgrade
- MenuLinks upgrade

Bugs correction
- 

Inner Beauty

Inner Coherence

See full changelog here:

---

## 4.8.0 (02/10/2018)
new version 4.8.0
Functionality Improvements :
- external calendar api upgrade for sabre
- translation upgrade
- calendarV2.php upgrade for the new external api
- public calendar with token
- eventsV2 upgrade (better management of recurence events).
- groups api little upgrade
- Cart delete CRMJsom upgrade with broadcaster/listener upgrade
- Person deletion update for 
- PDFLabel.php for CSV files UTF8
- CSVImport upgrade (when no field are added a message is added to the view)
- CSVImport + cart import too
- Calendar upgrade (coherence with color).
- SundaySchoolView GUI improvements : edition mode upgrade

Bugs correction
- SelectDelete.php bug resolution

Inner Beauty

Inner Coherence

See full changelog here:

---

## 4.7.6 (27/09/2018)
new version 4.7.6
- new edition mode in SundaySchoolView, real time cart add for each user.
![capture d ecran 2018-09-27 a 22 34 50](https://user-images.githubusercontent.com/20263693/46173091-97626300-c2a5-11e8-9f46-ddb4f161ec3a.png)
- csv import with add persons to cart
![capture d ecran 2018-09-27 a 22 34 26](https://user-images.githubusercontent.com/20263693/46173100-9df0da80-c2a5-11e8-8346-5461b234fa3a.png)
- last footer.js bug update
- re-introduction with the gettext function everywhere

new version 4.7.5
- Sundayschoolview update everything is now in real time and the add of each each user is faster, same with the deletion.

new version 4.7.4
Functionality Improvements :
- Sundayschoolview update :
  - New cart button for each students, 
![capture d ecran 2018-09-22 a 20 38 04](https://user-images.githubusercontent.com/20263693/45920639-bdec5c80-bea7-11e8-9456-b8af18676402.png)
  - the JS code is now moved to the js file too
  - the table is now order by dates too
  - The table can be filtered by gender
![capture d ecran 2018-09-22 a 20 38 29](https://user-images.githubusercontent.com/20263693/45920656-e8d6b080-bea7-11e8-880f-798c4fa0ce06.png)
  - everything is rewritten in JS code.
  - you can add members (with roles).
![capture d ecran 2018-09-22 a 20 38 09](https://user-images.githubusercontent.com/20263693/45920651-d3fa1d00-bea7-11e8-94bf-d6514d6ce54f.png)
  - delete members.
  - add them to the cart.
  - you can now directly click the address, mail, phone numbers and to open the compatible application.
  - BADGES : Now the people who are in the cart are directly can be badged
  - Now when you add members, you're in edition mode, so all the unusefull part will be removed.
![capture d ecran 2018-09-22 a 20 38 29](https://user-images.githubusercontent.com/20263693/45920653-dfe5df00-bea7-11e8-9be0-916c28562d29.png)
- SystemConfig : Date format simplifications
![capture d ecran 2018-09-22 a 20 47 55](https://user-images.githubusercontent.com/20263693/45920714-e58ff480-bea8-11e8-8699-a0ceda183409.png)
- SelectList.js code security
- PersonEditor.php message upgrade (when a family is added)
![capture d ecran 2018-09-22 a 20 49 18](https://user-images.githubusercontent.com/20263693/45920745-3d2e6000-bea9-11e8-8974-1299d58f5cce.png)
- Cart protection GDPR
- CartView.php security update
- Documents in PersonView.php reflect only one document.
- GUI : DirectoryReports.php, BackupDatabase.php, UserPasswordChange.php
- CSVCreateFile.php is rewritten.
- Massiv CSVImport.php rewrite
![capture d ecran 2018-09-22 a 20 44 30](https://user-images.githubusercontent.com/20263693/45920684-6b5f7000-bea8-11e8-86f0-b6171b9919fc.png)
![capture d ecran 2018-09-22 a 20 44 49](https://user-images.githubusercontent.com/20263693/45920686-70242400-bea8-11e8-8d0c-d198920f4e98.png)
- There's now a mechanism to avoid duplicate name :
![capture d ecran 2018-09-22 a 20 45 53](https://user-images.githubusercontent.com/20263693/45920692-87fba800-bea8-11e8-8e97-ec0c5556a5c6.png)
 - the clear mechanism is rewritten too.
![capture d ecran 2018-09-22 a 20 45 02](https://user-images.githubusercontent.com/20263693/45920687-74504180-bea8-11e8-8c2a-f46d06f8f209.png)
- CSV import for coordinates in family_fam
- UserSetting.php is unusefull because of the user roles, there's no more UserDefault settings, because there's user roles.
- LabelFunction.php is now unusefull


Bugs correction
- FontSelect is now rewritten, there's no more crash.

Inner Beauty
- isSundayShoolTeachForGroup is changed to isSundayShoolTeacherForGroup

Inner Coherence

See full changelog here:
---

## 4.7.5 (23/09/2018)
new version 4.7.5
- Sundayschoolview update everything is now in real time and the add of each each user is faster, same with the deletion.

new version 4.7.4
Functionality Improvements :
- Sundayschoolview update :
  - New cart button for each students, 
![capture d ecran 2018-09-22 a 20 38 04](https://user-images.githubusercontent.com/20263693/45920639-bdec5c80-bea7-11e8-9456-b8af18676402.png)
  - the JS code is now moved to the js file too
  - the table is now order by dates too
  - The table can be filtered by gender
![capture d ecran 2018-09-22 a 20 38 29](https://user-images.githubusercontent.com/20263693/45920656-e8d6b080-bea7-11e8-880f-798c4fa0ce06.png)
  - everything is rewritten in JS code.
  - you can add members (with roles).
![capture d ecran 2018-09-22 a 20 38 09](https://user-images.githubusercontent.com/20263693/45920651-d3fa1d00-bea7-11e8-94bf-d6514d6ce54f.png)
  - delete members.
  - add them to the cart.
  - you can now directly click the address, mail, phone numbers and to open the compatible application.
  - BADGES : Now the people who are in the cart are directly can be badged
  - Now when you add members, you're in edition mode, so all the unusefull part will be removed.
![capture d ecran 2018-09-22 a 20 38 29](https://user-images.githubusercontent.com/20263693/45920653-dfe5df00-bea7-11e8-9be0-916c28562d29.png)
- SystemConfig : Date format simplifications
![capture d ecran 2018-09-22 a 20 47 55](https://user-images.githubusercontent.com/20263693/45920714-e58ff480-bea8-11e8-8699-a0ceda183409.png)
- SelectList.js code security
- PersonEditor.php message upgrade (when a family is added)
![capture d ecran 2018-09-22 a 20 49 18](https://user-images.githubusercontent.com/20263693/45920745-3d2e6000-bea9-11e8-8974-1299d58f5cce.png)
- Cart protection GDPR
- CartView.php security update
- Documents in PersonView.php reflect only one document.
- GUI : DirectoryReports.php, BackupDatabase.php, UserPasswordChange.php
- CSVCreateFile.php is rewritten.
- Massiv CSVImport.php rewrite
![capture d ecran 2018-09-22 a 20 44 30](https://user-images.githubusercontent.com/20263693/45920684-6b5f7000-bea8-11e8-86f0-b6171b9919fc.png)
![capture d ecran 2018-09-22 a 20 44 49](https://user-images.githubusercontent.com/20263693/45920686-70242400-bea8-11e8-8d0c-d198920f4e98.png)
- There's now a mechanism to avoid duplicate name :
![capture d ecran 2018-09-22 a 20 45 53](https://user-images.githubusercontent.com/20263693/45920692-87fba800-bea8-11e8-8e97-ec0c5556a5c6.png)
 - the clear mechanism is rewritten too.
![capture d ecran 2018-09-22 a 20 45 02](https://user-images.githubusercontent.com/20263693/45920687-74504180-bea8-11e8-8c2a-f46d06f8f209.png)
- CSV import for coordinates in family_fam
- UserSetting.php is unusefull because of the user roles, there's no more UserDefault settings, because there's user roles.
- LabelFunction.php is now unusefull


Bugs correction
- FontSelect is now rewritten, there's no more crash.

Inner Beauty
- isSundayShoolTeachForGroup is changed to isSundayShoolTeacherForGroup

Inner Coherence

See full changelog here:
---

## 4.7.4 (22/09/2018)
new version 4.7.4
Functionality Improvements :
- Sundayschoolview update :
  - New cart button for each students, 
![capture d ecran 2018-09-22 a 20 38 04](https://user-images.githubusercontent.com/20263693/45920639-bdec5c80-bea7-11e8-9456-b8af18676402.png)
  - the JS code is now moved to the js file too
  - the table is now order by dates too
  - The table can be filtered by gender
![capture d ecran 2018-09-22 a 20 38 29](https://user-images.githubusercontent.com/20263693/45920656-e8d6b080-bea7-11e8-880f-798c4fa0ce06.png)
  - everything is rewritten in JS code.
  - you can add members (with roles).
![capture d ecran 2018-09-22 a 20 38 09](https://user-images.githubusercontent.com/20263693/45920651-d3fa1d00-bea7-11e8-94bf-d6514d6ce54f.png)
  - delete members.
  - add them to the cart.
  - you can now directly click the address, mail, phone numbers and to open the compatible application.
  - BADGES : Now the people who are in the cart are directly can be badged
  - Now when you add members, you're in edition mode, so all the unusefull part will be removed.
![capture d ecran 2018-09-22 a 20 38 29](https://user-images.githubusercontent.com/20263693/45920653-dfe5df00-bea7-11e8-9be0-916c28562d29.png)
- SystemConfig : Date format simplifications
![capture d ecran 2018-09-22 a 20 47 55](https://user-images.githubusercontent.com/20263693/45920714-e58ff480-bea8-11e8-8699-a0ceda183409.png)
- SelectList.js code security
- PersonEditor.php message upgrade (when a family is added)
![capture d ecran 2018-09-22 a 20 49 18](https://user-images.githubusercontent.com/20263693/45920745-3d2e6000-bea9-11e8-8974-1299d58f5cce.png)
- Cart protection GDPR
- CartView.php security update
- Documents in PersonView.php reflect only one document.
- GUI : DirectoryReports.php, BackupDatabase.php, UserPasswordChange.php
- CSVCreateFile.php is rewritten.
- Massiv CSVImport.php rewrite
![capture d ecran 2018-09-22 a 20 44 30](https://user-images.githubusercontent.com/20263693/45920684-6b5f7000-bea8-11e8-86f0-b6171b9919fc.png)
![capture d ecran 2018-09-22 a 20 44 49](https://user-images.githubusercontent.com/20263693/45920686-70242400-bea8-11e8-8d0c-d198920f4e98.png)
- There's now a mechanism to avoid duplicate name :
![capture d ecran 2018-09-22 a 20 45 53](https://user-images.githubusercontent.com/20263693/45920692-87fba800-bea8-11e8-8e97-ec0c5556a5c6.png)
 - the clear mechanism is rewritten too.
![capture d ecran 2018-09-22 a 20 45 02](https://user-images.githubusercontent.com/20263693/45920687-74504180-bea8-11e8-8c2a-f46d06f8f209.png)
- CSV import for coordinates in family_fam
- UserSetting.php is unusefull because of the user roles, there's no more UserDefault settings, because there's user roles.
- LabelFunction.php is now unusefull


Bugs correction
- FontSelect is now rewritten, there's no more crash.

Inner Beauty
- isSundayShoolTeachForGroup is changed to isSundayShoolTeacherForGroup

Inner Coherence

See full changelog here:
---

## 4.7.3 (15/09/2018)
new version 4.7.3
Functionality Improvements :
- Add Badges to CartView.php (now you can create Badges for your team).
- Add the ElectronicPaymentList.php a lack of a translated term
- Badges refactor for the sundayschool too

Bugs correction
- FontSelect is now rewritten, there's no more crash in foreign language.

new version 4.7.2
Functionality Improvements :
- Add the property to the badge.
- Add the ElectronicPaymentList.php and rewrite it in full ORM.

Bugs correction
- PersonView and FamilyView visual bug correction.

Inner Beauty
- Security for the payment api.
- Refactor of the Autopayment table with constraint

Inner Coherence

See full changelog here:

new version 4.7.1
Functionality Improvements:
- Add badge type.

new version 4.7.0
Functionality Improvements :
- SelectDelete.php is propeled
- SelectDelete.php updated (it can manage Person or Family pledges)
- PersonEditor optimisation
- User Finance Security update
- Menu Nice color upgrade
- Student badges
- DepositSlipEditor.js coherence
- Now the Family list will view only real families
- Personeditor rewrite in function of SelectDelete (A one person family is now deleted with the Family too).
- Now the PersonEditor.js focus button for the address.
- PersonEditor nice switch from one family to another
- PersonEditor left right code refactor for the custom fields.
- FamilyEditor left right code refactor for the custom fields.
- CartView.php code refactor.
- A one person Family is no more viewed.
- FamilyEditor.php is now completely rewritten.
- self-verify-updates.php localisation
- Propeled code : CartToFamily.php, PropertyTypeDelete.php, CartView.php, DirectoryReports.php is now propeled
- PersonEditor enhancement with address entry

Bugs correction
- bug PersonEditor.php bug resolution.
- bug resolution with DirectoryReport phone numbers.

Inner Beauty
- ExpandPhoneNumber is now unusefull

Inner Coherence

See full changelog here:

---

## 4.7.2 (13/09/2018)
new version 4.7.2
Functionality Improvements :
- Add the property to the badge.
- Add the ElectronicPaymentList.php and rewrite it in full ORM.

Bugs correction
- PersonView and FamilyView visual bug correction.

Inner Beauty
- Security for the payment api.
- Refactor of the Autopayment table with constraint

Inner Coherence

See full changelog here:

new version 4.7.1
Functionality Improvements:
- Add badge type.

new version 4.7.0
Functionality Improvements :
- SelectDelete.php is propeled
- SelectDelete.php updated (it can manage Person or Family pledges)
- PersonEditor optimisation
- User Finance Security update
- Menu Nice color upgrade
- Student badges
- DepositSlipEditor.js coherence
- Now the Family list will view only real families
- Personeditor rewrite in function of SelectDelete (A one person family is now deleted with the Family too).
- Now the PersonEditor.js focus button for the address.
- PersonEditor nice switch from one family to another
- PersonEditor left right code refactor for the custom fields.
- FamilyEditor left right code refactor for the custom fields.
- CartView.php code refactor.
- A one person Family is no more viewed.
- FamilyEditor.php is now completely rewritten.
- self-verify-updates.php localisation
- Propeled code : CartToFamily.php, PropertyTypeDelete.php, CartView.php, DirectoryReports.php is now propeled
- PersonEditor enhancement with address entry

Bugs correction
- bug PersonEditor.php bug resolution.
- bug resolution with DirectoryReport phone numbers.

Inner Beauty
- ExpandPhoneNumber is now unusefull

Inner Coherence

See full changelog here:

---

## 4.7.1 (12/09/2018)
new version 4.7.1
Functionality Improvements:
- Add badge type.

new version 4.7.0
Functionality Improvements :
- SelectDelete.php is propeled
- SelectDelete.php updated (it can manage Person or Family pledges)
- PersonEditor optimisation
- User Finance Security update
- Menu Nice color upgrade
- Student badges
- DepositSlipEditor.js coherence
- Now the Family list will view only real families
- Personeditor rewrite in function of SelectDelete (A one person family is now deleted with the Family too).
- Now the PersonEditor.js focus button for the address.
- PersonEditor nice switch from one family to another
- PersonEditor left right code refactor for the custom fields.
- FamilyEditor left right code refactor for the custom fields.
- CartView.php code refactor.
- A one person Family is no more viewed.
- FamilyEditor.php is now completely rewritten.
- self-verify-updates.php localisation
- Propeled code : CartToFamily.php, PropertyTypeDelete.php, CartView.php, DirectoryReports.php is now propeled
- PersonEditor enhancement with address entry

Bugs correction
- bug PersonEditor.php bug resolution.
- bug resolution with DirectoryReport phone numbers.

Inner Beauty
- ExpandPhoneNumber is now unusefull

Inner Coherence

See full changelog here:

---

## 4.7.0 (11/09/2018)
new version 4.7.0
Functionality Improvements :
- SelectDelete.php is propeled
- SelectDelete.php updated (it can manage Person or Family pledges)
- PersonEditor optimisation
- User Finance Security update
- Menu Nice color upgrade
- Student badges
- DepositSlipEditor.js coherence
- Now the Family list will view only real families
- Personeditor rewrite in function of SelectDelete (A one person family is now deleted with the Family too).
- Now the PersonEditor.js focus button for the address.
- PersonEditor nice switch from one family to another
- PersonEditor left right code refactor for the custom fields.
- FamilyEditor left right code refactor for the custom fields.
- CartView.php code refactor.
- A one person Family is no more viewed.
- FamilyEditor.php is now completely rewritten.
- self-verify-updates.php localisation
- Propeled code : CartToFamily.php, PropertyTypeDelete.php, CartView.php, DirectoryReports.php is now propeled
- PersonEditor enhancement with address entry

Bugs correction
- bug PersonEditor.php bug resolution.
- bug resolution with DirectoryReport phone numbers.

Inner Beauty
- ExpandPhoneNumber is now unusefull

Inner Coherence

See full changelog here:

---

## 4.6.2 (09/09/2018)
new version 4.6.2
Functionality Improvements :
- DepositSlipEditor.js coherence
- Now the Family list will view only real families
- SelectDelete.php updated (it can manage Person or Family pledges)
- Personeditor rewrite in function of SelectDelete (A one person family is now deleted with the Family too).
- Now the PersonEditor.js focus button for the address.
- PersonEditor nice switch from one family to another
- PersonEditor left right code refactor for the custom fields.
- FamilyEditor left right code refactor for the custom fields.
- CartView.php code refactor.
- A one person Family is no more viewed.
- FamilyEditor.php is now completely rewritten.
- self-verify-updates.php localisation
- Propeled code : CartToFamily.php, PropertyTypeDelete.php, CartView.php, DirectoryReports.php is now propeled
- PersonEditor enhancement with address entry

Bugs correction
- bug PersonEditor.php bug resolution.
- bug resolution with DirectoryReport phone numbers.

new version 4.6.1
Functionality Improvements :
- Menu Links optimisation code.
- Propeled code : CartToFamily.php, PropertyTypeDelete.php, CartView.php, DirectoryReports.php is now propeled
- PersonEditor enhancement with address entry

Bugs correction
- bug resolution with opened document.
- bug resolution with DirectoryReport phone numbers.

Inner Beauty
- ExpandPhoneNumber is now unusefull
new version 4.6.0
Functionality Improvements :
- Security update : JWT
- First Implementation of JWT authentication.
- Speed optimisation
- CRMJSOM + security update.
- Menu Links Updates.
- Kiosk bug update.
- GroupPersonPropsFormEditor.php update
- GroupPropsFormEditor.php GUI update
- Propeled code OptionManagerRowOps.php optimisation OptionsManager.php
- OptionManager.php is propeled

Bugs correction

Inner Beauty
- sql upgrade to implement PersonCustomMasterQuery and FamilyCustomMasterQuery classes.

Inner Coherence

See full changelog here:
---

## 4.6.1 (05/09/2018)
new version 4.6.1
Functionality Improvements :
- Menu Links optimisation code.
- Propeled code : CartToFamily.php, PropertyTypeDelete.php, CartView.php, DirectoryReports.php is now propeled
- PersonEditor enhancement with address entry

Bugs correction
- bug resolution with opened document.
- bug resolution with DirectoryReport phone numbers.

Inner Beauty
- ExpandPhoneNumber is now unusefull
new version 4.6.0
Functionality Improvements :
- Security update : JWT
- First Implementation of JWT authentication.
- Speed optimisation
- CRMJSOM + security update.
- Menu Links Updates.
- Kiosk bug update.
- GroupPersonPropsFormEditor.php update
- GroupPropsFormEditor.php GUI update
- Propeled code OptionManagerRowOps.php optimisation OptionsManager.php
- OptionManager.php is propeled

Bugs correction

Inner Beauty
- sql upgrade to implement PersonCustomMasterQuery and FamilyCustomMasterQuery classes.

Inner Coherence

See full changelog here:
---

## 4.6.0 (02/09/2018)
new version 4.6.0
Functionality Improvements :
- Security update : JWT
- First Implementation of JWT authentication.
- Speed optimisation
- CRMJSOM + security update.
- Menu Links Updates.
- Kiosk bug update.
- GroupPersonPropsFormEditor.php update
- GroupPropsFormEditor.php GUI update
- Propeled code OptionManagerRowOps.php optimisation OptionsManager.php
- OptionManager.php is propeled

Bugs correction

Inner Beauty
- sql upgrade to implement PersonCustomMasterQuery and FamilyCustomMasterQuery classes.

Inner Coherence

See full changelog here:
---

## 4.5.2 (27/08/2018)
New version 4.5.2
- GUI updates
![capture d ecran 2018-08-27 a 22 27 30](https://user-images.githubusercontent.com/20263693/44684124-84f7ce00-aa48-11e8-8943-e20599a5bc97.png)
![capture d ecran 2018-08-27 a 22 27 39](https://user-images.githubusercontent.com/20263693/44684127-86c19180-aa48-11e8-8bee-98ac1b8f7042.png)
![capture d ecran 2018-08-27 a 22 28 04](https://user-images.githubusercontent.com/20263693/44684132-8923eb80-aa48-11e8-8f87-269362f95197.png)

- Now the FamilyCustomFieldsEditor.php + PersonCustomFieldsEditor.php + order is now corrected
- Minor Menu update
![capture d ecran 2018-08-27 a 15 06 45](https://user-images.githubusercontent.com/20263693/44661406-e0a36680-aa0a-11e8-8648-e0d3571fe493.png)
- More JS code in PersonView, the properties and Volunteer part need no more page reload

new version 4.5.1
Better translations.
Now you can order the Custom Menus items
![capture d ecran 2018-08-26 a 20 25 26](https://user-images.githubusercontent.com/20263693/44632142-29f5a680-a976-11e8-9210-424f76b6a866.png)

![capture d ecran 2018-08-26 a 20 29 38](https://user-images.githubusercontent.com/20263693/44632143-2e21c400-a976-11e8-86b5-08a3d7cba511.png)

new version 4.5.0
Functionality Improvements :
- GDPR enhancements.
- The Pastoral care is now searchable.
![capture d ecran 2018-08-25 a 21 08 46](https://user-images.githubusercontent.com/20263693/44621699-192c2e80-a8ab-11e8-893e-7b5b07eefcd9.png)
- The pastoral care is now mentioned on the Main Dashboard.
![capture d ecran 2018-08-25 a 20 47 10](https://user-images.githubusercontent.com/20263693/44621689-f13ccb00-a8aa-11e8-81b3-9f5ea969872a.png)
- UserEditor skin is now saved in the user profile.
- Notification upgrade modification.
![capture d ecran 2018-08-25 a 21 09 41](https://user-images.githubusercontent.com/20263693/44621710-3660fd00-a8ab-11e8-8d81-43427f2576c9.png)
- Security in the Webdav server (introduction of UUID directory for each users).
- UserList massive update.
![capture d ecran 2018-08-25 a 21 10 48](https://user-images.githubusercontent.com/20263693/44621715-5ee8f700-a8ab-11e8-812f-930d7360fabd.png)
You can apply a role for many users together.
- Enable Features settings.
![capture d ecran 2018-08-25 a 21 11 38](https://user-images.githubusercontent.com/20263693/44621716-7b852f00-a8ab-11e8-9ad1-6313ef3ebff8.png)
- Global and Personal Menu Links.
![capture d ecran 2018-08-25 a 20 46 30](https://user-images.githubusercontent.com/20263693/44621735-c9019c00-a8ab-11e8-9066-8748ea1db3c7.png)
![capture d ecran 2018-08-25 a 20 47 02](https://user-images.githubusercontent.com/20263693/44621737-ce5ee680-a8ab-11e8-891a-b2c2062d968e.png)
- better translation of the property list and editor.
- coherence with the DataTables

Bugs correction
- bug resolution when you change language to another.

Inner Beauty
- better optimisation of the js code and po code too, everything is now seperated.
- improvements in the number translations.


Inner Coherence

See full changelog here:

---

## 4.5.1 (26/08/2018)
new version 4.5.1
Better translations.
Now you can order the Custom Menus items
![capture d ecran 2018-08-26 a 20 25 26](https://user-images.githubusercontent.com/20263693/44632142-29f5a680-a976-11e8-9210-424f76b6a866.png)

![capture d ecran 2018-08-26 a 20 29 38](https://user-images.githubusercontent.com/20263693/44632143-2e21c400-a976-11e8-86b5-08a3d7cba511.png)

new version 4.5.0
Functionality Improvements :
- GDPR enhancements.
- The Pastoral care is now searchable.
![capture d ecran 2018-08-25 a 21 08 46](https://user-images.githubusercontent.com/20263693/44621699-192c2e80-a8ab-11e8-893e-7b5b07eefcd9.png)
- The pastoral care is now mentioned on the Main Dashboard.
![capture d ecran 2018-08-25 a 20 47 10](https://user-images.githubusercontent.com/20263693/44621689-f13ccb00-a8aa-11e8-81b3-9f5ea969872a.png)
- UserEditor skin is now saved in the user profile.
- Notification upgrade modification.
![capture d ecran 2018-08-25 a 21 09 41](https://user-images.githubusercontent.com/20263693/44621710-3660fd00-a8ab-11e8-8d81-43427f2576c9.png)
- Security in the Webdav server (introduction of UUID directory for each users).
- UserList massive update.
![capture d ecran 2018-08-25 a 21 10 48](https://user-images.githubusercontent.com/20263693/44621715-5ee8f700-a8ab-11e8-812f-930d7360fabd.png)
You can apply a role for many users together.
- Enable Features settings.
![capture d ecran 2018-08-25 a 21 11 38](https://user-images.githubusercontent.com/20263693/44621716-7b852f00-a8ab-11e8-9ad1-6313ef3ebff8.png)
- Global and Personal Menu Links.
![capture d ecran 2018-08-25 a 20 46 30](https://user-images.githubusercontent.com/20263693/44621735-c9019c00-a8ab-11e8-9066-8748ea1db3c7.png)
![capture d ecran 2018-08-25 a 20 47 02](https://user-images.githubusercontent.com/20263693/44621737-ce5ee680-a8ab-11e8-891a-b2c2062d968e.png)
- better translation of the property list and editor.
- coherence with the DataTables

Bugs correction
- bug resolution when you change language to another.

Inner Beauty
- better optimisation of the js code and po code too, everything is now seperated.
- improvements in the number translations.


Inner Coherence

See full changelog here:

---

## 4.5.0 (26/08/2018)
new version 4.5.0
Functionality Improvements :
- GDPR enhancements.
- The Pastoral care is now searchable.
![capture d ecran 2018-08-25 a 21 08 46](https://user-images.githubusercontent.com/20263693/44621699-192c2e80-a8ab-11e8-893e-7b5b07eefcd9.png)
- The pastoral care is now mentioned on the Main Dashboard.
![capture d ecran 2018-08-25 a 20 47 10](https://user-images.githubusercontent.com/20263693/44621689-f13ccb00-a8aa-11e8-81b3-9f5ea969872a.png)
- UserEditor skin is now saved in the user profile.
- Notification upgrade modification.
![capture d ecran 2018-08-25 a 21 09 41](https://user-images.githubusercontent.com/20263693/44621710-3660fd00-a8ab-11e8-8d81-43427f2576c9.png)
- Security in the Webdav server (introduction of UUID directory for each users).
- UserList massive update.
![capture d ecran 2018-08-25 a 21 10 48](https://user-images.githubusercontent.com/20263693/44621715-5ee8f700-a8ab-11e8-812f-930d7360fabd.png)
You can apply a role for many users together.
- Enable Features settings.
![capture d ecran 2018-08-25 a 21 11 38](https://user-images.githubusercontent.com/20263693/44621716-7b852f00-a8ab-11e8-9ad1-6313ef3ebff8.png)
- Global and Personal Menu Links.
![capture d ecran 2018-08-25 a 20 46 30](https://user-images.githubusercontent.com/20263693/44621735-c9019c00-a8ab-11e8-9066-8748ea1db3c7.png)
![capture d ecran 2018-08-25 a 20 47 02](https://user-images.githubusercontent.com/20263693/44621737-ce5ee680-a8ab-11e8-891a-b2c2062d968e.png)
- better translation of the property list and editor.
- coherence with the DataTables

Bugs correction
- bug resolution when you change language to another.

Inner Beauty
- better optimisation of the js code and po code too, everything is now seperated.
- improvements in the number translations.


Inner Coherence

See full changelog here:

---

## 4.4.2 (18/08/2018)
new version 4.4.2
- A bug is corrected with the new Person deactivation function and the Maps.

new version 4.4.1
- The numbers are now fully translated in foreign languages too.
- Localisation with US datatable are now corrected.

new version 4.4.0
Functionality Improvements :
- New full design for the CRM (brand new skin and icons).
![capture d ecran 2018-08-11 a 18 13 07](https://user-images.githubusercontent.com/20263693/43993713-40920a68-9d92-11e8-8184-e8b503c76ba4.png)
![capture d ecran 2018-08-11 a 18 29 34](https://user-images.githubusercontent.com/20263693/43993899-8e044f5c-9d94-11e8-8297-34a00112b706.png)
- Favicon for iPhone, android etc ...
![capture d ecran 2018-08-11 a 18 14 25](https://user-images.githubusercontent.com/20263693/43993725-68caf95e-9d92-11e8-90ab-e1a749362ce5.png)
- New installation process, now everything can be set through the new installer.
![capture d ecran 2018-08-11 a 18 16 01](https://user-images.githubusercontent.com/20263693/43993748-a8b7cbaa-9d92-11e8-8055-b49bdf3a538b.png)
- Foreign translation at installation and better translation files, during the installation process.
- Patoral Care : No more WhyCame, it's now Pastoral Care, a full personalisable parts, with a special role Pastoral care in the UserEditor
![capture d ecran 2018-08-11 a 18 17 54](https://user-images.githubusercontent.com/20263693/43993762-e50cb3b8-9d92-11e8-9348-f61243627b1a.png)
Now you can add more comments and work with different pastors :
![capture d ecran 2018-08-11 a 18 19 45](https://user-images.githubusercontent.com/20263693/43993779-2a7645a4-9d93-11e8-82e8-90724e43d65f.png)
![capture d ecran 2018-08-11 a 18 20 51](https://user-images.githubusercontent.com/20263693/43993788-530ec2f2-9d93-11e8-96fb-8cd4485f89e4.png)
You can see a filter
- It's possible to personalise the type of Pastoral care 
![capture d ecran 2018-08-11 a 18 21 32](https://user-images.githubusercontent.com/20263693/43993795-66223536-9d93-11e8-9901-456a055c50fe.png)
So you can follow a person as you want.
- GDPR + DPO
![capture d ecran 2018-08-11 a 18 23 15](https://user-images.githubusercontent.com/20263693/43993811-a62ce1bc-9d93-11e8-9856-8d8c21440031.png)
Now you can de-activate a person :
![capture d ecran 2018-08-11 a 18 17 54](https://user-images.githubusercontent.com/20263693/43993802-8d663ea8-9d93-11e8-85f6-3e2ca3d7809d.png)
- To manage the GDPR and DPO : bGDPR, sGdprDpoSigner, sGdprDpoSignerEmail and iGdprExpirationDate set duration of the datas persistence
- every part is protected in function of GDPR.
When you de-activate a person or a family, during the 3 first years, an administrator can reactivate them.
After only a dpo can delete or re-activate them.
Important : it's impossible for a dpo to delete a person or a family with activated donations.
- MaindashBoard user setting, so you can create a special profile like a full administrator.
- Dashboard improvement
- MailChimp role in SystemSettings + restriction
- SystemConfig refactor
- classification icons are now manage in real time
- Prompt to view the number of groups.
- Better render for printView
- Better toolbar management
- Spanish  support : 99% and German support : 98% .
- better support for localized numbers in foreign languages.
- 100% Support of belgium, France, Swiss, Austrelia  with the régions, States, provinces, cantons, Bundesland.
- better financial Report, everything is rewritten too.

Bugs correction
- A bug in ChuchMetaData is corrected.
- a bug with the anniversaries and birthdays event is now corrected.
- bug with localisation event in the Calendar.
- bug resolution when you change language to another.

Inner Beauty
- better optimisation of the js code and po code too, everything is now seperated.


Inner Coherence

See full changelog here:
---

## 4.4.1 (16/08/2018)
new version 4.4.1
- The numbers are now fully translated in foreign languages too.
- Localisation with US datatable are now corrected.

new version 4.4.0
Functionality Improvements :
- New full design for the CRM (brand new skin and icons).
![capture d ecran 2018-08-11 a 18 13 07](https://user-images.githubusercontent.com/20263693/43993713-40920a68-9d92-11e8-8184-e8b503c76ba4.png)
![capture d ecran 2018-08-11 a 18 29 34](https://user-images.githubusercontent.com/20263693/43993899-8e044f5c-9d94-11e8-8297-34a00112b706.png)
- Favicon for iPhone, android etc ...
![capture d ecran 2018-08-11 a 18 14 25](https://user-images.githubusercontent.com/20263693/43993725-68caf95e-9d92-11e8-90ab-e1a749362ce5.png)
- New installation process, now everything can be set through the new installer.
![capture d ecran 2018-08-11 a 18 16 01](https://user-images.githubusercontent.com/20263693/43993748-a8b7cbaa-9d92-11e8-8055-b49bdf3a538b.png)
- Foreign translation at installation and better translation files, during the installation process.
- Patoral Care : No more WhyCame, it's now Pastoral Care, a full personalisable parts, with a special role Pastoral care in the UserEditor
![capture d ecran 2018-08-11 a 18 17 54](https://user-images.githubusercontent.com/20263693/43993762-e50cb3b8-9d92-11e8-9348-f61243627b1a.png)
Now you can add more comments and work with different pastors :
![capture d ecran 2018-08-11 a 18 19 45](https://user-images.githubusercontent.com/20263693/43993779-2a7645a4-9d93-11e8-82e8-90724e43d65f.png)
![capture d ecran 2018-08-11 a 18 20 51](https://user-images.githubusercontent.com/20263693/43993788-530ec2f2-9d93-11e8-96fb-8cd4485f89e4.png)
You can see a filter
- It's possible to personalise the type of Pastoral care 
![capture d ecran 2018-08-11 a 18 21 32](https://user-images.githubusercontent.com/20263693/43993795-66223536-9d93-11e8-9901-456a055c50fe.png)
So you can follow a person as you want.
- GDPR + DPO
![capture d ecran 2018-08-11 a 18 23 15](https://user-images.githubusercontent.com/20263693/43993811-a62ce1bc-9d93-11e8-9856-8d8c21440031.png)
Now you can de-activate a person :
![capture d ecran 2018-08-11 a 18 17 54](https://user-images.githubusercontent.com/20263693/43993802-8d663ea8-9d93-11e8-85f6-3e2ca3d7809d.png)
- To manage the GDPR and DPO : bGDPR, sGdprDpoSigner, sGdprDpoSignerEmail and iGdprExpirationDate set duration of the datas persistence
- every part is protected in function of GDPR.
When you de-activate a person or a family, during the 3 first years, an administrator can reactivate them.
After only a dpo can delete or re-activate them.
Important : it's impossible for a dpo to delete a person or a family with activated donations.
- MaindashBoard user setting, so you can create a special profile like a full administrator.
- Dashboard improvement
- MailChimp role in SystemSettings + restriction
- SystemConfig refactor
- classification icons are now manage in real time
- Prompt to view the number of groups.
- Better render for printView
- Better toolbar management
- Spanish  support : 99% and German support : 98% .
- better support for localized numbers in foreign languages.
- 100% Support of belgium, France, Swiss, Austrelia  with the régions, States, provinces, cantons, Bundesland.
- better financial Report, everything is rewritten too.

Bugs correction
- A bug in ChuchMetaData is corrected.
- a bug with the anniversaries and birthdays event is now corrected.
- bug with localisation event in the Calendar.
- bug resolution when you change language to another.

Inner Beauty
- better optimisation of the js code and po code too, everything is now seperated.


Inner Coherence

See full changelog here:
---

## 4.4.0 (11/08/2018)
new version 4.4.0
Functionality Improvements :
- New full design for the CRM (brand new skin and icons).
![capture d ecran 2018-08-11 a 18 13 07](https://user-images.githubusercontent.com/20263693/43993713-40920a68-9d92-11e8-8184-e8b503c76ba4.png)
![capture d ecran 2018-08-11 a 18 29 34](https://user-images.githubusercontent.com/20263693/43993899-8e044f5c-9d94-11e8-8297-34a00112b706.png)
- Favicon for iPhone, android etc ...
![capture d ecran 2018-08-11 a 18 14 25](https://user-images.githubusercontent.com/20263693/43993725-68caf95e-9d92-11e8-90ab-e1a749362ce5.png)
- New installation process, now everything can be set through the new installer.
![capture d ecran 2018-08-11 a 18 16 01](https://user-images.githubusercontent.com/20263693/43993748-a8b7cbaa-9d92-11e8-8055-b49bdf3a538b.png)
- Foreign translation at installation and better translation files, during the installation process.
- Patoral Care : No more WhyCame, it's now Pastoral Care, a full personalisable parts, with a special role Pastoral care in the UserEditor
![capture d ecran 2018-08-11 a 18 17 54](https://user-images.githubusercontent.com/20263693/43993762-e50cb3b8-9d92-11e8-9348-f61243627b1a.png)
Now you can add more comments and work with different pastors :
![capture d ecran 2018-08-11 a 18 19 45](https://user-images.githubusercontent.com/20263693/43993779-2a7645a4-9d93-11e8-82e8-90724e43d65f.png)
![capture d ecran 2018-08-11 a 18 20 51](https://user-images.githubusercontent.com/20263693/43993788-530ec2f2-9d93-11e8-96fb-8cd4485f89e4.png)
You can see a filter
- It's possible to personalise the type of Pastoral care 
![capture d ecran 2018-08-11 a 18 21 32](https://user-images.githubusercontent.com/20263693/43993795-66223536-9d93-11e8-9901-456a055c50fe.png)
So you can follow a person as you want.
- GDPR + DPO
![capture d ecran 2018-08-11 a 18 23 15](https://user-images.githubusercontent.com/20263693/43993811-a62ce1bc-9d93-11e8-9856-8d8c21440031.png)
Now you can de-activate a person :
![capture d ecran 2018-08-11 a 18 17 54](https://user-images.githubusercontent.com/20263693/43993802-8d663ea8-9d93-11e8-85f6-3e2ca3d7809d.png)
- To manage the GDPR and DPO : bGDPR, sGdprDpoSigner, sGdprDpoSignerEmail and iGdprExpirationDate set duration of the datas persistence
- every part is protected in function of GDPR.
When you de-activate a person or a family, during the 3 first years, an administrator can reactivate them.
After only a dpo can delete or re-activate them.
Important : it's impossible for a dpo to delete a person or a family with activated donations.
- MaindashBoard user setting, so you can create a special profile like a full administrator.
- Dashboard improvement
- MailChimp role in SystemSettings + restriction
- SystemConfig refactor
- classification icons are now manage in real time
- Prompt to view the number of groups.
- Better render for printView
- Better toolbar management
- Spanish  support : 99% and German support : 98% .
- better support for localized numbers in foreign languages.
- 100% Support of belgium, France, Swiss, Austrelia  with the régions, States, provinces, cantons, Bundesland.
- better financial Report, everything is rewritten too.

Bugs correction
- A bug in ChuchMetaData is corrected.
- a bug with the anniversaries and birthdays event is now corrected.
- bug with localisation event in the Calendar.
- bug resolution when you change language to another.

Inner Beauty
- better optimisation of the js code and po code too, everything is now seperated.


Inner Coherence

See full changelog here:
---

## 4.3.1 (04/07/2018)
Functionality Improvements :
- The personView is better managed
- Look and feel with email/Dashboard.php

Bugs correction
- A bug in PersonEditor is corrected.

Inner Beauty

Inner Coherence

See full changelog here:

---

## 4.3.0 (01/07/2018)
new version 4.3.0
Functionality Improvements :

- Adding BingMap, OpenStreetMap to the CRM, because of the new GoogleMap prices.
  - with BingMap :
![capture d ecran 2018-07-01 a 09 26 08](https://user-images.githubusercontent.com/20263693/42132090-105a2868-7d11-11e8-90b5-4d382c5258ee.png)
  - With OpenStreet Map via Leaflet :
![capture d ecran 2018-07-01 a 09 25 23](https://user-images.githubusercontent.com/20263693/42132099-5336eec8-7d11-11e8-870d-85a872ad86fb.png)
- The OptionManager is rewritten.
- You can now add your icon for your map and manage theme without any iteraction with GoogleMap.
  - To change the icons 
   ![capture d ecran 2018-07-01 a 09 24 41](https://user-images.githubusercontent.com/20263693/42132109-8cf8ca46-7d11-11e8-88a4-2a73e7ed7f52.png)
  - The image Picker
![capture d ecran 2018-07-01 a 09 24 49](https://user-images.githubusercontent.com/20263693/42132110-93d6b4c2-7d11-11e8-9fad-fcddf0f3c4e8.png)
- So the sGMapIcons key in the preferences is no more usefull.
- The legend map has now the ability to check/unchek the icons in function of the classification.
  - With BingMap
![capture d ecran 2018-07-01 a 09 26 15](https://user-images.githubusercontent.com/20263693/42132085-ef12868c-7d10-11e8-95b0-9023d0871baa.png)
-  With : OpenStreetMap
![capture d ecran 2018-07-01 a 09 25 33](https://user-images.githubusercontent.com/20263693/42132094-2ec3aa2c-7d11-11e8-9ac0-905ebd49b569.png)
- The Banner is Menu.php is rewritten too.
![capture d ecran 2018-07-01 a 09 33 23](https://user-images.githubusercontent.com/20263693/42132115-d42f8b02-7d11-11e8-8e82-7a37f744d355.png)
![capture d ecran 2018-07-01 a 09 34 03](https://user-images.githubusercontent.com/20263693/42132121-03efb588-7d12-11e8-98bc-92081088ee3c.png)



- The personView too.

![capture d ecran 2018-07-01 a 09 35 15](https://user-images.githubusercontent.com/20263693/42132124-1c4000e8-7d12-11e8-80f0-7fe0ecca8026.png)

- introduction of debug level 0. So now the CRM will send as few as possible error message (in SLIM, Propel, and JS).

Bugs correction
- Bug correction with PersonEditor
- calendar side bar with the eye icon under windows 7.
- calendar exclusion is now corrected.
- A bug correction in the formCustomField is corrected
- Bug resolution with Event location
- A bug with the present option for the calendars is now solved.
- etc ...

Inner Beauty
- PersonCustomFieldsEditor.php and FamilyCustomFieldsEditor.php are now redesigned.
- OptionManager.php is rewritten too.
- Now the aSecurityType is now rewritten with a new function.
- popwindow upgrade in the MapUsingGoogle.
- new api for the maps


Inner Coherence

See full changelog here:
---

## 4.2.1 (23/06/2018)
new version 4.2.1 update
Functionality Improvements :

A bug correction with the localisation Event.

- Now an event can be located on MapUsingGoogle.
![capture d ecran 2018-06-14 a 20 39 16](https://user-images.githubusercontent.com/20263693/41431386-0edf6ed6-7013-11e8-929e-a30afeb586da.png)
- There's a new Event MenuItem : View Map
So you can see the events on the Map :
![capture d ecran 2018-06-14 a 20 35 14](https://user-images.githubusercontent.com/20263693/41431246-987ffc42-7012-11e8-9c62-6464ee43f401.png)

- New menu items : Personal Area + documents
![capture d ecran 2018-06-17 a 18 35 11](https://user-images.githubusercontent.com/20263693/41509998-371105ee-725d-11e8-9305-6abeeed327f9.png)
- displayCustomField is now in the OutputUtils EcclesiaCRM part.
- Now Group Specific properties for Person are available for a user and can be used as a formular.
- Now location events are present everywhere.

- The groupView has now a groupReport button.
- Group Specific are now translated.
- There's now Person Group properties : GroupPersonPropsFormEditor.php
- FamilyView and PersonView are now secured in function of bAdmin etc ....
- MapUsingGoogle in the info window, when you click the address you open Google Map.
![capture d ecran 2018-06-17 a 18 37 22](https://user-images.githubusercontent.com/20263693/41510011-893f2724-725d-11e8-9fb9-a447bb6de61f.png)


Bugs correction
- displayCustomField.
- Person PersonEditor update.
- etc ...

Inner Beauty
- PersonCustomFieldsEditor.php and FamilyCustomFieldsEditor.php are now redesigned.
- OptionManager.php is rewritten too.
- Now the aSecurityType is now rewritten with a new function.


Inner Coherence

See full changelog here:
---

## 4.2.0 (17/06/2018)
new version 4.2.0
Functionality Improvements :

- Now an event can be located on MapUsingGoogle.
![capture d ecran 2018-06-14 a 20 39 16](https://user-images.githubusercontent.com/20263693/41431386-0edf6ed6-7013-11e8-929e-a30afeb586da.png)
- There's a new Event MenuItem : View Map
So you can see the events on the Map :
![capture d ecran 2018-06-14 a 20 35 14](https://user-images.githubusercontent.com/20263693/41431246-987ffc42-7012-11e8-9c62-6464ee43f401.png)

- New menu items : Personal Area + documents
![capture d ecran 2018-06-17 a 18 35 11](https://user-images.githubusercontent.com/20263693/41509998-371105ee-725d-11e8-9305-6abeeed327f9.png)
- displayCustomField is now in the OutputUtils EcclesiaCRM part.
- Now Group Specific properties for Person are available for a user and can be used as a formular.
- Now location events are present everywhere.

- The groupView has now a groupReport button.
- Group Specific are now translated.
- There's now Person Group properties : GroupPersonPropsFormEditor.php
- FamilyView and PersonView are now secured in function of bAdmin etc ....
- MapUsingGoogle in the info window, when you click the address you open Google Map.
![capture d ecran 2018-06-17 a 18 37 22](https://user-images.githubusercontent.com/20263693/41510011-893f2724-725d-11e8-9fb9-a447bb6de61f.png)


Bugs correction
- displayCustomField.
- Person PersonEditor update.
- etc ...

Inner Beauty
- PersonCustomFieldsEditor.php and FamilyCustomFieldsEditor.php are now redesigned.
- OptionManager.php is rewritten too.
- Now the aSecurityType is now rewritten with a new function.


Inner Coherence

See full changelog here:
---

## 4.1.0 (04/06/2018)
new version 4.1.0
Functionality Improvements :

- There's now CKeditor templates for each users. So you can manage all your templates like you want.
![capture d ecran 2018-06-04 a 20 29 53](https://user-images.githubusercontent.com/20263693/40935004-056af7e6-6837-11e8-92d2-03dc80fa0d55.png)
![capture d ecran 2018-06-04 a 20 30 00](https://user-images.githubusercontent.com/20263693/40935028-1a852caa-6837-11e8-801c-8cda75f51abe.png)
![capture d ecran 2018-06-04 a 20 30 13](https://user-images.githubusercontent.com/20263693/40935047-254210a4-6837-11e8-9baa-c73bea2605c7.png)

- The MenuBar is completely rewritten, now the selected menu items are stored and kept opened.
![capture d ecran 2018-06-04 a 20 29 23](https://user-images.githubusercontent.com/20263693/40935097-4badbcca-6837-11e8-9628-ced9786136db.png)

- The Groupview is now updated for GroupManagerPerson, a user can be assign to a group to manage member (add/delete), so there's two different method to manage group (Global or local).
- The GroupView is now fully written in js, no more reload.
![capture d ecran 2018-06-04 a 20 33 50](https://user-images.githubusercontent.com/20263693/40935132-6b1bd3d0-6837-11e8-99cd-406e997b2c0e.png)
- specific properties are reintroduce in the GroupView, with all the goodies (real date, text field, date field, etc ...).
![capture d ecran 2018-06-04 a 20 34 27](https://user-images.githubusercontent.com/20263693/40935348-1edd9200-6838-11e8-967a-a948afb2aed2.png)
- Now you can see the specific group properties for each member of the group in the person profile.
![capture d ecran 2018-06-04 a 21 27 20](https://user-images.githubusercontent.com/20263693/40937240-3f45d808-683e-11e8-994e-00e433450652.png)
- The Menu.php display correctly the family and the time in the right format.

- For some Notification email, the confirmSigner is the current user.
- DataTables are now sortable by dates with foreign languages.
- Some constants upgrade sTimeEnglish etc ...
- Now you can email directly a person or a family.
- The userEditor is updated, only user with email address can have a user account.
- Header-function is now full propelled.
- Code optimisation for speed.
- PersonEditor is rewritten in many direction.
- etc ....


Bugs correction
- PersonView minor bug correction.
- Boostrap bug correction with bootbox under Edge and FireFox (shift right of 15px).
- PersonView bug resolution in the dataTable.
- Bug resolution in the CalDAVPDO code.
- PDF_GroupDirectory.php bug correction.
- Bug correction in  SystemSettings with system sidebar.
- etc ...

Inner Beauty
- New GroupManagerPerson to manage only one group.
- New CKEditorTemplates and CKEditorTemplatesQuery to manage the templates.
- GroupMaster has now a real primary key.


Inner Coherence

See full changelog here:

---

## 4.0.0 (17/05/2018)
Why a 4.0 version ???
Events/Calendars are real a powerfull new features, 6 000 lines of code have been written.

The huge update is : CalDAV (ie : synchronisation with calendar).

**Be carefull, the CalDAV server isn't a MIT licence the WebDAV server too.**

Functionality Improvements :

- There's a new flag : sTimeZoneSet in the system settings, you've have to set it correctly for the CalDAV Server.
- Calendar/events are now completely rewritten, EcclesiaCRM is now a CalDav full server/client. I use sabre technology.
- Everything is now rewritten with a brand new design.
![capture d ecran 2018-05-17 a 10 18 22](https://user-images.githubusercontent.com/20263693/40165231-baa878de-59bb-11e8-9579-0e96e97f562c.png)

- Brand new calendar sidebar with flexible contents.
- EcclesiaCRM now manage Personal Calendar, Group Calendar, Share calendar.
![capture d ecran 2018-05-17 a 10 18 30](https://user-images.githubusercontent.com/20263693/40165255-cbbb6e24-59bb-11e8-8631-46db927f071b.png)
![capture d ecran 2018-05-17 a 10 18 34](https://user-images.githubusercontent.com/20263693/40165261-cf658988-59bb-11e8-977d-760f10242fba.png)
- The calendar can be shared to other persons in the CRM (with Read-Write or only Read access).
- Each user can manage his calendar colors or names, in the case of share calendar or not.
![capture d ecran 2018-05-17 a 10 21 15](https://user-images.githubusercontent.com/20263693/40165352-114e05e6-59bc-11e8-86e5-08ed19063c4a.png)
- The calendar can be shared inside/outside (with Read-Write access): full vcalendar support.
![capture d ecran 2018-05-17 a 10 20 41](https://user-images.githubusercontent.com/20263693/40165371-176d8ba4-59bc-11e8-8ec5-7f77ca0bc463.png)
- You can include/Exclude a calendar from the sidebar
![capture d ecran 2018-05-17 a 10 20 53](https://user-images.githubusercontent.com/20263693/40165462-51df568c-59bc-11e8-80ff-9b679e681ff5.png)

- To change the name of the calendar, click the name of it
![capture d ecran 2018-05-17 a 10 36 14](https://user-images.githubusercontent.com/20263693/40166192-44a3386a-59be-11e8-860a-c4604edf03cd.png)

- You can create a calendar outside in Calendar Mac or in Outlook with the right connector, it will appear in the CRM.

- You've got real event withs the vcalendar vevent standard support.
- A group Manager can create a group and the associated calendar and manage the attendees to the calendar.
- The reccurrence events are now manage with the vevent standard fully compatible with external application
- The drag and drop is now manage correctly with the new definition Vevent with reccurence or not.
- There's now nice icon in each events, so you can easely see if an event is a personal/group/share event.
- Everything is designed to manage user calendars, group calendars, share calendars as simple as possible.
- A group has automatically a calendar and you can add user to the group and to the calendar too, with read/write access or only read access inside the CRM or outside.
- You can visualize your calendars under Calendar, thunderBird, etc ...
Under the CRM :
![capture d ecran 2018-05-17 a 10 18 22](https://user-images.githubusercontent.com/20263693/40165231-baa878de-59bb-11e8-9579-0e96e97f562c.png)

Under the Calendar (you can all the user calendars automatically loaded) :
![capture d ecran 2018-05-17 a 10 26 45](https://user-images.githubusercontent.com/20263693/40165733-0827253c-59bd-11e8-9bfc-ebac0831762e.png)

Under iPhone
- The main point you can create a calendar outside of the CRM.
- It's possible to add real attendees to a sunday group and to a normal group too.
- ListEvent is completely rewritten with propel code and you can add a user with the new js/api directly no more EventEditor.php but the new EventEditor.js code.
- Checkin.php is rewritten too, with the EventEditor.js. You can now add an event and make the attendance directly.
![capture d ecran 2018-05-17 a 10 31 19](https://user-images.githubusercontent.com/20263693/40165905-7f202242-59bd-11e8-84bf-22cf6662776d.png)
- The UserEditor.php is now deleted, everything is done with the new js code. For example in the ListEvent.php :
![capture d ecran 2018-05-17 a 10 32 02](https://user-images.githubusercontent.com/20263693/40165963-a1a31dd8-59bd-11e8-8ecd-96bcd55b2d40.png)
- In the calendar, when you chose the Month/Week ... view, the CRM will remind your last choice the next time.
- A new button in the Calendar View : Actualize (this give you the choice to synchronize with other Client).
![capture d ecran 2018-05-17 a 10 33 31](https://user-images.githubusercontent.com/20263693/40166017-c86fb624-59bd-11e8-867d-08116c434edd.png)

- No more bAddEvent flag, everyone can have a calendar : personal etc...
- A user who belongs to a sunday group, can create an attendance for his sunday, make attendees, but not modify the event in the calendar View (security), one time it's created, it's impossible to modifiy it.
- You've to set the new localisation systemconfig : sTimeZoneSet
- Last when you upgrade .... all the events are translated to the new format. The groups are now translated to calendar groups.
- Nothing is lost ;-).


- Attendees export in CSV and PDF improvements.
- Two new flags for the user settings : bExportSundaySchoolCSV, bExportSundaySchoolPDF (So a person can manage the export of the sundayschool attendees, phototobook) and export CSV attendees.
- New export attendees for sunday school, it's possible now to export all the attendees classes.
![capture d ecran 2018-05-14 a 18 24 54](https://user-images.githubusercontent.com/20263693/40143636-4710f612-595c-11e8-981c-675f4d997e80.png)
- New stats in the sunday school attendees.


- CSP update for the youtube
- The sidebar will collapse now by default and each user has the ability to chose or not this new feature.
- There's two new flags too : bSidebarExpandOnHover, bSidebarCollapse, so a user can manage the behaviour of the sidebar menu.
- Some upgrade in SettingsIndividual.php
![capture d ecran 2018-05-17 a 10 35 04](https://user-images.githubusercontent.com/20263693/40166090-fe17c1ae-59bd-11e8-88c5-37a3b67ff75f.png)


- sCSVExportDelemiter, sCSVExportCharset update : this two parameters are now in the user settings and no more in System settings, each user can manage the CSV export flags.
![capture d ecran 2018-05-17 a 10 35 04](https://user-images.githubusercontent.com/20263693/40166090-fe17c1ae-59bd-11e8-88c5-37a3b67ff75f.png)

- IE 11 is now fully supported with the CRM. So Chrome, Safari, FireFox, Microsoft Edge and IE 11 are fully supported.

- etc .......... etc ......

Bugs correction
- ListEvent, 
- MenuEventCount.
- Login.
- One in the user profiles with the semi colon char.
- Directory export with UT8 export format is now corrected.
- etc ...

Inner Beauty

- Sabre is now inside.
- new api calendarV2/eventsV2.
- A huge schema update for calendars.
- brand new events_event etc ....
- New classes for attendees for the sundayschool.
- Many CSP updates.
- So many Propel classes.
- to many things ...


Inner Coherence

See full changelog here:
---

## 3.6.2 (24/04/2018)
Functionality Improvements :

- The listEvent has now a filter by month.
- A better translation of the mail message when a user share a document to another.
![capture d ecran 2018-04-24 a 22 11 58](https://user-images.githubusercontent.com/20263693/39211704-c31368dc-480c-11e8-9086-bdf5fc35e73c.png)
- The GetText.php is bootstraped.
![capture d ecran 2018-04-24 a 22 12 16](https://user-images.githubusercontent.com/20263693/39211664-a1c8992c-480c-11e8-9402-f30cb060614a.png)
- many files are now rewritten to avoid the use of the session variables.
- Now we've a new textfield to add Person/family/Group to an event : EditEventAttendees.php
![capture d ecran 2018-04-24 a 22 11 06](https://user-images.githubusercontent.com/20263693/39211609-77811ebe-480c-11e8-9995-67c4dcc8c4cf.png)
- Some translation update too.

Bugs correction
- EventEditor.php : a visual problem is corrected.
- PersonEditor.php : a person can now edit the member of his family again.
- etc ...


Inner Beauty

The files are completely rewritten in ORM :
- EventEditor.php
- GetText.php
- ListEvents.php
- ReportList.php


Inner Coherence


See full changelog here:
---

## 3.6.1 (16/04/2018)
new version 3.6.1
Functionality Improvements :

- None

Bugs correction
- With webdav structure in the case you delete a document, when you change the name of a folder it's reflected in the CRM.
- A bug correction for NoteEditor when you cancel the creation of the document.
- A bug correction which could crash the code in NoteEditor.
- etc ...


Inner Beauty

- None


Inner Coherence


See full changelog here:

---

## 3.6.0 (16/04/2018)
Functionality Improvements : SHARE-DOCUMENT-TIMELINE

- Now we've a new concept : shared document time-Line 
![capture d ecran 2018-04-16 a 00 51 35](https://user-images.githubusercontent.com/20263693/38784326-7b5919a4-4110-11e8-938f-ec2309a26f87.png)
- A document is now shareable (to a group, family, persons).
![capture d ecran 2018-04-16 a 00 51 44](https://user-images.githubusercontent.com/20263693/38784328-85cb816a-4110-11e8-81ab-37b883a1c866.png)
- A notification is send to the users when a document is shared.
- You've got rights for each of your documents.
![capture d ecran 2018-04-16 a 00 51 52](https://user-images.githubusercontent.com/20263693/38784329-8ccf0f86-4110-11e8-81f6-9eb578623fda.png)
![capture d ecran 2018-04-16 a 00 52 07](https://user-images.githubusercontent.com/20263693/38784332-98ba8870-4110-11e8-91ad-aab0debadee3.png)
- A document can't be modified if it is edited by another user.
![capture d ecran 2018-04-16 a 00 54 39](https://user-images.githubusercontent.com/20263693/38784348-ce44b5e2-4110-11e8-9621-1fce52678bab.png)
- the Timeline has now a filter to view only the video, file or normal document.
![capture d ecran 2018-04-16 a 00 46 39](https://user-images.githubusercontent.com/20263693/38784309-33db2a18-4110-11e8-8425-92c02e1935c2.png)

- A Document has now a title.
![capture d ecran 2018-04-16 a 00 55 45](https://user-images.githubusercontent.com/20263693/38784356-efc349c2-4110-11e8-9112-3d902b4113bb.png)

- a new api : sharedocument

Bugs correction
- With webdav structure in the case you delete a document.
- Checkin.php will now work correctly when there's no event in the current week.
- etc ...


Inner Beauty

- new propel schema and new DB too.


Inner Coherence


See full changelog here:

---

## 3.5.0 (04/04/2018)
Functionality Improvements :

- Autopayments and pledges for single user is now supported.
![capture d ecran 2018-04-04 a 22 20 54](https://user-images.githubusercontent.com/20263693/38332532-81961f14-3856-11e8-92f5-8407dd9f64b9.png)
- EcclesiaCRM is now a WebDAV server.
![capture d ecran 2018-04-04 a 22 12 04](https://user-images.githubusercontent.com/20263693/38332253-d79a0e4e-3855-11e8-9371-95e57b9ebff9.png)
- Full support of WebDav for any user.
- The user now have an internal home folder to store files etc ...
![capture d ecran 2018-04-04 a 22 17 20](https://user-images.githubusercontent.com/20263693/38332316-fbae5d6c-3855-11e8-8197-fc9126e922b4.png)
- You can now connect your home folder through the windows or mac or KDE to your account and put files in your home folder with webdav protocol.
- You can add files, like movies, mp3, pDF, png, jpg files and visualise them in your own account too.
![capture d ecran 2018-04-04 a 18 51 36](https://user-images.githubusercontent.com/20263693/38332344-0fcc7856-3856-11e8-9c62-33ad49e313e8.png)
![capture d ecran 2018-04-03 a 21 17 31](https://user-images.githubusercontent.com/20263693/38332363-18138806-3856-11e8-8df5-3fd7af589b70.png)
- the note manager is updated too to support webDAV and files too, when you move a file in the share home folder, you'll get the modification in the notes.
- New notification for update center.
- The cartview is fully boostraped
- the CartToFamily is fully boostraped
- The calendar is now responsive too.


Bugs correction
- bug corrections with sunday school attendees.
- bug correction in the sundayschoolView charts.
- a bug is corrected in the email notification center.
- etc ...


Inner Beauty

- new propel schema and new DB too.


Inner Coherence


See full changelog here:

---

## 3.4.4 (07/03/2018)
new version 3.4.4
Functionality Improvements
- user profile  now incorporate the skin.
- deposit/pledges are refactored and are working correctly for the calulations amounts.
- the deposit export is now translated and is nicer too.
- better pdf export for all the report files.
- family pdf verification is redesigned.
- verify-family-info.php is translated for role, groups, TRUE …
- FamilyView has now a new color codification to make a difference with the PersonView.

Bugs correction

- etc ...


Inner Beauty

- new api userprofile, new propel schema and new DB too.


Inner Coherence


See full changelog here:
---

## 3.4.3 (02/03/2018)
Functionality Improvements
- new api : userprofile.
![capture d ecran 2018-04-04 a 22 25 16](https://user-images.githubusercontent.com/20263693/38332825-30a38b5e-3857-11e8-8c75-bc43cd668b30.png)
![capture d ecran 2018-04-04 a 22 25 24](https://user-images.githubusercontent.com/20263693/38332829-33b0ebc0-3857-11e8-8654-2a472bfb0e7f.png)
- now we can manage user with profiles.
![capture d ecran 2018-04-04 a 22 26 35](https://user-images.githubusercontent.com/20263693/38332947-7efcfdee-3857-11e8-9430-d0ca81714001.png)

Bugs correction

- etc ...


Inner Beauty

- new api userprofile, new propel schema and new DB too.


Inner Coherence


See full changelog here:
---

## 3.4.2 (01/03/2018)
Functionality Improvements
- now you can export the attendees for the sunday school as pdf file too.
- the PledgeEditor has a bug correction.
- AutoPaymentEditor is boostraped and propeled.

Bugs correction

- etc ...


Inner Beauty


Inner Coherence


See full changelog here:

---

## 3.4.1 (26/02/2018)
Functionality Improvements
- an update of the checkin.php The gender is added.
- some terms are now translated too.
- The ListEvent.php reflect now the real count of the person who should be called at the end of the checkout.
- When you delete an event of a repeat event, it will keep the link between events.
- optimisation of the calendar rendering, now only the event of the current month are loaded and rendered.
- resize and move events have now a new dialog box to move one or all the events.

Bugs correction

- etc ...


Inner Beauty


Inner Coherence


See full changelog here:

---

## 3.4.0 (18/02/2018)
Functionality Improvements
- This update is a huge update (event scheduling + GDPR : security update + refactor of the SelectList).
- All the CRM is refactor for security to be GDPR ready with European laws (GroupView, SundaySchoolDashboard, WhyCameEditor, FamiliyView, PersonView, NoteEditor, etc ...).
- Security upgrade (now the workspace is restricted), a user can only see his group and his family ....
- There is two new constant in the UserEditor: ShowCart and ShowMap (DB upgrade).
- The cart and the map are now showable or not (it's a user configuration).
- Now a normal user has his profile page as main dashboard.
- A normal user can see his personal private  datas and family and can't consult the other personal information.
- In the SundaySchoolView there's an export button to list all the events in a range.
- Now with the Calendar, you can delete, make attendance, add attendees directly.
- The EditUser window is refactor too.
- The selectList is rebuild too in bootstrap and work now correctly in foreign language. The code is optimized for speed too.
- Now you can repeat an event in the calendar, drag and drop will work with repeated events.
- etc ...


Bugs correction

- Constraint with events attendees are now real, everything works now with sql constraint for the events event_attendees, etc ...
- etc ...


Inner Beauty

- now I use real DB constraint to maintain correctly the DB.
- Some parts of the schema is update with real foreign key.

Inner Coherence


See full changelog here: 

---

## 3.3.0 (15/04/2018)
3.3.0
Functionality Improvements
- The event are now editable in the Calendar directly.
- All the reports are now translated for the Deposit.
- There is now a note tab in the FamilyView.
- New note type : video, so you can include Youtube video in your notes with iframes.
- The report Directory is translated too.
- Search field for payement is now working correctly.
- Every DataTable in FamilyView are full responsive with + button, the Automatic Payments and the Pledges table and the are in Javascript rewritten, no more reload.
- New api pledges, donationfunds, payments.
- The Deposit is now refactored too.
- Now the Checkin.php has the possibility to add a note at the end and you can check everybody with one button.
- etc ...


Bugs correction

- Bug systemConfig now corrected.
- Bug in deposit is translated.
- Bug in the report corrected.
- Bug in the Event creator corrected.
- etc ...


Inner Beauty
- Many new propel update
- Many new db upgrade too.

Inner Coherence
- The code is refactor in the api to be the same in every parts


See full changelog here:

---

## 3.2.1 (01/02/2018)
new version 3.2.1<br>Functionality Improvements<br>- Support of Text Message.<br>- add the date to the event of Sundayschool.<br>- add the group in the title of the sundayschool event creator.<br>- Solve a problem with lands without any state new SystemConfig local variable.<br>- Report are now right translated and are working.<br>- Deposit are working too, everything is now fully translated.<br>- New variable in the localisation part to add the currency.<br>- Search in deposit is now working right.<br>- etc ...<br><br><br>Bugs correction<br><br>- Bug with the sundayschool report solved.<br>- Bug in the Cartview SelectList and PersonView.<br>- Family bug resolution with the deposit<br>- etc ...<br><br><br>Inner Beauty<br><br>Inner Coherence<br><br><br>See full changelog here:
---

## 3.2.0 (30/01/2018)
new version 3.2.0<br>Functionality Improvements<br>- New button in the SundayClassView for making attendance.<br>- New Checkin.php to check the attendes as easy as possible.<br>- Brand new calendar add event with attendees with dates etc ...<br>- Cosmetical Optimisation of the Event window in the calendar<br>- No more Add event menu item everything is inside the calendar.<br>- Cart view icon for FamilyView, PersonView and PersonView is now really fully live update and don't anymore the old way. Now we use brodacaster/listener system.<br>- You can now add the teacher or the Student to the cart separately from the SundaySchollView.<br>- New management of the events ListEvent,EditEventAttendees, EventEditor, <br>- New ListEvents.php (you have the real attendees). There's now a checkout button.<br>- New ListEvents.php statistics mean and sum for the events with attendees.<br>- ListEvent is brand new too the add event now goes to the calendar.<br>- Better management of the Attendees on the Main dashboard, when there no attendees the badge is no more visible.<br>- New Checkin.php you've directly the current user in the case of the you want to checkout.<br>- Search fields in EditEventAttendees, ListEvents, EditEventAttendees.<br>- EditEventAttendees has now tel link for the phone numbers.<br>- EditEventAttendees has now a lots of improvement responsive datable, add group select field and a add member select fiel too.<br>- EditEventAttendees has a checkout button too.<br>- UI update for EditEventAttendees, GroupList, ListEvents are now unified.<br>- No more CartToEvent everything is in JS, it's more flexible and easy to use.<br>- New buttons for the CKeditor for the Event Window in calendar<br>- translated EventEditor.<br>- New CKEditor function.<br>- New CRM variables.<br>- etc ... etc ...<br><br><br>Bugs correction<br><br>- Bug with the sundayschool report solved.<br>- bug with Attendees resolution (data constraint resolution).<br>- bug with register menu resolution.<br>- bug on the main dashboard resolution with Attendees, it's now reflected the good attendees.<br>- Bug with the menu Events resolution.<br>- Bug sundayschool report resolution.<br>- Bug in the help menu on smartphone.<br>- Bug correction for events in ListEvents.<br>- Bug correction in the checkin.php code.<br>- Bug in PersonEditor corrected.<br>- Bug with sunday school sub menu.<br>- Bug with the chart in the sundayschoolview corrected.<br>- etc ...<br><br><br>Inner Beauty<br>- optimisation of the JS code, goal : speed<br>- new way to manage the events with trigger no more loop to manage everything in the CRMSJSOM.js<br>- the JS code is split and I use trigger to post the update, so the code is real faster. <br>- The cart is completely rewritten, the code is real faster.<br><br>Inner Coherence<br><br><br>See full changelog here:
---

## 3.1.0 (15/01/2018)
        Happy new year 2018 !!!
<br><br><br>Functionality Improvements<br>- sTimeEnglish for setting the time in event format.<br>- iPersonAddressStyle to support the export address in foreign format.<br>- Update sunday school export for long addresses.<br>- Update PhotoBook for nice output too.<br>- The custom search is now updated with like sql statement, so You can search in a custom field with Capital or not letter, or the beginning of the text you want to search.<br>- Timeline localisation.<br>- New Query Menu with classifications.<br>- Localisation of the tooltip in system config.<br>- add the roles student/teacher when a group is changed to a sunday group.<br>- Many comestical updates (buttons, dropdown etc ...).<br>- etc ...<br><br><br>Bugs<br><br>- send bug report.<br>- Event management corrected for french date and everything is written in ORM.<br>- many bug corrections inside ...<br><br>Inner Beauty<br><br>- New propel schema update.<br>- Add Event changes to get the code to work with php 7.1 + ORM (EventAttendQuery, EventNames.php, EditEventTypes.php).<br>- PersonEditor is now in full ORM code.<br>- SundaySchoolClassView translation problem + ORM<br>- So many new code in ORM.<br>- Update some sql code.<br>- sql update<br><br>Inner Coherence<br><br>- A propel schema is now good chosen for the future development.<br><br>See full changelog here:
---

## 3.0.0 (09/01/2018)
       Happy new year 2018 !!!

Functionality Improvements<br>- New Event Menu item which is a fusion of the calendar and the Events menu.<br>- New Sunday school menu improvement (now you can personnalise the menu in the group editor).<br>- GroupeEditor is now refactor too, with nice menu edition.<br>- Added button remove in the PersonView/GroupView too.<br>- The Popup search are now improved ...<br>- Refactor of the GroupView (the datatable, buttons etc ... follow the guide lines).<br>- PersonView is now fully translated  (the datatable, buttons etc ... follow the guide lines).<br>- Add a new delete cart and CRM persons menuitem.<br>- A new query menu item :  to find all the persons who aren't in a group before the year you mentioned).<br>- A new query menu item :  to find all the persons who are in a group.<br>- Issue submission is now improved and can be used several times without reloading the page.<br>- the issue are now send to the new git repo.<br>- The help books is now available (but in construction).<br>- The event are now modernise and work in foreign languages.<br>- Many new files are now in ORM.<br>- Calendar event filters are reintroduced.<br>- New sunday school export to pdf.<br>- etc ... <br><br><br>Bugs<br><br>- send bug report.<br>- many bug corrections inside.<br><br>Inner Beauty<br><br>- New propel schema update.<br>- and so many new things inside.<br>- No more PropertyAssign.php PropertyUnassign.php.<br>- brand new properties api.<br>- Refactor of the code more and more coherence in the code and in the propel code too.<br>- many things are now in Propel code.<br><br>Inner Coherence<br><br>- A propel schema is now good chosen for the future development.<br><br>See full changelog here:
---

## 2.10.5 (31/12/2017)
# Happy new year!!!

## Functionality Improvements
- separate version number from the name of the CRM
- Menu.php date update localisation
- Add new buttons for the time line
- PrintView image to big is now solved
- Now the report issue on github is operational too with ecclesiacrm.
- Better installer and No more bug with cart status at the installation of ecclesiacrm.
- Automatic logout
- when you try to go back to the website after a logout you're redirected to the logout.
- new website in progress...

## Bugs
- Fix the upgrade from 2.10.3 to 2.10.5 and delete the ChurchCRM code.
- the horrible message at the installation disappear.

## Inner Beauty
- add version number and version name in propel tool
- Support for states per country (US &amp; CA for now)
- New class OutputUtils FormatDate, FormatBirthDate, BirthDate

See full changelog here:
---

## 2.10.4 (28/12/2017)
        Merry Christmas everyone!!!


Functionality Improvements
- Locale Text updates
- Person timeline now shows when a member was added/removed from a group
- Event was added successfully.
- Better improvement with Firefox and JS
- Delete a member of a group now work perfectly 
- the Delete button is now aligned in the PersonEditor
- The time line is now well translated
- Now the project has a new update center
- A new website too
- The project is now called : EcclesiaCRM
- new web site in progress
- new help ballon with the possibility
- I extend the search field I've develop in ChurchCRM : it has the work phone too.
- Now the student/teacher are now everywhere translated
- etc ...

Bugs

- Fixed Delete Person Group Bug
- Fixed Invalid date when Entering new Family
- Fixed Can't delete Family
- Add Event via Calendar corrected
- Add All People to Cart is fully functional in JS code only
- CKEditor missing css


Inner Beauty

- APIs to Support Country List
- Support for states per country (US &amp; CA for now)
- API Security updates

See full changelog here:

---

## 2.10.3 (26/12/2017)
essai