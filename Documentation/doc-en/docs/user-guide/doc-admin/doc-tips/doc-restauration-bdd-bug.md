Here you can set the integration settings for other apps 

1. In some cases after the data recovery one may have a note about `email_list` or `email_count`, it is recommend to delete the two tables entries in the data base.

2. In the case of `Mail->MailChimp->Tableau de bord->Email en doublon`, if there is an issue to extract the duplicates: 
  
    - This bug happens after the transfer of a data base from one server to another 
   
    - The solution is to apply this patch on the data base : 


```
-- drop view in case of existence
DROP VIEW IF EXISTS `email_list`;
DROP VIEW IF EXISTS  `email_count`;

-- method for dup emails
CREATE VIEW email_list AS
    SELECT fam_Email AS email, 'family' AS type, fam_id AS id FROM family_fam WHERE fam_email IS NOT NULL AND fam_email != ''
    UNION
    SELECT per_email AS email, 'person_home' AS type, per_id AS id FROM person_per WHERE per_email IS NOT NULL AND per_email != ''
    UNION
    SELECT per_WorkEmail AS email, 'person_work' AS type, per_id AS id FROM person_per WHERE per_WorkEmail IS NOT NULL AND per_WorkEmail != '';

CREATE VIEW email_count AS
    SELECT email, COUNT(*) AS total FROM email_list group by email;
```
