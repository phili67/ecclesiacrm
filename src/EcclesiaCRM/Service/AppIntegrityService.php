<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemConfig;

class ApplicationPrerequisite {
  private $_name;
  private $_message;

  function __construct($name, $_message) {
    $this->_name = $name;
    $this->_message = $_message;
  }

  public function getName() : string
  {
    return $this->_name;
  }

  public function getMessage() : string
  {
    return $this->_message;
  }

  public function getLink(): string
  {
    $language = SystemConfig::getValue('sLanguage');

    $firsLetters = substr($language, 0,2);

    if ($firsLetters == "fr") {
      return "https://www.docs.ecclesiacrm.com/fr/user-guide/doc-installation/doc-requirement/";
    } else {
      return "https://www.docs.ecclesiacrm.com/en/user-guide/doc-installation/doc-requirement/";
    }
  }
}

class AppIntegrityService
{
  private static $IntegrityCheckDetails;

  public static function verifyApplicationIntegrity() : array
  {
    if (AppIntegrityService::$IntegrityCheckDetails === NULL) {
      $signatureFile = SystemURLs::getDocumentRoot() . '/signatures.json';
      $signatureFailures['CRM'] = [];
      if (file_exists($signatureFile)) {
        $signatureData = json_decode(file_get_contents($signatureFile));
        if (sha1(json_encode($signatureData->files, JSON_UNESCAPED_SLASHES)) == $signatureData->sha1) {
          foreach ($signatureData->files as $file) {
            $currentFile = SystemURLs::getDocumentRoot() . '/' . $file->filename;
            if (file_exists($currentFile)) {
              $actualHash = sha1_file($currentFile);
              if ($actualHash != $file->sha1) {
                $signatureFailures['CRM'][] = ['filename' => $file->filename, 'status' => 'Hash Mismatch', 'expectedhash' => $file->sha1, 'actualhash' => $actualHash];
              }
            } else {
                $signatureFailures['CRM'][] = ['filename' => $file->filename, 'status' => 'File Missing'];
            }
          }
        } else {
          return ['status' => 'failure', 'message' => _('Signature definition file signature failed validation'), 'files' => []];
        }
      } else {
        return ['status' => 'failure', 'message' => _('Signature definition File Missing'), 'files' => []];
      }

      $pluginsIntegrity = AppIntegrityService::verifyPluginsIntegrity([]);

      if (count($signatureFailures['CRM']) > 0 or $pluginsIntegrity['status'] == 'failure') {
        if (array_key_exists('files', $pluginsIntegrity)) {
            $signatureFailures['PLUGINS'] = $pluginsIntegrity['files'];
        }
        AppIntegrityService::$IntegrityCheckDetails = ['status' => 'failure', 'message' => _('One or more files failed signature validation'), 'files' => $signatureFailures];
      } else {
        AppIntegrityService::$IntegrityCheckDetails = ['status' => 'success'];
      }
    }

    return AppIntegrityService::$IntegrityCheckDetails;
  }

  public static function getIntegrityCheckStatus(): string
  {
      if (AppIntegrityService::verifyApplicationIntegrity()['status'] === 'failure') {
          return _('Failed');
      }

      return _('Passed');
  }

  public static function getIntegrityCheckMessage(): string
  {
      if (AppIntegrityService::verifyApplicationIntegrity()['status'] === 'failure') {
          return AppIntegrityService::verifyApplicationIntegrity()['message'];
      }

      return _('Passed');
  }

  public static function getFilesFailingIntegrityCheck(): array
  {

      if (AppIntegrityService::verifyApplicationIntegrity()['status'] === 'failure') {
          return AppIntegrityService::verifyApplicationIntegrity()['files'];
      }

      return AppIntegrityService::$IntegrityCheckDetails;
  }


