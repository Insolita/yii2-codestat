Yii2 Code Statistic
===================
[![Build Status](https://travis-ci.org/Insolita/yii2-codestat.svg?branch=master)](https://travis-ci.org/Insolita/yii2-codestat)

Installation
============
Either run

```
composer require -dev insolita/yii2-codestat:~0.0.1
```
or add

```
"insolita/yii2-codestat": "~0.0.1"
```
in require-dev section of your `composer.json` file.

Basic Usage
-----------

Add in console configuration file, in section modules

```
php
'modules'=>[
 ....
        'codestat'=>[
            'class'=>CodeStatModule::class,
            'scanTargets' => ['@backend/','@common/','@frontend/','@console/'],
            'exceptTargets' => ['*config*','vendor*','*web/*','*runtime/*','*views/*','*tests/*'],
        ]
    ],

```

 *scanTargets*   - array of path, or path aliases that will be scanned recursively
 *exceptTargets* - array of path patterns for excluding

For checking whole list of files that will be processed, run
```
./yii codestat/default/list-files
```

For statistic output run
```
./yii codestat
```

Advanced Usage
--------------

###Custom Class Grouping Rules


