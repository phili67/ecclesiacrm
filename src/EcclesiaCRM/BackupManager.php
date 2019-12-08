<?php

namespace EcclesiaCRM\Backup;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\FileSystemUtils;
use EcclesiaCRM\utils\LoggerUtils;
use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\SQLUtils;

use PharData;
use Ifsnop\Mysqldump\Mysqldump;
use Propel\Runtime\Propel;




abstract class BackupType
{
    // archive type : archiveType in BackupDatabae.php
    const GZSQL         = 0;
    const Zip           = 1;// for the zip code : actually deactivated
    const SQL           = 2;
    const FullBackup    = 3;
}

    class JobBase
   {
       /**
         *
         * @var BackupType
         */
       protected $BackupType;


        /**
         *
         * @var string
         */
        protected $backupRoot;

       /**
        *
        * @var String
        */
       protected $TempFolder;
    
       protected function CreateEmptyTempFolder($contentDir)
       {
           // both backup and restore operations require a clean temporary working folder.  Create it.
           $this->backupRoot = SystemURLs::getDocumentRoot() . '/tmp_attach/';
           $this->TempFolder = $this->backupRoot."/".$contentDir."/";
           LoggerUtils::getAppLogger()->debug("Removing temp folder tree at ". $this->TempFolder);
           FileSystemUtils::recursiveRemoveDirectory($this->backupRoot, true);
           LoggerUtils::getAppLogger()->debug("Creating temp folder at ". $this->TempFolder);
           mkdir($this->TempFolder, 0750, true);
           LoggerUtils::getAppLogger()->debug("Temp folder created");
           return $this->TempFolder;
       }

       protected function DeleteTempFolder()
       {
           FileSystemUtils::recursiveRemoveDirectory($this->backupRoot,true);
       }
   }

   class RestoreBackup extends JobBase {
       /**
        *
        * @var SplFileInfo
        */
       protected $file;
       /**
        *
        * @var array
        */
       protected $Messages;

       /**
        *
        * @var array
        */
       protected $headers;

       /**
        *
        * @var string
        */
       protected $backupDir;

       /**
        *
        * @var string
        */
       protected $uploadedFileDestination;

       /**
        *
        * @var string
        */
       protected $type;

       /**
        *
        * @var string
        */
       protected $type2;

       private function IsIncomingFileFailed()
       {
           // Not actually sure what this is supposed to do, but it was working before??
           return $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0;
       }

       public function __construct($file)
       {
           if ($this->IsIncomingFileFailed()) {
               $message = _('The selected file exceeds this servers maximum upload size of').": ". SystemService::getMaxUploadFileSize();
               LoggerUtils::getAppLogger()->error($message);
               throw new \Exception($message, 500);
           }

           //MiscUtils::requireUserGroupMembership('bAdmin');
           $this->Messages = array();

           $this->file = $file;
           $this->type = pathinfo($file['name'], PATHINFO_EXTENSION);
           $this->type2 = pathinfo(mb_substr($file['name'], 0, strlen($file['name']) - 3), PATHINFO_EXTENSION);
           $this->headers = array();

           $this->backupDir = $this->CreateEmptyTempFolder('EcclesiaCRMRestores');
       }

       public function run()
       {
           $connection = Propel::getConnection();

           $this->uploadedFileDestination =  $this->backupDir . '/' . $this->file['name'];

           move_uploaded_file($this->file['tmp_name'], $this->uploadedFileDestination);
           if ($this->type == 'gz') {
               if ($this->type2 == 'tar') {
                   $phar = new PharData($this->uploadedFileDestination);
                   $phar->extractTo($this->backupDir);
                   $this->SQLfile = "$this->backupDir/EcclesiaCRM-Database.sql";
                   if (file_exists($this->SQLfile))
                   {
                       SQLUtils::sqlImport($this->SQLfile, $connection);
                       FileSystemUtils::recursiveRemoveDirectory(SystemURLs::getDocumentRoot() . '/Images');
                       FileSystemUtils::recursiveCopyDirectory($this->backupDir . '/Images/', SystemURLs::getImagesRoot());
                   }
                   else
                   {
                       FileSystemUtils::recursiveRemoveDirectory($this->backupDir,true);
                       throw new Exception(_("Backup archive does not contain a database").": " . $file['name']);
                   }

               } elseif ($this->type2 == 'sql') {
                   $this->SQLfile = $this->backupDir . str_replace('.gz', '', $file['name']);
                   file_put_contents($this->SQLfile, gzopen($this->uploadedFileDestination, r));
                   SQLUtils::sqlImport($this->SQLfile, $connection);
               }
           } elseif ($this->type == 'sql') {
               SQLUtils::sqlImport($this->uploadedFileDestination, $connection);
           } else {
               FileSystemUtils::recursiveRemoveDirectory($this->backupDir,true);
               throw new Exception(_("Unknown File Type").": " . $this->type . " "._("from file").": " . $file['name']);
           }

           $this->UpgradeStatus = UpgradeService::upgradeDatabaseVersion();
           //When restoring a database, do NOT let the database continue to create remote backups.
           //This can be very troublesome for users in a testing environment.
           SystemConfig::setValue('bEnableExternalBackupTarget', '0');
           array_push($this->Messages, _('As part of the restore, external backups have been disabled.  If you wish to continue automatic backups, you must manuall re-enable the bEnableExternalBackupTarget setting.'));
           SystemConfig::setValue('sLastIntegrityCheckTimeStamp', null);

           return $this;
       }
   }


