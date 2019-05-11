- Le RGPD est un nécessité en Europe.
- Pour fonctionner correctement, il faut une personne attitrée dans l'association qui sera au fait des évolutions de la législation dispensée par la CNIL par exemple.

Ecclesia**CRM** a été pensé et construit autour de ce principe, tant au niveau de sa gestion via un DPO que de sa structure interne.

## Au niveau sécurité

L'ensemble du CRM est construit autour de la notion d'API (via SLIM) et fonctionne via un jeton (token) à l'activation, une fois la déconnexion de l'utilisateur faite, le jeton est invalide et donc l'accès au données également.

Chaque fichier est propre à chaque utilisateur et est stocké dans un dossier qui possède un UUID.

## Au niveau Suivi

Chaque action sur chaque utilisateur est enregistrée, afin de tracer au mieux toute modification faite sur un compte par un superviseur ou un administrateur.
  
## Au niveau droits Utilisateurs

  Chaque utilisateur a des droits précis, à mettre en place conjointement avec le DPO. Ceci fait un tulisateur du CRM ne pourra visualiser que ce qui lui est octroyé comme rôle.
  
  Les extractions CSV ou PDF sont possibles uniquement via ce rôle.
  
## Utilisateur désactivé

- Un utilisateur désactivé, n'est plus visible par aucun utilisateur, mis à part un administrateur pendant une période légale de deux années.
- Il n'est plus accessible via aucun requête, recherche, extraction, carte, etc ...
- Seul un administrateur global peut encore le consulter dans la liste des personnes désactivées et ceci pendant une période de deux années.
- Au bout de deux années, l'utilisateur passera dans la gestion pure RGPD, seul le DPO pourra encore le visualiser.
- Il aura alors la liberté de pouvoir le réactiver ou tout simplement de le supprimer définitivement du CRM.

## Limitation d'un DPO

Un utilisateur ne pourra être réellement supprimé que si ses dons sont transférés à une autre personne ou une autre famille.

La partie fiscale l'emportant sur la partie RGPD.

## MailChimp

Quand la fonctionnalité RGPD est activée, toutes listes de diffusion MailChimp sont automatiquement RPGD et ne peuvent légalement pas être utilisée par MailChimp a des fins commerciales.