  public static function verifyPluginsIntegrity($signatureFailures = []): array
  {
      $plugins = new \DirectoryIterator(SystemURLs::getDocumentRoot() . '/Plugins/');

      foreach ($plugins as $plugin) {
          if ($plugin->isDir() && !$plugin->isDot()) {
              $pluginName = $plugin->getFilename();
              $signatureFile = SystemURLs::getDocumentRoot() . '/Plugins/' . $pluginName . '/signatures.json';

              if (file_exists($signatureFile)) {
                  $signatureData = json_decode(file_get_contents($signatureFile));
                  if (sha1(json_encode($signatureData->files, JSON_UNESCAPED_SLASHES)) == $signatureData->sha1) {
                      foreach ($signatureData->files as $file) {
                          $currentFile = SystemURLs::getDocumentRoot() . '/' . $file->filename;
                          if (file_exists($currentFile)) {
                              $actualHash = sha1_file($currentFile);
                              if ($actualHash != $file->sha1) {
                                  if ( !array_key_exists($pluginName, $signatureFailures)) {
                                      $signatureFailures[$pluginName] = [];
                                  }
                                  array_push($signatureFailures[$pluginName], ['filename' => $file->filename, 'status' => 'Hash Mismatch', 'expectedhash' => $file->sha1, 'actualhash' => $actualHash]);
                              }
                          } else {
                              array_push($signatureFailures[$pluginName], ['filename' => $file->filename, 'status' => 'File Missing']);
                          }
                      }
                  } else {
                      return ['status' => 'failure', 'message' => _('Signature definition file signature failed validation'), 'files' => []];
                  }
              } else {
                  return ['status' => 'failure', 'message' => _('Signature definition File Missing'), 'files' => []];
              }
          }
      }

      if (count($signatureFailures) > 0) {
          return ['status' => 'failure', 'message' => _('One or more files failed signature validation'), 'files' => $signatureFailures];
      } else {
          return ['status' => 'success'];
      }
  }



  private static function testImagesWriteable(): bool
  {
    return is_writable(SystemURLs::getDocumentRoot().'/Images/') &&
            is_writable(SystemURLs::getDocumentRoot().'/Images/Family') &&
            is_writable(SystemURLs::getDocumentRoot().'/Images/Person');

  }

  private static function testPrivateWriteable(): bool
  {
    return is_writable(SystemURLs::getDocumentRoot().'/private/') &&
            is_writable(SystemURLs::getDocumentRoot().'/private/userdir');

  }

  private static function testDataWriteable(): bool
  {
    return is_writable(SystemURLs::getDocumentRoot().'/data/');
  }

  public static function getApplicationPrerequisites() : array
  {
    # this code avoid the problem with ondrej package version
    $sys_version = explode("-",explode ("+",PHP_VERSION)[0]);

    $prerequisites = array(
      new ApplicationPrerequisite('PHP 8.0+',version_compare($sys_version[0], '8.0.0', '>=')),
      new ApplicationPrerequisite('PCRE Support'                              , function_exists('preg_match')),
      new ApplicationPrerequisite('UTF-8 Support'                             , @preg_match('/^.$/u', 'A') && @preg_match('/^\pL$/u', 'A')),
      new ApplicationPrerequisite('Multibyte Encoding'                        , extension_loaded('mbstring')),
      new ApplicationPrerequisite('imagick'                                   , extension_loaded('imagick')),
      new ApplicationPrerequisite('PHP Phar'                                  , extension_loaded('phar')),
      new ApplicationPrerequisite('PHP Session'                               , extension_loaded('session')),
      new ApplicationPrerequisite('PHP XML'                                   , extension_loaded('xml')),
      new ApplicationPrerequisite('PHP EXIF'                                  , extension_loaded('exif')),
      new ApplicationPrerequisite('PHP iconv'                                 , extension_loaded('iconv')),
      new ApplicationPrerequisite('Open SSL'                                  , extension_loaded('openssl')),
      new ApplicationPrerequisite('Mod Rewrite'                               , AppIntegrityService::hasModRewrite('mod_rewrite')),
      new ApplicationPrerequisite('GD Library for image manipulation'         , (extension_loaded('gd') && function_exists('gd_info'))),
      new ApplicationPrerequisite('FileInfo Extension for image manipulation' , extension_loaded('fileinfo')),
      new ApplicationPrerequisite('cURL'                                      , function_exists('curl_version')),
      new ApplicationPrerequisite('locale gettext'                            , function_exists('bindtextdomain')),
      new ApplicationPrerequisite('Include/Config file is writeable'          , is_writable(SystemURLs::getDocumentRoot().'/Include/') || is_writable(SystemURLs::getDocumentRoot().'/Include/Config.php')),
      new ApplicationPrerequisite('Images directory is writeable'             , AppIntegrityService::testImagesWriteable()),
      new ApplicationPrerequisite('private directory is writeable (DAV)'      , AppIntegrityService::testPrivateWriteable()),
      new ApplicationPrerequisite('data directory is writeable (DAV)'         , AppIntegrityService::testDataWriteable())
    );

    return $prerequisites;
  }

