Yii2 Code Statistic
===================
[![Build Status](https://travis-ci.org/Insolita/yii2-codestat.svg?branch=master)](https://travis-ci.org/Insolita/yii2-codestat)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Insolita/yii2-codestat/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Insolita/yii2-codestat/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a2b84a39-bbae-4330-ae75-d464111f98ac/small.png)](https://insight.sensiolabs.com/projects/a2b84a39-bbae-4330-ae75-d464111f98ac)

![screenshot](codestat.png)

Installation
============

*NOTE*:

 - 1.x branch require yii>=2.0.13
 - 0.x branch for older yii versions

Either run

```
composer require -dev insolita/yii2-codestat:~1.0.1
```
or add

```
"insolita/yii2-codestat": "~1.0.1"
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
            'class'=>\insolita\codestat\CodeStatModule::class,
            'scanTargets' => ['@backend/','@common/','@frontend/','@console/'],
            'exceptTargets' => ['*config*','vendor*','*web/*','*runtime/*','*views/*','*tests/*'],
        ]
    ],

```

 **scanTargets**   - array of path, or path aliases that will be scanned recursively
 **exceptTargets** - array of path patterns for excluding

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

#### Custom Class Grouping Rules

You can extend or overwrite property 'groupRules', with supported formats
`'Group Name' => 'BaseParentClass'`
 where 'BaseParentClass' should by verified with (\ReflectionClass)->isSubclassOf();
or
```
'Group Name' => function(\ReflectionClass $reflection){
                     //Should return true if class valid for this group, otherwise false;
                }
```
Final example

```
php
'modules'=>[
 ....
        'codestat'=>[
            'class'=>\insolita\codestat\CodeStatModule::class,
             'groupRules' => [
                                'Jobs' => 'yii\queue\JobInterface',
                                'Handlers' => 'trntv\bus\interfaces\Handler::class',
                                'DTO' => function (\ReflectionClass $reflection) {
                                     return mb_strpos($reflection->getFileName(), 'Dto')!==false;
                                },
                                'All Tests' => function (\ReflectionClass $reflection) {
                                    return $reflection->isSubclassOf('\Codeception\Test\Unit')
                                        || StringHelper::endsWith($reflection->getName(), 'Cest');
                                },
                            ] + CodeStatModule::defaultRules(),
                    ],
        ]
    ],
```
**Important!** The order of the rules in the list matters, the base classes (\yii\base\Component and \yii\base\Object) should be at the end of the list!

### Custom code metrics

Code metrics provided by [https://github.com/sebastianbergmann/phploc](https://github.com/sebastianbergmann/phploc),  has lot of variants, you can define own combination, or also use another package;
set property 'analyseCallback' in module like as
```php
    'analyseCallback = function($group){
               /**@var insolita\codestat\lib\collection\Group $group **/
               $metrics=$customAnalyzer->analyze($group->getFiles());
               return ['totalFiles'=>count($group->getFiles()), 'metric1'=>$metrics[some], ...etc];
     }
```
It should return associative array with `'metric name' => 'metric value'` data and will be replacement for https://github.com/Insolita/yii2-codestat/blob/7d0fc3351718b2052624ea091ff8f154fe471aeb/src/lib/CodestatService.php#L154

And also table summary convention - if metric name contains slash "/", for summary row will be counted average value, otherwise sum


