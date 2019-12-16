<?php

namespace EcclesiaCRM\Backup;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\utils\LoggerUtils;

use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\Service\UpgradeService;

use EcclesiaCRM\FileSystemUtils;
use EcclesiaCRM\SQLUtils;

use PharData;
use Ifsnop\Mysqldump\Mysqldump;
use Propel\Runtime\Propel;
use ZipArchive;

abstract class BackupType
{
    // archive type : archiveType in BackupDatabae.php
    const GZSQL                 = 0;
    const Zip                   = 1;// for the zip
    const SQL                   = 2;
    const FullBackup            = 3;
    const FullBackupEncrypted   = 4;
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

    /**
     *
     * @var string
     */
    protected $backupDir;

    protected function CreateEmptyTempFolder($contentDir)
    {
        // both backup and restore operations require a clean temporary working folder.  Create it.
        $this->backupRoot = SystemURLs::getDocumentRoot() . '/tmp_attach/';
        $this->TempFolder = $this->backupRoot . $contentDir . "/";
        LoggerUtils::getAppLogger()->debug("Removing temp folder tree at " . $this->TempFolder);
        FileSystemUtils::recursiveRemoveDirectory($this->backupRoot, true);
        LoggerUtils::getAppLogger()->debug("Creating temp folder at " . $this->TempFolder);
        mkdir($this->TempFolder, 0750, true);
        LoggerUtils::getAppLogger()->debug("Temp folder created");
        return $this->TempFolder;
    }

    protected function DeleteTempFolder()
    {
        FileSystemUtils::recursiveRemoveDirectory($this->backupRoot, true);
    }
}

class RestoreBackup extends JobBase
{
    /**
     *
     * @var SplFileInfo
     */
    protected $file;
    /**
     *
     * @var array
     */
    public $Messages;

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

    /**
     *
     * @bool
     */

    public $UpgradeStatus;

    /**
     *
     * @var
     */
    protected $SQLfile;

    private function IsIncomingFileFailed()
    {
        // Not actually sure what this is supposed to do, but it was working before??
        return $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0;
    }

    public function __construct($file)
    {
        if ($this->IsIncomingFileFailed()) {
            $message = _('The selected file exceeds this servers maximum upload size of') . ": " . SystemService::getMaxUploadFileSize();
            LoggerUtils::getAppLogger()->error($message);
            throw new \Exception($message, 500);
        }

        $this->Messages = [];

        $this->file = $file;
        $this->type = pathinfo($file['name'], PATHINFO_EXTENSION);
        $this->type2 = pathinfo(mb_substr($file['name'], 0, strlen($file['name']) - 3), PATHINFO_EXTENSION);

        $this->backupDir = $this->CreateEmptyTempFolder('EcclesiaCRMRestores');
    }

