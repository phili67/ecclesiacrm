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
use EcclesiaCRM\utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;

use PharData;
use Ifsnop\Mysqldump\Mysqldump;
use Propel\Runtime\Propel;
use ZipArchive;
use Defuse\Crypto\File;

abstract class BackupType
{
    // archive type
    const GZSQL = 0;
    const Zip = 1;
    const SQL = 2;
    const FullBackup = 3;
    const FullBackupEncrypted = 4;
}

class JobBase
{

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
        $this->backupRoot = SystemURLs::getDocumentRoot() . '/tmp_attach';
        $this->TempFolder = $this->backupRoot . "/". $contentDir . "/";
        LoggerUtils::getAppLogger()->debug("Removing temp folder tree at " . $this->TempFolder);
        $this->DeleteTempFolder();
        LoggerUtils::getAppLogger()->debug("Creating temp folder at " . $this->TempFolder);
        mkdir($this->TempFolder, 0750, true);
        LoggerUtils::getAppLogger()->debug("Temp folder created");
        return $this->TempFolder;
    }

    protected function DeleteTempFolder()
    {
        MiscUtils::delTree($this->TempFolder);
    }
}

class RestoreBackup extends JobBase
{
    /**
     *
     * @var string
     */
    protected $file;
    /**
     *
     * @var array
     */
    protected $Messages;

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
     * @var bool
     */
    protected $gpg_encrypted = false;

    /**
     *
     * @bool
     */

    protected $gpg_forgotten_password = false;

    /**
     *
     * @bool
     */

    protected $UpgradeStatus;

    /**
     *
     * @string
     */
    protected $restorePassword;


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
        $this->restorePassword = InputUtils::FilterString($_POST['restorePassword']);

        $path = $file['name'];
        $type = pathinfo($path, PATHINFO_EXTENSION);
        if ($type == "gpg") {// in the case of a GPG encryption
            $this->gpg_encrypted = true;
            if (strlen($this->restorePassword) > 0) {
                // we get now the real file ....
                $path = mb_substr($this->file['name'], 0, strlen($this->file['name']) - 4);
            } else {
                // we are in a case of a problem
                $this->gpg_forgotten_password = true;
                throw new \Exception(_("GPG Backup need a password to work") . ": " . $this->file['name']);
            }
        }

        $this->type = pathinfo($path, PATHINFO_EXTENSION);
        $this->type2 = pathinfo(mb_substr($path, 0, strlen($path) - 3), PATHINFO_EXTENSION);

