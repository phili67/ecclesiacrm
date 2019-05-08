Ici vous pourrez régler les paramètres d'intégration avec d'autres applications

1. Dans certain cas à la restauration d'une base de données, vous pourriez avoir un message concernant : `email_list` ou `email_list`, il faut dans ce cas supprimer les deux tables dans la sauvegarde

2. Dans le cas de `Mail->MailChimp->Tableau de bord->Email en doublon` si vous rencontrez un problème pour extraire les doublons

    - Ce bug apparait, suite au transfert d'une base de données d'un serveur à un autre.
    
    - La solution est d'appliquer sur la base de données ce patch :


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
