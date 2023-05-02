<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/06
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\LocaleInfo;

use Slim\Views\PhpRenderer;

class VIEWSystemSettingsController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderSettings (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/sidebar/');

        if (!SessionUser::getUser()->isAdmin()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'systemsettings.php', $this->argumentsSystemSettingsArray());
    }

    public function argumentsSystemSettingsArray ()
    {
        $saved = false;

        // Save Settings
        if (isset($_POST['save'])) {
            $new_value = $_POST['new_value'];
            $type = $_POST['type'];
            ksort($type);
            reset($type);

            $iHTMLHeaderRow = SystemConfig::getConfigItem('sHeader')->getId();

            while ($current_type = current($type)) {
                $id = key($type);
                // Filter Input
                if ($id == $iHTMLHeaderRow) {  // Special handling of header value so HTML doesn't get removed
                    $value = InputUtils::FilterHTML($new_value[$id]);
                } elseif ($current_type == 'text' || $current_type == 'textarea' || $current_type == 'password') {
                    $value = InputUtils::FilterString($new_value[$id]);
                } elseif ($current_type == 'number') {
                    $value = InputUtils::FilterFloat($new_value[$id]);
                } elseif ($current_type == 'date') {
                    $value = InputUtils::FilterDate($new_value[$id]);
                } elseif ($current_type == 'json') {
                    $value = $new_value[$id];
                } elseif ($current_type == 'choice') {
                    $value = InputUtils::FilterString($new_value[$id]);
                } elseif ($current_type == 'ajax') {
                    $value = InputUtils::FilterString($new_value[$id]);
                } elseif ($current_type == 'boolean') {
                    if ($new_value[$id] != '1') {
                        $value = '';
                    } else {
                        $value = '1';
                    }
                }

                // If changing the locale, translate the menu options
                if ($id == 39 && $value != Bootstrapper::GetCurrentLocale()->getLocale()) {
                    $localeInfo = new LocaleInfo($value);
                    setlocale(LC_ALL, $localeInfo->getLocale());
                    $aLocaleInfo = $localeInfo->getLocaleInfo();
                }

                if ($id == 65 && !(in_array($value, timezone_identifiers_list()))) {
                    $value = date_default_timezone_get();
                }

                SystemConfig::setValueById($id, $value);
                next($type);
            }
            $saved = true;
        }

        //Set the page title
        $sPageTitle = _("General Settings");

        $sRootDocument  = SystemURLs::getDocumentRoot();

        $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
            'sRootDocument' => $sRootDocument,
            'sPageTitle'    => $sPageTitle,
            'saved'         => $saved
        ];
        return $paramsArguments;
    }

    public function renderSettingsMode (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/sidebar/');

        if (!SessionUser::getUser()->isAdmin()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $sMode = $args['mode'];

        return $renderer->render($response, 'systemsettings.php', $this->argumentsSystemSettingsModeArray($sMode));
    }
    
    public function argumentsSystemSettingsModeArray ($sMode)
    {
        $saved = false;

        //Set the page title
        $sPageTitle = _("General Settings");

        $sRootDocument  = SystemURLs::getDocumentRoot();

        $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
            'sRootDocument' => $sRootDocument,
            'sPageTitle'    => $sPageTitle,
            'Mode'         => $sMode
        ];
        
        return $paramsArguments;
    }
    
}
