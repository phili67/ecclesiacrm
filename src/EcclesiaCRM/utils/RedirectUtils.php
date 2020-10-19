<?php
namespace EcclesiaCRM\Utils;
use EcclesiaCRM\dto\SystemURLs;

class RedirectUtils
{
    /**
     * Convert a relative URL into an absolute URL and redirect the browser there.
     * @param string $sRelativeURL
     * @throws \Exception
     */
    public static function Redirect($sRelativeURL)
    {
        if (substr($sRelativeURL, 0,1) != "/") {
            $sRelativeURL = "/" . $sRelativeURL;
        }
        if (substr($sRelativeURL, 0, strlen(SystemURLs::getRootPath())) != SystemURLs::getRootPath()) {
            $finalLocation = SystemURLs::getRootPath() . $sRelativeURL;
        } else {
            $finalLocation = $sRelativeURL;
        }
        header('Location: ' . $finalLocation);
        exit;
    }

    // Convert a relative URL into an absolute URL and return absolute URL.
    public static function RedirectURL($sRelativeURL)
    {
        // Test if file exists before redirecting.  May need to remove
        // query string first.
        $iQueryString = strpos($sRelativeURL, '?');
        if ($iQueryString) {
            $sPathExtension = mb_substr($sRelativeURL, 0, $iQueryString);
        } else {
            $sPathExtension = $sRelativeURL;
        }

        // The idea here is to get the file path into this form:
        //     $sFullPath = $sDocumentRoot . $sRootPath . $sPathExtension
        // The Redirect URL is then in this form:
        //     $sRedirectURL = $sRootPath . $sPathExtension
        $sFullPath = str_replace('\\', '/', SystemURLs::getDocumentRoot().'/'.$sPathExtension);

        // With the query string removed we can test if file exists
        if (file_exists($sFullPath) && is_readable($sFullPath)) {
            return SystemURLs::getRootPath().'/'.$sRelativeURL;
        } else {
            $sErrorMessage = 'Fatal Error: Cannot access file: '.$sFullPath."<br>\n"
          ."\$sPathExtension = $sPathExtension<br>\n"
          ."\$sDocumentRoot = ".SystemURLs::getDocumentRoot()."<br>\n"
          .'$sRootPath = ' .SystemURLs::getRootPath()."<br>\n";

            die($sErrorMessage);
        }
    }

    public static function SecurityRedirect($missingRole) {
        LoggerUtils::getAppLogger()->info("Security Redirect Request due to Role: " . $missingRole);
        self::Redirect("v2/dashboard");
    }

}
