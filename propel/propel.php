<?php
return [
    'propel' => [
        'database' => [
            'connections' => [
                'default' => [
                    'adapter'  => 'mysql',
                    'dsn'      => 'mysql:host=localhost;port=3306;dbname=ecclesiacrm',
                    'user'     => 'ecclesiacrm',
                    'password' => 'ecclesiacrm',
                    'settings' => [
                        'charset' => 'utf8',
                    ],
                ],
            ],
        ],
    ],
];
