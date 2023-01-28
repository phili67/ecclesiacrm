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

---
Route | Method | function | Description
------|--------|----------|------------
`/allowRegistration` | POST | KiosksController::class . ':allowDeviceRegistration' | Allow a Kiosk registration

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/reloadKiosk` | POST | KiosksController::class . ':reloadKiosk' | Reload kiosk for kioskId

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/identifyKiosk` | POST | KiosksController::class . ':identifyKiosk' | Identify Kiosk by id

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/acceptKiosk` | POST | KiosksController::class . ':acceptKiosk' | Accept Kiosk by id

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}/setAssignment` | POST | KiosksController::class . ':setKioskAssignment' | Set Kiosk assignement

---
Route | Method | function | Description
------|--------|----------|------------
`/{kioskId:[0-9]+}` | DELETE | KiosksController::class . ':deleteKiosk' | Delete kiosk by id

---
## API "mailchimp"

   in route : "/api/routes/mailchimp.php"

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
