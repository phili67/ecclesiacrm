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
