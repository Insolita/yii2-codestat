<?php
return [
    'id' => 'app-test',
    'basePath' => dirname(dirname(__DIR__)),
    'sourceLanguage' => 'en-US',
    'timeZone'            => 'Europe/Moscow',
    'language'       => 'ru',
    'charset'        => 'utf-8',
    'bootstrap'=>['log'],
    'aliases'=>[
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'container'=>[
        'definitions'=>[],
        'singletons'=>[]
    ],
    'modules'=>[
    ],
    'params'=>[]
];