        $this->backupDir = $this->CreateEmptyTempFolder('EcclesiaCRMRestores');
    }

    private function DecryptBackupFileGPG()
    {
        LoggerUtils::getAppLogger()->info("Decrypting backup file: " . $this->file);
        putenv('GNUPGHOME=/tmp');
        $this->encryptCommand = "gpg --batch --passphrase " . $this->restorePassword . " " . $this->uploadedFileDestination;
        system($this->encryptCommand);
        LoggerUtils::getAppLogger()->info("Finished decrypting backup file");

        // now the path is without gpg extension
        $this->uploadedFileDestination = $path = mb_substr($this->uploadedFileDestination, 0, strlen($this->uploadedFileDestination) - 4);
    }

    private function DecryptBackupFileInternal()
    {
        LoggerUtils::getAppLogger()->info("Decrypting file: " . $this->uploadedFileDestination);
        $tempfile = $this->uploadedFileDestination . "temp";

        try {
            File::decryptFileWithPassword($this->uploadedFileDestination, $tempfile, $this->restorePassword);
            rename($tempfile, $this->uploadedFileDestination);
            LoggerUtils::getAppLogger()->info("File decrypted");
        } catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
            if ($ex->getMessage() == 'Bad version header.') {
                LoggerUtils::getAppLogger()->info("Bad version header; this file probably wasn't encrypted");
            } else {
                LoggerUtils::getAppLogger()->error($ex->getMessage());
                throw $ex;
            }
        }
    }



    private function RestoreArchive_SQL_GZ()
    {
        $connection = Propel::getConnection();

        $SQLfile = $this->backupDir . str_replace('.gz', '', $this->file['name']);
        file_put_contents($SQLfile, gzopen($this->uploadedFileDestination, r));
        SQLUtils::sqlImport($SQLfile, $connection);
    }

    private function RestoreArchive_ZIP()
    {
        $connection = Propel::getConnection();

        $zip = new ZipArchive;
        $SQLfile = $this->backupDir . str_replace('.zip', '', $this->file['name']);
        if ($zip->open($this->uploadedFileDestination) === TRUE) {
            $zip->extractTo($this->backupDir);
            $zip->close();
            SQLUtils::sqlImport($this->backupDir . "EcclesiaCRM-Database.sql", $connection);
        } else {
            throw new \Exception(_("Impossible to open") . $this->saveTo);
        }
    }

    private function RestoreArchive_SQL()
    {
        $connection = Propel::getConnection();

        SQLUtils::sqlImport($this->uploadedFileDestination, $connection);
    }

    private function RestoreFullArchive_TAR_GZ()
    {
        $connection = Propel::getConnection();

        $phar = new PharData($this->uploadedFileDestination);
        $phar->extractTo($this->backupDir);
        $SQLfile = $this->backupDir . "/EcclesiaCRM-Database.sql";
        if (file_exists($SQLfile)) {
            SQLUtils::sqlImport($SQLfile, $connection);
            // restore the Images folder
            MiscUtils::delTree(SystemURLs::getDocumentRoot() . '/Images');
            FileSystemUtils::recursiveCopyDirectory($this->backupDir . '/Images/', SystemURLs::getImagesRoot());
            // restore the Webdav private folder
            MiscUtils::delTree(SystemURLs::getDocumentRoot() . '/private');
            FileSystemUtils::recursiveCopyDirectory($this->backupDir . '/private/', SystemURLs::getEDrivePrivateRoot());
            // restore the Webdav public folder
            MiscUtils::delTree(SystemURLs::getDocumentRoot() . '/public');
            FileSystemUtils::recursiveCopyDirectory($this->backupDir . '/public/', SystemURLs::getEDrivePublicRoot());
        } else {
            FileSystemUtils::recursiveRemoveDirectory($this->backupDir, true);
            throw new \Exception(_("Backup archive does not contain a database") . ": " . $this->file['name']);
        }
    }

    public function run()
    {
        $this->uploadedFileDestination = $this->backupDir . $this->file['name'];

        move_uploaded_file($this->file['tmp_name'], $this->uploadedFileDestination);

        if ($this->restorePassword == true) {
            if ($this->gpg_encrypted == true) {
                // we've have to decode the archive
                $this->DecryptBackupFileGPG();
            } else {
                // the way of ecclesiacrm.com
                $this->DecryptBackupFileInternal();
            }
        }

        if ($this->type == 'gz') {
            if ($this->type2 == 'tar') {
                $this->RestoreFullArchive_TAR_GZ();
            } elseif ($this->type2 == 'sql') {
                $this->RestoreArchive_SQL_GZ();
            }
        } elseif ($this->type == 'sql') {
            $this->RestoreArchive_SQL();
        } elseif ($this->type == 'zip') {
            $this->RestoreArchive_ZIP();
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
     * @string
     */
    protected $saveTo;
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
    public $result = false;
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

    /**
     * CreateBackup constructor.
     * @param null $params
     * @throws \Exception
     */

    public $error = "";

    protected $SQLFile;

    public function __construct($params=null)
    {
        //MiscUtils::requireUserGroupMembership('bAdmin');
        $this->backupDir = $this->CreateEmptyTempFolder('EcclesiaCRMBackups');

        if ($params == null) {
            // we're in the case of a scheduled backup
            $this->params = new class {
                public $iArchiveType=BackupType::FullBackup;
                public $bEncryptBackup=false;
                public $iRemote=1;
            };
        } else {
            $this->params = $params;
        }

        $this->headers = [];
        $this->saveTo = $this->backupDir . "EcclesiaCRM-" . date(SystemConfig::getValue("sDateFilenameFormat"));
        $this->SQLFile = $this->backupDir . "EcclesiaCRM-Database.sql";

    }

    private function GZBackupFile()
    {
        LoggerUtils::getAppLogger()->info("GZ backup file: " . $this->saveTo);
        $this->saveTo .= '.sql.gz';
        $gzf = gzopen($this->saveTo, 'w6');
        gzwrite($gzf, file_get_contents($this->SQLFile));
        gzclose($gzf);
        LoggerUtils::getAppLogger()->info("GZ backup file");
    }

    private function ZipBackupFile()
    {
        LoggerUtils::getAppLogger()->info("Zip backup file: " . $this->saveTo);
        $zip = new \ZipArchive();
        $this->saveTo .= '.sql.zip';

        if ($zip->open($this->saveTo, ZipArchive::CREATE) === TRUE) {
            $zip->addFromString('EcclesiaCRM-Database.sql', file_get_contents($this->SQLFile));
            $zip->close();
        } else {
            throw new \Exception(_("Impossible to open") . $this->saveTo);
        }
        LoggerUtils::getAppLogger()->info("Zip backup file");
    }

    private function FullBackupFile()
    {
        LoggerUtils::getAppLogger()->info("Full backup file: " . $this->saveTo);
        $this->saveTo .= '.tar';
        $phar = new \PharData($this->saveTo);
        $phar->startBuffering();
        // the sql DB
        $phar->addFile($this->SQLFile, 'EcclesiaCRM-Database.sql');
        // the images files
        $imageFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(SystemURLs::getImagesRoot()));
        foreach ($imageFiles as $imageFile) {
            if (!$imageFile->isDir()) {
                $localName = str_replace(SystemURLs::getDocumentRoot() . '/', '', $imageFile->getRealPath());
                $phar->addFile($imageFile->getRealPath(), $localName);
            }
        }

        // the eDrive(s) for all the users
        // 1) the private Webdav folder
        $privateFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(SystemURLs::getEDrivePrivateRoot(),\FilesystemIterator::SKIP_DOTS));
        foreach ($privateFiles as $privateFile) {
            if ($privateFile->isFile() && $privateFile->getBasename() === '.DS_Store' || $privateFile->isLink()) {
                continue;
            }
            if ($privateFile->isFile() && $privateFile->getBasename() == "public") {
                // the symlink public is forbidden.
                continue;
            }
            if (!$privateFile->isDir()) {
                $localName = str_replace(SystemURLs::getDocumentRoot() . '/', '', $privateFile->getRealPath());
                $phar->addFile($privateFile->getRealPath(), $localName);
            }
        }
        // 2) the public Webdav folder
        $publicFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(SystemURLs::getEDrivePublicRoot(),\FilesystemIterator::SKIP_DOTS));
        foreach ($publicFiles as $publicFile) {
            if ($publicFile->isLink()) {
                continue;
            }

            if (!$publicFile->isDir()) {
                $localName = str_replace(SystemURLs::getDocumentRoot() . '/', '', $publicFile->getRealPath());
                $phar->addFile($publicFile->getRealPath(), $localName);
            }
        }
        $phar->stopBuffering();
        $phar->compress(\Phar::GZ);
        unlink($this->saveTo);
        $this->saveTo .= '.gz';
        LoggerUtils::getAppLogger()->info("Finished Full backup file");
    }

    private function EncryptBackupFileGPG()
    {
        LoggerUtils::getAppLogger()->info("Encrypting backup file: " . $this->saveTo);
        putenv('GNUPGHOME=/tmp');
        $this->encryptCommand = "echo " . $this->params->password . " | gpg -q -c --batch --no-tty --passphrase-fd 0 " . $this->saveTo;
        $this->saveTo .= '.gpg';
        system($this->encryptCommand);
        LoggerUtils::getAppLogger()->info("Finished encrypting backup file");
    }

    private function EncryptBackupFileInternal()
    {
        LoggerUtils::getAppLogger()->info("Encrypting backup file: " . $this->saveTo);
        $tempfile = $this->saveTo . "temp";
        rename($this->saveTo, $tempfile);
        File::encryptFileWithPassword($tempfile, $this->saveTo, $this->params->password);
        LoggerUtils::getAppLogger()->info("Finished encrypting backup file");
    }

    private function getDatabaseBackup()
    {
        try {
            $dump = new Mysqldump(Bootstrapper::GetDSN(), Bootstrapper::GetUser(), Bootstrapper::GetPassword(), ['add-drop-table' => true]);
            $dump->start($this->SQLFile);
        } catch (\Exception $e) {
            // this problem came from : email_list and email_count tables
            // follow : https://docs.ecclesiacrm.com/en/user-guide/doc-admin/doc-tips/doc-restauration-bdd-bug/
            throw new \Exception("Unable to create backup archive at " . $this->SQLFile . " error : ".$e->getCode(). " message : " . $e->getMessage(), 500);
        }

        switch ($this->params->iArchiveType) {
            case BackupType::GZSQL: // The user wants a gzip'd SQL file.
                $this->GZBackupFile();
                break;
            case BackupType::Zip:
                $this->ZipBackupFile();
                break;
            case BackupType::SQL : //The user wants a plain ol' SQL file
                $this->saveTo .= '.sql';
                rename($this->SQLFile, $this->saveTo);
                break;
            case BackupType::FullBackup : //the user wants a .tar.gz file
                $this->FullBackupFile();
                break;
        }


        if ($this->params->bEncryptBackup) {  //the user has selected an encrypted backup
            LoggerUtils::getAppLogger()->info("Encrypting backup file: " . SystemConfig::getValue('sPGP'));
            if (strtolower (SystemConfig::getValue('sPGP')) == "gpg") {
                $this->EncryptBackupFileGPG();
            } else {
                $this->EncryptBackupFileInternal();
            }

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

                $fh = fopen($this->saveTo, 'r');
                $this->remoteUrl = SystemConfig::getValue('sExternalBackupEndpoint');

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $this->remoteUrl . basename($this->filename));
                curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
                curl_setopt($ch, CURLOPT_PUT, true);

                curl_setopt($ch, CURLOPT_INFILE, $fh);
                curl_setopt($ch, CURLOPT_INFILESIZE, $this->filesize);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary

                $this->error = curl_exec ($ch);
                fclose($fh);

                if ($this->error == "") {
                    $this->result = true;
                }

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
            $this->result = true;

            return $this;
        } else {
            // we're in the case of a remote backup
            LoggerUtils::getAppLogger()->debug("Start remote backup : run");
            $this->copyBackupToExternalStorage();
            LoggerUtils::getAppLogger()->debug("End remote backup : run");

            // everything is done in the : copyBackupToExternalStorage
            //$this->result = true;

            return $this;
        }
    }
}

