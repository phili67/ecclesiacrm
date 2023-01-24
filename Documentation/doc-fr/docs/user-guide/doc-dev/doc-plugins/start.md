# Pour bien démarrer

## Pour générer l'architecture d'un plugin

Une commande shell a été développé pour faciliter le travail : **createPluginArch.sh**


**Attention**

- les routes pour chaque plugins sont prévues et sont incluses dans la gestion complète des routes dans **api/plgnapi.php** (voir plus bas) et sont prévues pour être géré via contrôleurs dans l'arberscence **core/APIControllers**).
- pour les vues des routes sont préétablies aussi **v2/routes/v2route.php** (elles sont liés éventuellement à vos propres contrôleurs **core/VIEWControllers**).
- ce mécanisme garantie un maximum de sécurité.
- il est impératif de suivre le fait que chaque plugin doit avoir une signature qui est validé par le crm (voir pour cela la documentation plugin).

**Pour créer un plugin**

```
bash createPluginArch.sh *NameOfPlugin*
```

Ce script va créer **NameOfPlugin** dans le répertoire **Plugins** du répertoire **src**.

1\. L'architecture est de la forme

```
api/                // gestion api interne
    plgnapi.php     // on doit créer ses propres routes ici
    (géré par le CRM directement)
core/ // ici on peut gérer tous les modèles
    APIControllers  // pour définir le controleur appelé dans plgnapi.php).
    model           // exemple pour de l'orm propel
    VIEWControllers // pour les vues de la partie v2/routes
    ...
    //Par exemple
    Service
    views
ident/ // pour un accès à une api externe (facultatif)
    routes/         // appelé par le crm directement
    templates/      // appelé par la partie route
locale/
    js/             // code de traduction js
    textdomain/     // pour les traduction gettext du code php
    index.html
mysql/ // mise en place des fichiers mysql
    index.html      // fichier de protection
    Install.sql     // script sql pour créer la base de données
                    // appelé par le gestionnaire de plugin
    Uninstall.sql   // Pour désinstaller le plugin charge à vous de ne rien oublier
    upgrade.json    // cette partie permet de gérer les upgrades (en cours de développement)
skin/
    css/            // l'ensemble des class css appelé par le crm
    js/             // l'ensemble du code js appelé par le crm
v2/                 // MVC pour les vues le modèles et le controlleur : obligatoire pour les plugin dashboard
    routes/         // appelé par le crm directement
        v2route.php
    templates/
                    // les templates
config.json
signatures.json
```

La signature est créée via l'outil fourni par le CRM : **grunt genPluginsSignatures**.

2\. On peut éditer tout de suite : **config.json**
```
   {
       "Name": "EventWorkflow",
       "copyrights": "Philippe Logel © EcclesiaCRM Team",
       "version": "1.0",
       "Description": "Plugin to manage your events",
       "infos": "iMathGeo & Softwares",
       "url_infos": "https://www.ecclesiacrm.com",
       "url_docs": "https://",
       "Settings_url": "v2/eventworkflow/settings",
       "Details": "https://url;iframe=true&amp;width=772&amp;height=549"
   }
```

- Cette partie est capitale pour le système de mise à jour (via le numéro de version)
- Le numéro de version doit toujours être de la forme **x.y**

3\. Droits particuliers

- Il est possible de fixer des droits admin ou non

## Création d'un plugin classique

1\. Concernant l'injection dans la base de données dans la table ` `plugin` `

Dans la base de données Mettre le plugin, on doit fixer

- ``` `plgn_Category` ``` permettra de mettre l'entrée du plugin dans le menu à gauche dans la partie Personnel, RGPD, Etc .... les options sont
```  'Personal', 'GDPR', 'Events','PEOPLE','GROUP', 'SundaySchool', 'Meeting', 'PastoralCare', 'Mail', 'Deposit', 'Funds', 'FreeMenu' ```
-
- une description ``` `plgn_Description` ``` à par exemple : 'Plugin to show the current connected users'
- une version ``` `plgn_version` ``` à '1.0' par exemple
- le type de prefixe pour les entrées ``` `plgn_prefix` ``` à 'jm_'
- ``` `plgn_position`  ``` peut prendre les valeurs ``` 'inside_category_menu', 'after_category_menu' ``` (très clair).

Voici un exemple complet dans le plugin `MeetingJitsi`

