# To get started

## To generate the architecture of a plugin

A shell command has been developed to facilitate the work: **createPluginArch.sh**

## To create a plugin

**Read carrefully before starting**

- the **routes** for each plugin are planned and are included in the complete management of the routes in **api/plgnapi.php** (see below) and are planned to be managed via controllers in the **core/APIControllers** path.
- for the views of the routes are predefined also **v2/routes/v2route.php** (they are possibly linked to your own controllers **core/VIEWControllers**).
- this mechanism guarantees maximum security.
- it is imperative to follow the fact that each plugin must have a signature which is validated by the crm (see for that the plugin documentation).


**You have to use this Script**

```
bash createPluginArch.sh *NameOfPlugin*
```

This script will create **NameOfPlugin** in the **Plugins** directory of the **src** directory.

1\. The architecture is of the form

```
api/ // internal api management
    plgnapi.php // we must create our own routes here
    (managed by the CRM directly)
core/ // here we can manage all the models
    APIControllers // to define the controller called in plgnapi.php).
    model // example for propel orm
    VIEWControllers // for the views of the v2/routes part
    ...
    // for example
    Service
    views
ident/ // for an access to an external api (optional)
    routes/ // called by the crm directly
    templates/ // called by the route part
locale/
    js/ // js translation code
    textdomain/ // for gettext translation of php code
    index.html
mysql/ // setting up mysql files
    index.html // protection file
    Install.sql // sql script to create the database
                    // called by the plugin manager
    Uninstall.sql // to uninstall the plugin, it's up to you not to forget anything
    upgrade.json // this part allows to manage upgrades (under development)
skin/
    css/ // all css classes called by the crm
    js/ // all the js code called by the crm
v2/ // MVC for views, models and controller : mandatory for dashboard plugin
    routes/ // called by the crm directly
        v2route.php
    templates/
                    // templates
config.json
signatures.json
```

The signature is created via the tool provided by the CRM : **grunt genPluginsSignatures**.

2\. We can edit it right away: **config.json**
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

- This part is crucial for the update system (via the version number)
- The version number must always be of the form **x.y**.

3\. Special rights

- It is possible to set admin rights or not

## Creation of a classic plugin

1\. Concerning injection into the database in the "plugin" table

In the database Put the plugin, we must set

- ```plgn_Category``` will allow to put the entry of the plugin in the menu on the left in the Personal, RGPD, Etc. .... the options are
  ```'Personal', 'GDPR', 'Events', 'PEOPLE', 'GROUP', 'SundaySchool', 'Meeting', 'PastoralCare', 'Mail', 'Deposit', 'Funds', 'FreeMenu', 'EDRive'```
-
- a ``plgn_Description`` description, e.g.: 'Plugin to show the current connected users
- a version ```plgn_version`` `` to e.g. '1.0
- the prefix type for the entries ```plgn_prefix`` to 'jm_'
- ```plgn_position`` ```can take the values ```'inside_category_menu'', ``after_category_menu'' ``` (very clear).

Here is a complete example in the `MeetingJitsi` plugin

```
INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`)
VALUES ('MeetingJitsi', 'Plugin for jitsi Meeting', 'Meeting', NULL, '', '0', '1.0', 'jm_', 'after_category_menu');
```

To create additional menu bar entries in the `plugin_menu_bar` table, we must

- set the name of the plugin in ``plgn_mb_plugin_name`` for example to ``MeetingJitsi``.
- The name of the menu item: ```plgn_mb_plugin_Display_name`` to ``Settings'' for example
- the url ```plgn_mb_url`` ``to for example 'v2/meeting/dashboard
- the icon ```plgn_bm_icon`` at 'fas fa-cogs
- Then a security option ````plgn_bm_grp_sec`` ```to the possible values of roles defined in the crm, for example 'usr_admin'


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

Here is a complete example

```
-- insert the menu item
-- the first one is the main menu !!!
INSERT INTO `plugin_menu_bar` (`plgn_mb_plugin_name`, `plgn_mb_plugin_Display_name`, `plgn_mb_url`, `plgn_bm_icon`, `plgn_bm_grp_sec`) VALUES
('MeetingJitsi', 'Jitsi', 'v2/meeting/dashboard', 'fas fa-video', ''),
('MeetingJitsi', 'Dashboard', 'v2/meeting/dashboard', 'fas fa-tachometer-alt', ''),
('MeetingJitsi', 'Settings', 'v2/meeting/settings', 'fas fa-cogs', 'usr_admin');
```

2\. Attention, it must follow the following recommendations

- You can set the place and in the menu or after the menu (type seen above)
- put the css in : skin/css
- put the js in : skin/js
- for the api in api/plgnapi.php (you have to put it in, it secures the CRM)
- for the php code of the views, it is better to put it in v2/templates/
- If you want to use the MVC design pattern for the views, v2route.php is ready in v2/routes/
- for your personal classes you can go in core/
- for propel classes and models everything is in core/model
- etc ...

3\. Be careful with the autoload for propel or personal classes:

```
// we've to load the model to make sure the plugin will work
spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\Service', '\'), array(__DIR__.'/../../core/Service', '/'), $className) . '.php';
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../../core/model', '/'), $className) . '.php';
});
```

4\. For menus

- For menu management in the case of a classic plugin, fixed menus can be injected via the database, see the example of the jitsimeeting plugin: `install.sql`, see above, sql entries: `plugin_menu_bar`.

- for menu link management (in the dynamic case: you create menus dynamically) correlated to a base menu, here's an example code :

```
....

        // we set the new menu bar link
        $menuBarLink = new PluginMenuBar();
        $menuBarLink->setURL($event->getLink());
        $menuBarLink->setName('EventWorkflow');
        $menuBarLink->setDisplayName(InputUtils::FilterHTML($name));
        $menuBarLink->setIcon('');
        $menuBarLink->setLinkParentId(($type == 'one day')?$masterSeveralDay->getId():$masterOneDay->getId());         
        $menuBarLink->save();
        // end of the menu link