    public function run()
    {
        $connection = Propel::getConnection();

        $this->uploadedFileDestination = $this->backupDir . $this->file['name'];

        move_uploaded_file($this->file['tmp_name'], $this->uploadedFileDestination);
        if ($this->type == 'gz') {
            if ($this->type2 == 'tar') {
                $phar = new PharData($this->uploadedFileDestination);
                $phar->extractTo($this->backupDir);
                $this->SQLfile = "$this->backupDir/EcclesiaCRM-Database.sql";
                if (file_exists($this->SQLfile)) {
                    SQLUtils::sqlImport($this->SQLfile, $connection);
                    FileSystemUtils::recursiveRemoveDirectory(SystemURLs::getDocumentRoot() . '/Images');
                    FileSystemUtils::recursiveCopyDirectory($this->backupDir . '/Images/', SystemURLs::getImagesRoot());
                } else {
                    FileSystemUtils::recursiveRemoveDirectory($this->backupDir, true);
                    throw new \Exception(_("Backup archive does not contain a database") . ": " . $this->file['name']);
                }

            } elseif ($this->type2 == 'sql') {
                $this->SQLfile = $this->backupDir . str_replace('.gz', '', $this->file['name']);
                file_put_contents($this->SQLfile, gzopen($this->uploadedFileDestination, r));
                SQLUtils::sqlImport($this->SQLfile, $connection);
            }
        } elseif ($this->type == 'sql') {
            SQLUtils::sqlImport($this->uploadedFileDestination, $connection);
        } elseif ($this->type == 'zip') {
            $zip = new ZipArchive;
            $this->SQLfile = $this->backupDir . str_replace('.zip', '', $this->file['name']);
            LoggerUtils::getAppLogger()->debug("Upload destination : ".$this->file['name']);
            LoggerUtils::getAppLogger()->debug("Upload destination : ".$this->SQLfile);
            LoggerUtils::getAppLogger()->debug("Upload destination : ".$this->uploadedFileDestination);
            if ($zip->open($this->uploadedFileDestination) === TRUE) {
                $zip->extractTo($this->backupDir);
                $zip->close();
                SQLUtils::sqlImport($this->backupDir."EcclesiaCRM-Database.sql", $connection);
            } else {
                throw new \Exception(_("Impossible to open"). $this->saveTo);
            }
        } else {
            FileSystemUtils::recursiveRemoveDirectory($this->backupDir, true);
            throw new \Exception(_("Unknown File Type") . ": " . $this->type . " " . _("from file") . ": " . $this->file['name']);
        }

        $this->UpgradeStatus = UpgradeService::upgradeDatabaseVersion();
        //When restoring a database, do NOT let the database continue to create remote backups.
        //This can be very troublesome for users in a testing environment.
        SystemConfig::setValue('bEnableExternalBackupTarget', '0');
        $this->Messages[] = _('As part of the restore, external backups have been disabled.  If you wish to continue automatic backups, you must manuall re-enable the bEnableExternalBackupTarget setting.');
        SystemConfig::setValue('sLastIntegrityCheckTimeStamp', null);

        return $this;
    }
}

class CreateBackup extends JobBase
{
    /**
     *
     * @var
     */
    protected $params;
    /**
     *
     * @var
     */
    protected $saveTo;
    /**
     *
     * @var
     */
    protected $SQLFile;
    /**
     *
     * @var array
     */
    protected $headers;

    // this is for the remote server

    /**
     *
     * @var
     */
    protected $credentials;
    /**
     *
     * @int
     */
    protected $filesize;
    /**
     *
     * @url : string
     */
    protected $remoteUrl;
    /**
     *
     * @bool
     */
    public $result;
    /**
     *
     * @string
     */
    public $filename;

    /**
     *
     * @string
     */
    protected $encryptCommand;

    public function __construct($params)
    {
        //MiscUtils::requireUserGroupMembership('bAdmin');
        $this->backupDir = $this->CreateEmptyTempFolder('EcclesiaCRMBackups');

        $this->headers = [];
        $this->params = $params;
        $this->saveTo = "$this->backupDir/EcclesiaCRM-" . date(SystemConfig::getValue("sDateFilenameFormat"));
        $this->SQLFile = "$this->backupDir/EcclesiaCRM-Database.sql";

    }

    private function getDatabaseBackup()
    {
        try {
            $dump = new Mysqldump(Bootstrapper::GetDSN(), Bootstrapper::GetUser(), Bootstrapper::GetPassword(), ['add-drop-table' => true]);
            $dump->start($this->SQLFile);
        } catch (\Exception $e) {
            throw new \Exception("Unable to create backup archive at " . $this->SQLFile, 500);
        }

        switch ($this->params->iArchiveType) {
            case BackupType::GZSQL: // The user wants a gzip'd SQL file.
                $this->saveTo .= '.sql.gz';
                $gzf = gzopen($this->saveTo, 'w6');
                gzwrite($gzf, file_get_contents($this->SQLFile));
                gzclose($gzf);
                break;
            case BackupType::Zip:
                //todo
                $zip = new \ZipArchive();
                $this->saveTo.= '.sql.zip';

                if ($zip->open($this->saveTo, ZipArchive::CREATE)===TRUE) {
                    $zip->addFromString('EcclesiaCRM-Database.sql', file_get_contents($this->SQLFile));
                    $zip->close();
                } else {
                    throw new \Exception(_("Impossible to open"). $this->saveTo);
                }
                break;
            case BackupType::SQL : //The user wants a plain ol' SQL file
                $this->saveTo .= '.sql';
                rename($this->SQLFile, $this->saveTo);
                break;
            case BackupType::FullBackup : //the user wants a .tar.gz file
                $this->saveTo .= '.tar';
                $phar = new \PharData($this->saveTo);
                $phar->startBuffering();
                $phar->addFile($this->SQLFile, 'EcclesiaCRM-Database.sql');
                $imageFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(SystemURLs::getImagesRoot()));
                foreach ($imageFiles as $imageFile) {
                    if (!$imageFile->isDir()) {
                        $localName = str_replace(SystemURLs::getDocumentRoot() . '/', '', $imageFile->getRealPath());
                        $phar->addFile($imageFile->getRealPath(), $localName);
                    }
                }
                $phar->stopBuffering();
                $phar->compress(\Phar::GZ);
                unlink($this->saveTo);
                $this->saveTo .= '.gz';
                break;
        }


