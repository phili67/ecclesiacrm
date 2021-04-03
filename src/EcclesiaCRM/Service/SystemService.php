<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\VersionQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Backup\CreateBackup;

use Github\Client;
use Propel\Runtime\Propel;
use PDO;

require SystemURLs::getDocumentRoot() . '/vendor/ifsnop/mysqldump-php/src/Ifsnop/Mysqldump/Mysqldump.php';


class SystemService
{
    public function updateSessionLastOperation ()
    {
       $_SESSION['tLastOperation'] = time();
    }

    public function isSessionStillValid ()
    {
      // Basic security: If the UserID isn't set (no session), redirect to the login page
      if (is_null(SessionUser::getUser())) {
        return false; // we have to return to the login page
      }

      // Check for login timeout.  If login has expired, redirect to login page
      if (SystemConfig::getValue('iSessionTimeout') > 0) {
          if ((time() - $_SESSION['tLastOperation']) > SystemConfig::getValue('iSessionTimeout')) {
              return false; // we have to return to the login page
          }// the time is set in the function page
      }

      return true;
    }

    public function getSessionTimeout ()
    {
      return  SystemConfig::getValue('iSessionTimeout')-(time() - $_SESSION['tLastOperation']);
    }

    public function getLatestRelese()
    {
        $client = new Client();
        $release = null;
        try {
            //$json = file_get_contents('https://www.ecclesiacrm.com/download.php');
            //$release = json_decode($json,TRUE);
            $release = $client->api('repo')->releases()->latest('phili67', 'ecclesiacrm');
        } catch (\Exception $e) {
        }

        return $release;
    }

    static public function getInstalledVersion()
    {
        $composerFile = file_get_contents(SystemURLs::getDocumentRoot() . '/composer.json');
        $composerJson = json_decode($composerFile, true);
        $version = $composerJson['version'];

        return $version;
    }

    public function getConfigurationSetting($settingName, $settingValue)
    {
        MiscUtils::requireUserGroupMembership('bAdmin');
    }

    public function setConfigurationSetting($settingName, $settingValue)
    {
        MiscUtils::requireUserGroupMembership('bAdmin');
    }

    static public function getDBVersion()// get the DB version
    {
        $version = VersionQuery::Create()
            ->orderById('DESC')->findOne();

        return $version->getVersion();
    }

    static public function getDBMainVersion() // the main part of the version 2.3.10 will give : 2
    {
        return strstr(self::getDBVersion(),".",true);
    }


    static public function getPackageMainVersion() // the main part of the version 2.3.10 will give : 2
    {
        return strstr(self::getInstalledVersion(),".",true);
    }