  public static function getUnmetPrerequisites(): array
  {
    $unmet = [];
    foreach (AppIntegrityService::getApplicationPrerequisites() as $prerequisite) {
          if (!$prerequisite->getMessage()) {
              array_push($unmet,[$prerequisite->getName(),$prerequisite->getMessage()]);
          }
      }
    return $unmet;
  }

  public static function arePrerequisitesMet(): bool
  {
    $prerequisites = AppIntegrityService::getApplicationPrerequisites();
    foreach ($prerequisites as $prerequisiteMet)
    {
      if (!$prerequisiteMet->getMessage())
      {
        return false;
      }
    }
    return true;
  }

  public static function hasModRewrite(): bool
  {
      // ChurchCRM code
      // mod_rewrite can be tricky to detect properly.
      // First check if it's loaded as an apache module
      // Second check (if supported) if apache cli lists the module
      // Third, finally try calling a known invalid URL on this installation
      //   and check for a header that would only be present if .htaccess was processed.
      //   This header comes from index.php (which is the target of .htaccess for invalid URLs)
      //
      // Put in the <VirtualHost *:443> part
      // <IfModule mod_env.c>
      //    ## Tell PHP that the mod_rewrite module is ENABLED.
      //    SetEnv HTTP_MOD_REWRITE On
      // </IfModule>
      //
      //
      //

      $check = false;
      $logger = LoggerUtils::getAppLogger();

      if (isset($_SERVER['HTTP_MOD_REWRITE'])) {
          $logger->debug("Webserver configuration has set mod_rewrite variable: {$_SERVER['HTTP_MOD_REWRITE']}");
          $check = strtolower($_SERVER['HTTP_MOD_REWRITE']) === 'on';
      } elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') !== false) {
          $logger->debug('PHP is running through Apache; looking for mod_rewrite');
          if (function_exists('apache_get_modules')) {
              $check = in_array('mod_rewrite', apache_get_modules());
          }
          $logger->debug("Apache mod_rewrite check status: $check");
          if (empty($check)) {
              if (!empty(shell_exec('/usr/sbin/apachectl -M | grep rewrite'))) {
                  $logger->info('Found rewrite module enabled using apachectl');
                  $check = true;
              }
          }
      } else {
          $logger->debug('PHP is not running through Apache');
      }

      if ($check === false) {
          $logger->debug('Previous rewrite checks failed');
          if (function_exists('curl_version')) {
              $ch = curl_init();
              $request_url_parser = parse_url($_SERVER['HTTP_REFERER']);
              $request_scheme = $request_url_parser['scheme'] ?? 'http';
              $rewrite_chk_url = $request_scheme . '://' . $_SERVER['SERVER_ADDR'] . SystemURLs::getRootPath() . '/INVALID';
              $logger->debug("Testing CURL loopback check to: $rewrite_chk_url");
              curl_setopt($ch, CURLOPT_URL, $rewrite_chk_url);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($ch, CURLOPT_HEADER, 1);
              curl_setopt($ch, CURLOPT_NOBODY, 1);
              $output = curl_exec($ch);
              curl_close($ch);
              $headers = [];
              $data = explode("\n", $output);
              $headers['status'] = $data[0];
              array_shift($data);
              foreach ($data as $part) {
                  if (strpos($part, ':')) {
                      $middle = explode(':', $part);
                      $headers[trim($middle[0])] = trim($middle[1]);
                  }
              }
              $check = $headers['CRM'] === 'would redirect';
              $logger->debug('CURL loopback check headers observed: ' . ($check ? 'true' : 'false'));
          }
      }

      return $check;
  }
}