```
INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`)
VALUES ('MeetingJitsi', 'Plugin for jitsi Meeting', 'Meeting', NULL, '', '0', '1.0', 'jm_', 'after_category_menu');
```

Pour créer les entrées dans la barre de menus supplémentaires dans la table ` `plugin_menu_barre` `, on doit

- fixer le nom du plugin dans ``` `plgn_mb_plugin_name` ``` par exemple à 'MeetingJitsi'
- Le nom de l'item de menu : ``` `plgn_mb_plugin_Display_name` ``` à 'Settings' par exemple
- l'url ``` `plgn_mb_url` ``` à par exemple 'v2/meeting/dashboard'
- l'icône ``` `plgn_bm_icon` ``` à 'fas fa-cogs'
- Puis une option de sécurité ``` `plgn_bm_grp_sec` ``` aux valeurs possibles de rôles définies dans le crm, par exemple 'usr_admin'

```
usr_AddRecords,
usr_EditRecords,
usr_DeleteRecords,
usr_ShowCart,
usr_ShowMap,
usr_EDrive,
usr_MenuOptions,
usr_ManageGroups,
usr_ManageCalendarResources,
usr_HtmlSourceEditor,
usr_Finance,
usr_Notes,
usr_EditSelf,
usr_Canvasser,
usr_Admin,
usr_showMenuQuery,
usr_CanSendEmail,
usr_ExportCSV,
usr_CreateDirectory,
usr_ExportSundaySchoolPDF,
usr_ExportSundaySchoolCSV,
usr_MainDashboard,
usr_SeePrivacyData,
usr_MailChimp,
usr_GDRP_DPO,
usr_PastoralCare
```

Voci un exemple complet

```
-- insert the menu item
-- the first one is the main menu !!!
INSERT INTO `plugin_menu_barre` (`plgn_mb_plugin_name`, `plgn_mb_plugin_Display_name`, `plgn_mb_url`, `plgn_bm_icon`, `plgn_bm_grp_sec`) VALUES
('MeetingJitsi', 'Jitsi', 'v2/meeting/dashboard', 'fas fa-video', ''),
('MeetingJitsi', 'Dashboard', 'v2/meeting/dashboard', 'fas fa-tachometer-alt', ''),
('MeetingJitsi', 'Settings', 'v2/meeting/settings', 'fas fa-cogs', 'usr_admin');
```

2\. Attention, il doit suivre les recommandations suivantes

   - On peut donc fixer la place et dans le menu ou après le menu (type vue plus haut)
   - on met les css dans : skin/css
   - on met les js dans : skin/js
   - pour les api dans api/plgnapi.php (il faut le mettre dedans, cela sécurise le CRM)
   - pour le code php des vues, il est préférable de le mettre dans v2/templates/
   - Si on veut utiliser le design pattern MVC pour les vues, v2route.php est prêt dans v2/routes/
   - pour ses classes personnelles on peut aller dans core/
   - pour les classes et modèles propel tout est dans core/model
   - etc ...

3\. Attention à l'autoload pour propel ou des classes personnelles:

```
// we've to load the model make the plugin to workmv
spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\\Service', '\\'), array(__DIR__.'/../../core/Service', '/'), $className) . '.php';
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../../core/model', '/'), $className) . '.php';
});
```

## Création d'un plugin dashboard

1\. Pour l'injection au niveau base de données dans la table ` `plugin` `

Dans la base de données Mettre le plugin, on doit fixer

- ``` `plgn_Category` ``` à 'Dashboard'
- une description ``` `plgn_Description` ``` à 'Plugin to show the current connected users'
- une version ``` `plgn_version` ``` à '1.0' par exemple
- le type de prefixe pour les entrées ``` `plgn_prefix` ``` à 'cud_'
- la position à ``` `plgn_default_orientation` ``` à 'top', 'left', 'center', 'right'
- la couleur de la barre de la `card` ``` `plgn_default_color` ``` 'bg-gradient-blue text-white', 'bg-gradient-indigo text-white', .... (voir pour cela la base de données)
- la partie sécurité est très importante ``` `plgn_securities` ``` à ces valeurs possibles qui se trouvent dans `src\EcclesiaCRM\model\User.php`
```
abstract class SecurityOptions
{
    const bNoDashBordItem = 0;
    const bAdmin = 1; // bit 0
    const bPastoralCare = 2;// bit 1
    const bMailChimp = 4;// bit 2
    const bGdrpDpo = 8;// bit 3
    const bMainDashboard = 16;// bit 4
    const bSeePrivacyData = 32;// bit 5
    const bAddRecords = 64;// bit 6
    const bEditRecords = 128;// bit 7
    const bDeleteRecords = 256;// bit 8
    const bMenuOptions = 512;// bit 9
    const bManageGroups = 1024;// bit 10
    const bFinance = 2048;// bit 11
    const bNotes = 4096;// bit 12
    const bCanvasser = 8192;// bit 13
    const bEditSelf = 16384;// bit 14
    const bShowCart = 32768;// bit 15
    const bShowMap = 65536;// bit 16
    const bEDrive = 131072;// bit 17
    const bShowMenuQuery = 262144; // bit 18
    const bDashBoardUser = 1073741824; // bit 30
}
```
- Côté optionnel : ``` `plgn_UserRole_Dashboard_Availability` ``` que l'on peut mettre à 1 (cela permettra à utilisateur d'être administrateur : dans le cas du dashboard News seul quelques personnes peuvent saisir la news, les autres seront simplement des lecteurs).


![Screenshot](../../../img/plugins/plugins_dashboard_admin.png)

Voici un exemple
```
INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`, `plgn_default_orientation`, `plgn_default_color`, `plgn_securities`)
VALUES ('CurrentUsersDashboard', 'Plugin to show the current connected users', 'Dashboard', NULL, '', '1', '1.0', 'cud_', 'inside_category_menu', 'right', 'bg-gradient-green text-black', 1073741824);
```