    public function getDBServerVersion()
    {
      try{
        return Propel::getServiceContainer()->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION);
      }
      catch (\Exception $exc)
      {
        return "Could not obtain DB Server Version";
      }
    }

    static public function isDBCurrent()
    {
        return SystemService::getDBVersion() == SystemService::getInstalledVersion();
    }


    public function reportIssue($data)
    {
        $serviceURL = 'https://www.ecclesiacrm.com/issues/';
        $headers = [];
        $headers[] = 'Content-type: application/json';

        $issueDescription = $data->issueDescription . "\r\n\r\n\r\n" .
            "Collected Value Title |  Data \r\n" .
            "----------------------|----------------\r\n" .
            'Page Name |' . $data->pageName . "\r\n" .
            'Screen Size |' . $data->screenSize->height . 'x' . $data->screenSize->width . "\r\n" .
            'Window Size |' . $data->windowSize->height . 'x' . $data->windowSize->width . "\r\n" .
            'Page Size |' . $data->pageSize->height . 'x' . $data->pageSize->width . "\r\n" .
            'Platform Information | ' . php_uname($mode = 'a') . "\r\n" .
            'PHP Version | ' . phpversion() . "\r\n" .
            'SQL Version | ' . $this->getDBServerVersion() . "\r\n" .
            'EcclesiaCRM Version |' . $_SESSION['sSoftwareInstalledVersion'] . "\r\n" .
            'Reporting Browser |' . $_SERVER['HTTP_USER_AGENT'] . "\r\n".
            'Prerequisite Status |' . ( AppIntegrityService::arePrerequisitesMet() ? "All Prerequisites met" : "Missing Prerequisites: " .json_encode(AppIntegrityService::getUnmetPrerequisites()))."\r\n";
            //'Integrity check status |' . file_get_contents(SystemURLs::getDocumentRoot() . '/integrityCheck.json')."\r\n";

        if (function_exists('apache_get_modules')) {
            $issueDescription .= 'Apache Modules    |' . implode(',', apache_get_modules());
        }

        $postdata = new \stdClass();
        $postdata->issueTitle = InputUtils::LegacyFilterInput($data->issueTitle);
        $postdata->issueDescription = $issueDescription;

        $curlService = curl_init($serviceURL);

        curl_setopt($curlService, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlService, CURLOPT_POST, true);
        curl_setopt($curlService, CURLOPT_POSTFIELDS, json_encode($postdata));
        curl_setopt($curlService, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlService, CURLOPT_CONNECTTIMEOUT, 1);

        $result = curl_exec($curlService);
        if ($result === false) {
            throw new \Exception('Unable to reach the issue bridge', 500);
        }

        return $result;
    }

    public function runTimerJobs()
    {
        if (NotificationService::isUpdateRequired())
        {
          NotificationService::updateNotifications();
        }
        //start the external backup timer job
        if (SystemConfig::getBooleanValue('bEnableExternalBackupTarget') && SystemConfig::getValue('sExternalBackupAutoInterval') > 0) {  //if remote backups are enabled, and the interval is greater than zero
            try {
                $now = new \DateTime();  //get the current time
                $previous = new \DateTime(SystemConfig::getValue('sLastBackupTimeStamp')); // get a DateTime object for the last time a backup was done.
                $diff = $previous->diff($now);  // calculate the difference.
                if (!SystemConfig::getValue('sLastBackupTimeStamp') || $diff->h >= SystemConfig::getValue('sExternalBackupAutoInterval')) {  // if there was no previous backup, or if the interval suggests we do a backup now.
                    $createBackup = new CreateBackup();
                    $createBackup->run();
                    $now = new \DateTime();  // update the LastBackupTimeStamp.
                    SystemConfig::setValue('sLastBackupTimeStamp', $now->format('Y-m-d H:i:s'));
                }
            } catch (\Exception $exc) {
                // an error in the auto-backup shouldn't prevent the page from loading...
            }
        }
        if (SystemConfig::getBooleanValue('bEnableIntegrityCheck') && SystemConfig::getValue('iIntegrityCheckInterval') > 0) {
            $now = new \DateTime();  //get the current time
            $previous = new \DateTime(SystemConfig::getValue('sLastIntegrityCheckTimeStamp')); // get a DateTime object for the last time a backup was done.
            $diff = $previous->diff($now);  // calculate the difference.
            if (!SystemConfig::getValue('sLastIntegrityCheckTimeStamp') || $diff->h >= SystemConfig::getValue('iIntegrityCheckInterval')) {  // if there was no previous backup, or if the interval suggests we do a backup now.
                $integrityCheckFile = SystemURLs::getDocumentRoot() . '/integrityCheck.json';
                $appIntegrity = AppIntegrityService::verifyApplicationIntegrity();
                file_put_contents($integrityCheckFile, json_encode($appIntegrity));
                $now = new \DateTime();  // update the LastBackupTimeStamp.
                SystemConfig::setValue('sLastIntegrityCheckTimeStamp', $now->format('Y-m-d H:i:s'));
            }
        }
    }

    private function download_remote_file_with_curl($file_url, $save_to)
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_POST, 0);
      curl_setopt($ch,CURLOPT_URL,$file_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $file_content = curl_exec($ch);
      curl_close($ch);

      $downloaded_file = fopen($save_to, 'w');
      fwrite($downloaded_file, $file_content);
      fclose($downloaded_file);

    }

    public function downloadLatestRelease()
    {
        $release = $this->getLatestRelese();
        $UpgradeDir = SystemURLs::getDocumentRoot() . '/Upgrade';
        foreach ($release['assets'] as $asset) {
            if ($asset['name'] == "EcclesiaCRM-" . $release['name'] . ".zip") {
                $url = $asset['browser_download_url'];
            }
        }
        mkdir($UpgradeDir);
        file_put_contents($UpgradeDir . '/' . basename($url), file_get_contents($url));
        //$this->download_remote_file_with_curl($url,$UpgradeDir . '/' . basename($url));
        $returnFile = [];
        $returnFile['fileName'] = basename($url);
        $returnFile['releaseNotes'] = $release['body'];
        $returnFile['fullPath'] = $UpgradeDir . '/' . basename($url);
        $returnFile['sha1'] = sha1_file($UpgradeDir . '/' . basename($url));

        return $returnFile;
    }

    public function moveDir($src, $dest)
    {
        $files = array_diff(scandir($src), ['.', '..']);
        foreach ($files as $file) {
            if (is_dir("$src/$file")) {
                mkdir("$dest/$file");
                $this->moveDir("$src/$file", "$dest/$file");
            } else {
                rename("$src/$file", "$dest/$file");
            }
        }

        return rmdir($src);
    }

    public function doUpgrade($zipFilename, $sha1)
    {
        ini_set('max_execution_time', 60);
        if ($sha1 == sha1_file($zipFilename)) {
            $zip = new \ZipArchive();
            if ($zip->open($zipFilename) == true) {
                $zip->extractTo(SystemURLs::getDocumentRoot() . '/Upgrade');
                $zip->close();
                $this->moveDir(SystemURLs::getDocumentRoot() . '/Upgrade/ecclesiacrm', SystemURLs::getDocumentRoot());
            }

            unlink($zipFilename);
            SystemConfig::setValue('sLastIntegrityCheckTimeStamp', null);

            return 'success';
        } else {
            return 'hash validation failure';
        }
    }

        // Returns a file size limit in bytes based on the PHP upload_max_filesize
    // and post_max_size
    public static function getMaxUploadFileSize($humanFormat=true) {
      //select maximum upload size
      $max_upload = SystemService::parse_size(ini_get('upload_max_filesize'));
      //select post limit
      $max_post = SystemService::parse_size(ini_get('post_max_size'));
      //select memory limit
      $memory_limit = SystemService::parse_size(ini_get('memory_limit'));
      // return the smallest of them, this defines the real limit
      if ($humanFormat)
      {
        return SystemService::human_filesize(min($max_upload, $max_post, $memory_limit));
      }
      else
      {
         return min($max_upload, $max_post, $memory_limit);
      }
    }

    private static function parse_size($size) {
      $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
      $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
      if ($unit) {
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
      }
      else {
        return round($size);
      }
    }

    static function human_filesize($bytes, $decimals = 2) {
      $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
      $factor = floor((strlen($bytes) - 1) / 3);
      return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    static public function getCopyrightDate()
     {
         $composerFile = file_get_contents(SystemURLs::getDocumentRoot() . '/composer.json');
         $composerJson = json_decode($composerFile, true);
         $version = new \DateTime($composerJson['date']);
         return $version->format("Y");
     }
}