....

```

## Creating a dashboard plugin

1\. For the injection at the database level in the "plugin" table

'widget'



In the database Put the plugin, we must set

- ``plgn_Category`` to ``Dashboard``.
- ``plgn_default_orientation`` to ``widget`` if you want to have a widget (the little square in the top main dashboard)
- a description ``plgn_Description`` to ``Plugin to show the current connected users
- a version ```plgn_version`` `` to '1.0' for example
- the prefix type for the `plgn_prefix` entries to `cud_`.
- the position at ``plgn_default_orientation`` at ``top`, ``left``, ``center``, ``right``
- the color of the card's bar ````plgn_default_color`` ```bg-gradient-blue text-white'', ``bg-gradient-indigo text-white'', .... (see for this the database)
- the security part is very important ``plgn_securities`` to those possible values which are in `src\EcclesiaCRM\model\User.php`
```
abstract class SecurityOptions
{
    const bNoDashBordItem = 0;
    const bAdmin = 1; // bit 0
    const bPastoralCare = 2;// bit 1
    const bMailChimp = 4;// bit 2
    const bGdrpDpo = 8;// bit 3
    const bMainDashboard = 16;// bit 4 is now deprecated
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
- Optional side: ````plgn_UserRole_Dashboard_Availability``` which can be set to 1 (this will allow user to be administrator: in the case of the News dashboard only few people can enter the news, the others will be simply readers).


Screenshot](../../../img/plugins/plugins_dashboard_admin.png)

Here is an example
```
INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`, `plgn_default_orientation`, `plgn_default_color`, `plgn_securities`)
VALUES ('CurrentUsersDashboard', 'Plugin to show the current connected users', 'Dashboard', NULL, '', '1', '1.0', 'cud_', 'inside_category_menu', 'right', 'bg-gradient-green text-black', 1073741824);
```

2\. about the code

- There is only one view in : v2/template/View.php
- set correctly the card .....
  In the code of the View.php

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
    <div class="card-body" style="<?= $Card_body ?>;padding: .15rem;">

         .... Your code

    </div>
</div
```

3\. Attention

- pour les dashboard plugins, le code js est chargé dans le footer
- le css est également chargé pour vous par le CRM.
- le code css est chargé automatiquement dans le header !!!!

Cela évite des chargements sales en plein milieu du code.


## Final recommendations for both types of plugins

0\. VERY IMPORTANT

For templates and JS/CSS code to work properly

- The plugin folder must have **the same name as the plugin name** (this is what plugin management and integration is all about).
- In the case of the JitsiMeeting view controler, note that in `PhpRenderer` we have: `SystemURLs::getDocumentRoot().'/Plugins/MeetingJitsi/...` if the name differs, the templates and JS/CSS code will not load.

```
    public function renderDashboard (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer(SystemURLs::getDocumentRoot().'/Plugins/MeetingJitsi/v2/templates');

        if ( !( SessionUser::getUser()->isEnableForPlugin('MeetingJitsi') ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . /v2/dashboard');
        }

        return $renderer->render($response, 'meetingdashboard.php', $this->argumentDashboard());
    }
```

- Everything is done in the plugin manager to ensure that the code loads optimally. 

Translated with DeepL.com (free version)

1\. For translations

- For the PHP code: We don't use `gettext` but with dgettext and an associated domain `dgettext("messages-NewsDashboard", "News")` and we work with separate po code for each plugin to avoid conflicts.
- For the JS code: We use `i18next.t('News Title', {ns: 'NewsDashboard'})` with `namespace` also.

2\. For specialized propel code or classes, the autoload must be done manually

Tip :

- never use `composer dump-autoload` it will not work when loading the plugin via the plugin manager
- So you have to work around the problem like this:

```
// we've to load the model make the plugin to work
spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\Service', '\'), array(__DIR__.'/../../core/Service', '/'), $className) . '.php';
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../../core/model', '/'), $className) . '.php';
});
```

3\. The signatures

The signature of a plugin is created via the tool provided by the CRM : **grunt genPluginsSignatures** at the root path of your dev env.

Good development of plugins.