2\. concernant le code

- Il n'y a qu'une seule vue dans : v2/template/View.php
- régler correctement le card .....
Dans le code de la View.php

```

<?php
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PluginUserRoleQuery;
use EcclesiaCRM\Map\PluginUserRoleTableMap;

....

// we've to load the model make the plugin to work
// for example if you've a model for your propel orm version + a class service

spl_autoload_register(function ($className) {
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../../core/model', '/'), $className) . '.php';
    include_once str_replace(array('Plugins\\Service', '\\'), array(__DIR__.'/../../core/Service', '/'), $className) . '.php';
});

use .....

....

$plugin = PluginQuery::create()
    ->usePluginUserRoleQuery()
        ->addAsColumn('PlgnColor', PluginUserRoleTableMap::COL_PLGN_USR_RL_COLOR)
    ->endUse()
    ->findOneById($PluginId);

..... .... Your code
?>

<div class="card <?= $plugin->getName() ?> <?= $Card_collapsed ?>" style="position: relative; left: 0px; top: 0px;" data-name="<?= $plugin->getName() ?>">
    <div class="card-header border-0 ui-sortable-handle">
        <h5 class="card-title"><i class="fas fa-newspaper"></i> <?= dgettext("messages-NewsDashboard","News") ?></h5>
        <div class="card-tools">
            <button type="button" class="btn btn-default btn-sm" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
            <button type="button" class="btn btn-default btn-sm" data-card-widget="collapse" title="Collapse">
                <i class="fas <?= $Card_collapsed_button?>"></i>
            </button>
        </div>
    </div>
    <div class="card-body"  style="<?= $Card_body ?>;padding: .15rem;">

         .... Your code

    </div>
</div
```

3\. Attention

- pour les dashboard plugins, le code js est chargé dans le footer
- le css est également chargé pour vous par le CRM.
- le code css est chargé automatiquement dans le header !!!!

Cela évite des chargements sales en plein milieu du code.


## Dernières recommandations pour les deux types de plugins


1\. Pour les traductions

- Pour le code PHP : On utilise non pas `gettext` mais avec dgettext et un domaine associé `dgettext("messages-NewsDashboard","News")` et on travaille donc avec du code po séparé pour chaque plugin pour éviter les conflits.
- Pour le code JS : On utilise `i18next.t('News Title', {ns: 'NewsDashboard'})` avec des `namespace`aussi.

2\. Pour du code spécialisé propel ou des class, l'autoload doit se faire manuellement

Conseil :

- ne jamais utiliser `composer dump-autoload` cela ne marchera pas au chargement du plugin via le plugin manager
- Il faut donc contourner le problème comme cela :

```
// we've to load the model make the plugin to work
spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\\Service', '\\'), array(__DIR__.'/../../core/Service', '/'), $className) . '.php';
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../../core/model', '/'), $className) . '.php';
});
```

3\. Les signatures

La signature d'un plugin est créée via l'outil fourni par le CRM : **grunt genPluginsSignatures** à la racine.

Bon développement de plugins.
