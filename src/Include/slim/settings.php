<?php

use EcclesiaCRM\dto\SystemConfig;

if (SystemConfig::getValue('sLogLevel') == 0) {
  return [
    'settings' => [
      'displayErrorDetails' => false, // set to false in production
    ],
  ];
} else {
  return [
    'settings' => [
      'displayErrorDetails' => true, // set to false in production
    ],
  ];
}
