# <center><big>Import de données & Ecclesia**CRM** </big></center>

Vous pourrez importer des données via un fichier CSV avec le séparateur "," ou ";". Chaque ligne du fichier doit contenir les informations pour une personne.

La fonctionnalité "Importer un fichier CSV" se trouve dans le menu administrateur

Vous trouverez ici vous permettant de manière guidée d'importer les données de manière automatisée. Si vous sélectionnez le genre correctement vous permettra de créer automatiquement les familles selon les adresses.

Voici le informations de base

- Titre (pour la création de famille)
- Prénom
- deuxième prénom
- Le nom
- le suffixe
- le genre
- Enveloppe de don
- Adresse1
- Adresse2
- Ville
- Etat
- Code postal
- Pays
- Le téléphone personnel
- Le téléphone professionel
- Le téléphone portable
- email
- Travail/Autre téléphone
- la date de naissance
- La date d'adhésion à l'église
- La date de mariage
- des champs personnels pour la personne
- des champs personnels pour la famille


Les dates peuvent être formatées sous la forme YYYY-MM-DD, MM-DD-YYYY, or DD-MM-YYYY. Les séparateurs de date (un tiret, un slash, etc.) ou le blanc ne peut fonctionner.

It is best to import the data first, before you invest time into tweaking the database. This way you can use the feature of the import screen which deletes all of the Person and Family records (see image below) so you can try importing again. It is usually easier to do most of the necessary clean-up in your data file rather than navigating the ChurchCRM interface to make corrections.

Import clear data

Once you are satisfied that the import has done what it can, you can make corrections using the ChurchCRM pages. You can move someone to a different family by editing the Person record. You can also specify family roles such as “child”, which helps ChurchCRM identify the adults for address labels.

Generating family records

ChurchCRM can automatically generate family records for the imported person records. Two methods are offered to determine if two (or more) persons belong to the same family

They share the same Last Name and Address1 field values
They share values of a selected Custom Family Field
Select family record mode

The first option is a simple heuristic to detect traditional families. The second option allows the import of member directories with pre-assigned family identifiers and allows for families where not all members share the same last name. Switch on family record generation by ticking the checkbox in front of the dropdown list shown in the figure above, then select either "Make Family records based on last name and address" (default) or one of listed custom family fields below.

Member classification

Another area which may require attention is the classification field, which keeps track of which people are members. This information becomes important when ChurchCRM is used to generate the list of voting members. By creating separate import files by membership type (member, guest, regular attender, etc.), you can assign a classification during the import process that will apply to each person in that file. Example: Create an import file that contains only “Members” so the classification of “Member” can be assigned during the import process. This way each person in that file will be classified as a “Member” in ChurchCRM. Then, import another file that contains only “Guests” so the classification of “Guest” can be assigned during the import process…and so on.

Note: If you are running the import on shared hosting then you may not have write access to the default area needed for import and the import script will show a file with 0 rows and not import anything. In this case, edit lines 139 and 316 of the CSVImport.php file so that they point to a writable location, e.g. $system_temp = "../tmp";