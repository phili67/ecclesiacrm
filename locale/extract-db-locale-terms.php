<?php

include "connection.php";

/*
// Put
<?php
  $db_servername = 'localhost';
  $db_username = 'user_name';
  $db_password = 'password';
  $db_name = 'name of the db';
*/

//phpdismod -s cli xdebug

$stringsDir = 'db-strings';
$stringFiles = [];

$db = new \PDO('mysql:host='.$db_servername.';dbname='.$db_name.';charset=utf8mb4', $db_username, $db_password);
$query = 'select DISTINCT ucfg_tooltip as term, "" as translation, "userconfig_ucfg" as cntx from userconfig_ucfg
union all
select DISTINCT qry_Name as term, "" as translation, "query_qry" as cntx   from query_qry
union all
select DISTINCT qry_Description as term, "" as translation, "query_qry" as cntx    from query_qry
union all
select DISTINCT qpo_Display as term, "" as translation, "queryparameteroptions_qpo" as cntx from queryparameteroptions_qpo
union all
select DISTINCT qrp_Name as term, "" as translation, "queryparameters_qrp" as cntx from queryparameters_qrp
union all
select DISTINCT qrp_Description term, "" as translation, "queryparameters_qrp" as cntx from queryparameters_qrp
union all
select DISTINCT qry_Name as term, "" as translation, "query_qry" as cntx from query_qry
union all
select DISTINCT qry_Description as term, "" as translation, "query_qry" as cntx from query_qry;';
foreach ($db->query($query) as $row) {
    $stringFile = $stringsDir.'/'.$row['cntx'].'.php';
    if (!file_exists($stringFile)) {
        file_put_contents($stringFile, "<?php\r\n", FILE_APPEND);
        array_push($stringFiles, $stringFile);
    }
    $rawDBTerm = $row['term'];
    $dbTerm = addslashes($rawDBTerm);
    file_put_contents($stringFile, "gettext('".$dbTerm."');\n", FILE_APPEND);
}
foreach ($stringFiles as $stringFile) {
    file_put_contents($stringFile, "\r\n?>", FILE_APPEND);
}

/*$stringFile = $stringsDir.'/settings-countries.php';
require '../src/ChurchCRM/data/Countries.php';
file_put_contents($stringFile, "<?php\r\n", FILE_APPEND);

foreach (ChurchCRM\data\Countries::getNames() as $country) {
    file_put_contents($stringFile, 'gettext("'.addslashes($country)."\");\r\n", FILE_APPEND);
}
file_put_contents($stringFile, "\r\n?>", FILE_APPEND);*/

$stringFile = $stringsDir.'/settings-locales.php';
file_put_contents($stringFile, "<?php\r\n", FILE_APPEND);
$localesFile = file_get_contents("../src/locale/locales.json");
$locales = json_decode($localesFile, true);
foreach ($locales as $key => $value) {
    file_put_contents($stringFile, 'gettext("'.$key."\");\r\n",FILE_APPEND);
}
file_put_contents($stringFile, "\r\n?>", FILE_APPEND);

//phpenmod -s cli xdebug
