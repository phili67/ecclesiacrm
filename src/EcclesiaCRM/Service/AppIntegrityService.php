<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\LoggerUtils;

class AppIntegrityService
{
  public static function verifyApplicationIntegrity()
  {
    $signatureFile = SystemURLs::getDocumentRoot() . '/signatures.json';
    $signatureFailures = [];
    if (file_exists($signatureFile)) {
      $signatureData = json_decode(file_get_contents($signatureFile));
      if (sha1(json_encode($signatureData->files, JSON_UNESCAPED_SLASHES)) == $signatureData->sha1) {
        foreach ($signatureData->files as $file) {
          $currentFile = SystemURLs::getDocumentRoot() . '/' . $file->filename;
          if (file_exists($currentFile)) {
            $actualHash = sha1_file($currentFile);
            if ($actualHash != $file->sha1) {
              array_push($signatureFailures, ['filename' => $file->filename, 'status' => 'Hash Mismatch', 'expectedhash' => $file->sha1, 'actualhash' => $actualHash]);
            }
          } else {
            array_push($signatureFailures, ['filename' => $file->filename, 'status' => 'File Missing']);
          }
        }
      } else {
        return ['status' => 'failure', 'message' => gettext('Signature definition file signature failed validation')];
      }
    } else {
      return ['status' => 'failure', 'message' => gettext('Signature definition File Missing')];
    }

    $pluginsIntegrity = AppIntegrityService::verifyPluginsIntegrity($signatureFailures);

    if (count($signatureFailures) > 0 or $pluginsIntegrity['status'] == 'failure') {
      if (array_key_exists('files', $pluginsIntegrity)) {
          $signatureFailures = $pluginsIntegrity['files'];
      }
      return ['status' => 'failure', 'message' => gettext('One or more files failed signature validation'), 'files' => $signatureFailures];
    } else {
      return ['status' => 'success'];
    }
  }

  public static function verifyPluginsIntegrity($signatureFailures = [])
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
                      return ['status' => 'failure', 'message' => gettext('Signature definition file signature failed validation')];
                  }
              } else {
                  return ['status' => 'failure', 'message' => gettext('Signature definition File Missing')];
              }
          }
      }

      if (count($signatureFailures) > 0) {
          return ['status' => 'failure', 'message' => gettext('One or more files failed signature validation'), 'files' => $signatureFailures];
      } else {
          return ['status' => 'success'];
      }
  }

  private static function testImagesWriteable()
  {
    return is_writable(SystemURLs::getDocumentRoot().'/Images/') &&
            is_writable(SystemURLs::getDocumentRoot().'/Images/Family') &&
            is_writable(SystemURLs::getDocumentRoot().'/Images/Person');

  }

  private static function testPrivateWriteable()
  {
    return is_writable(SystemURLs::getDocumentRoot().'/private/') &&
            is_writable(SystemURLs::getDocumentRoot().'/private/userdir');

  }

  private static function testDataWriteable()
  {
    return is_writable(SystemURLs::getDocumentRoot().'/data/');
  }

  public static function getApplicationPrerequisites()
  {
    # this code avoid the problem with ondrej package version
    $sys_version = explode("-",explode ("+",PHP_VERSION)[0]);

    $prerequisites = array(
      'PHP 8.0+'                                  => version_compare($sys_version[0], '8.0.0', '>='),
      'PCRE Support'                              => function_exists('preg_match'),
      'UTF-8 Support'                             => @preg_match('/^.$/u', 'A') && @preg_match('/^\pL$/u', 'A'),
      'Multibyte Encoding'                        => extension_loaded('mbstring'),
      'PHP Phar'                                  => extension_loaded('phar'),
      'PHP Session'                               => extension_loaded('session'),
      'PHP XML'                                   => extension_loaded('xml'),
      'PHP EXIF'                                  => extension_loaded('exif'),
      'PHP iconv'                                 => extension_loaded('iconv'),
      'Open SSL'                                  => extension_loaded('openssl'),
      //'Mcrypt'                                    => extension_loaded('mcrypt'),
      'Mod Rewrite'                               => AppIntegrityService::hasModRewrite('mod_rewrite'),
      'GD Library for image manipulation'         => (extension_loaded('gd') && function_exists('gd_info')),
      'FileInfo Extension for image manipulation' => extension_loaded('fileinfo'),
      'cURL'                                      => function_exists('curl_version'),
      'locale gettext'                            => function_exists('bindtextdomain'),
      'Include/Config file is writeable'          => is_writable(SystemURLs::getDocumentRoot().'/Include/') || is_writable(SystemURLs::getDocumentRoot().'/Include/Config.php'),
      'Images directory is writeable'             => AppIntegrityService::testImagesWriteable(),
      'private directory is writeable (DAV)'      => AppIntegrityService::testPrivateWriteable(),
      'data directory is writeable (DAV)'         => AppIntegrityService::testDataWriteable()
    );
    return $prerequisites;
  }

  public static function getUnmetPrerequisites()
  {
    $unmet = [];
    foreach (AppIntegrityService::getApplicationPrerequisites() as $prerequisite=>$status) {
          if (!$status) {
              array_push($unmet,$prerequisite);
          }
      }
    return $unmet;
  }

  public static function arePrerequisitesMet()
  {
    $prerequisites = AppIntegrityService::getApplicationPrerequisites();
    foreach ($prerequisites as $prerequisiteName => $prerequisiteMet)
    {
      if (!$prerequisiteMet)
      {
        return false;
      }
    }
    return true;
  }

  public static function hasApacheModule($module)
  {
      if (function_exists('apache_get_modules')) {
          return in_array($module, apache_get_modules());
      } else {        
        return self::is_mod_rewrite_enabled();
      }

      return false;
  }

  public static function is_mod_rewrite_enabled() {
    if ($_SERVER['HTTP_MOD_REWRITE'] == 'On') {
      return TRUE;
    } else {
      /*
          put this code in vhost ...

          <IfModule mod_env.c>
		          ## Tell PHP that the mod_rewrite module is ENABLED.
		          SetEnv HTTP_MOD_REWRITE On
	        </IfModule>
        */
        LoggerUtils::getAppLogger()->info("put this code in vhost ...

        <IfModule mod_env.c>
            ## Tell PHP that the mod_rewrite module is ENABLED.
            SetEnv HTTP_MOD_REWRITE On
        </IfModule>
        
        before :
        RewriteEngine  on");
        
      return FALSE;
    }
  }

  public static function hasModRewrite()
  {
      $check = AppIntegrityService::hasApacheModule('mod_rewrite');

      if (!$check && function_exists('shell_exec')) {
          $check = strpos(shell_exec('/usr/local/apache/bin/apachectl -l'), 'mod_rewrite') !== false;
      }

      return $check;
  }

}