        if ($this->params->bEncryptBackup) {  //the user has selected an encrypted backup
            putenv('GNUPGHOME=/tmp');
            $this->encryptCommand = "echo ".$this->params->password." | " . SystemConfig::getValue('sPGPname') . " -q -c --batch --no-tty --passphrase-fd 0 ".$this->saveTo;
            $this->saveTo .= '.gpg';
            system($this->encryptCommand);
            $this->params->iArchiveType = BackupType::FullBackupEncrypted;
        }

        switch ($this->params->iArchiveType) {
            case BackupType::GZSQL:
            case BackupType::FullBackup:
                $this->headers[] = '';
            case BackupType::Zip:
                $this->headers[] = 'Content-type: application/x-zip';
            case BackupType::SQL:
                $this->headers[] = 'Content-type: text/plain';
            case BackupType::FullBackupEncrypted:
                $this->headers[] = 'Content-type: application/pgp-encrypted';
        }

        $this->filename = mb_substr($this->saveTo, strrpos($this->saveTo, '/', -1) + 1);
        $this->headers[] = "Content-Disposition: attachment; filename=$this->filename";
    }

    public function copyBackupToExternalStorage()
    {
        if (strcasecmp(SystemConfig::getValue('sExternalBackupType'), 'WebDAV') == 0) {
            if (SystemConfig::getValue('sExternalBackupUsername') && SystemConfig::getValue('sExternalBackupPassword') && SystemConfig::getValue('sExternalBackupEndpoint')) {
                LoggerUtils::getAppLogger()->debug("Start remote backup");

                $this->getDatabaseBackup();

                $this->credentials = SystemConfig::getValue('sExternalBackupUsername') . ':' . SystemConfig::getValue('sExternalBackupPassword');
                $this->filesize = filesize($this->saveTo);

                LoggerUtils::getAppLogger()->debug("file size : " . $this->filesize);

                $fh = fopen($this->saveTo, 'r');
                $this->remoteUrl = SystemConfig::getValue('sExternalBackupEndpoint');
                $ch = curl_init($this->remoteUrl . $this->filename);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
                curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_INFILE, $fh);
                curl_setopt($ch, CURLOPT_INFILESIZE, $this->filesize);
                $this->result = curl_exec($ch);
                fclose($fh);

                LoggerUtils::getAppLogger()->debug("Return : " . $this->result);

                return $this;
            } else {
                throw new \Exception('WebDAV backups are not correctly configured.  Please ensure endpoint, username, and password are set', 500);
            }
        } elseif (strcasecmp(SystemConfig::getValue('sExternalBackupType'), 'Local') == 0) {
            try {
                $this->getDatabaseBackup();
                exec('mv ' . $this->saveTo . ' ' . SystemConfig::getValue('sExternalBackupEndpoint'));

                return $this;
            } catch (\Exception $exc) {
                throw new \Exception('The local path ' . SystemConfig::getValue('sExternalBackupEndpoint') . ' is not writeable.  Unable to store backup.', 500);
            }
        }
    }

    public function run()
    {
        if ($this->params->iRemote == 0) {
            // we're in a normal backup
            LoggerUtils::getAppLogger()->debug("Start backup : run");
            $this->getDatabaseBackup();
            LoggerUtils::getAppLogger()->debug("End backup : run");

            // everything is OK
            $this->result = 1;

            return $this;
        } else {
            // we're in the case of a remote backup
            LoggerUtils::getAppLogger()->debug("Start remote backup : run");
            $this->copyBackupToExternalStorage();
            LoggerUtils::getAppLogger()->debug("End remote backup : run");

            // everything is done in the : copyBackupToExternalStorage
            //$this->result = 1;

            return $this;
        }
    }
}