class DownloadManager
{
    static function run($filename)
    {
        set_time_limit(0);
        $path = SystemURLs::getDocumentRoot() . "/tmp_attach/EcclesiaCRMBackups/$filename";
        if (file_exists($path)) {
            if ($fd = fopen($path, 'r')) {
                $fsize = filesize($path);
                $path_parts = pathinfo($path);
                $ext = strtolower($path_parts['extension']);
                switch ($ext) {
                    case 'gz':
                        header('Content-type: application/x-gzip');
                        header('Content-Disposition: attachment; filename="' . $path_parts['basename'] . '"');
                        break;
                    case 'tar.gz':
                        header('Content-type: application/x-gzip');
                        header('Content-Disposition: attachment; filename="' . $path_parts['basename'] . '"');
                        break;
                    case 'sql':
                        header('Content-type: text/plain');
                        header('Content-Disposition: attachment; filename="' . $path_parts['basename'] . '"');
                        break;
                    case 'gpg':
                        header('Content-type: application/pgp-encrypted');
                        header('Content-Disposition: attachment; filename="' . $path_parts['basename'] . '"');
                        break;
                    case 'zip':
                        header('Content-type: application/zip');
                        header('Content-Disposition: attachment; filename="' . $path_parts['basename'] . '"');
                        break;
                    // add more headers for other content types here
                    default:
                        header('Content-type: application/octet-stream');
                        header('Content-Disposition: filename="' . $path_parts['basename'] . '"');
                        break;
                }
                header("Content-length: $fsize");
                header('Cache-control: private'); //use this to open files directly
                while (!feof($fd)) {
                    $buffer = fread($fd, 2048);
                    echo $buffer;
                }
            }
            fclose($fd);
            FileSystemUtils::recursiveRemoveDirectory(SystemURLs::getDocumentRoot() . '/tmp_attach/', true);
        }
    }
}
