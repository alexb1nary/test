<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$db2 = require __DIR__ . '/db2.php';
require_once(__DIR__.'/functions.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru-RU',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '66MJQXrDRr4nOiiXBs-jnWlWaPEQAkIQ',
        ],
        'cache' => [
            'class' => 'yii\caching\MemCache',
            'useMemcached' => true,
        ],
        'user' => [
            'identityClass' => 'app\models\Users',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'db2' => $db2,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer'
            ],

            'rules' => [
                '' => 'site/index',
                'search' => 'site/search',
                'tracks/id/<id>' => 'tracks/track-info',
                'tracks' => 'tracks/tracks',
                'videos/id/<id>' => 'videos/video-info',
                'videos' => 'videos/videos',
                'songs/id/<id>' => 'songs/song-info',
                'songs' => 'songs/songs',
                'albums/id/<id>' => 'albums/album-info',
                'albums' => 'albums/albums',
                'users' => 'users/users',
                'users/id/<id>' => 'users/users-show',
                'users/usages/id/<id>/edit' => 'users/usages-edit',
                'users/usages/id/<id>' => 'users/users-usages',
                'users/pass/id/<id>' => 'users/users-change-pass',
                'users/new' => 'users/users-new',
                'users/edit/id/<id>' => 'users/users-edit',
                'users/del/id/<id>' => 'users/users-delete',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'contact' => 'site/contact',
                'find' => 'site/find',
            ],
        ],

    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*', '192.168.123.48', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*', '192.168.123.48', '::1'],
    ];
}

return $config;
