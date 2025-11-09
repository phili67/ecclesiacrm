<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\LocaleInfo;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;

class SidebarSystemSettingsController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function saveSettings(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->type)) {
            $type = $input->type;
            $new_value = $input->new_value;

            $type = array_filter($type, function($value) {
                 return !is_null($value);
            });

            $new_value = array_filter($new_value, function($value) {
                 return !is_null($value);
            });

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

            return $response->withJson(['status' => "success"]);

        }

        return $response->withJson(['status' => "failed"]);
    }
}
