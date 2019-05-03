<?php
/**
 * Created by solly [18.10.17 4:41]
 */

namespace insolita\codestat\controllers;

use insolita\codestat\CodeStatModule;
use insolita\codestat\helpers\Output;
use League\CLImate\CLImate;
use yii\base\Module;
use yii\console\Controller;

class DefaultController extends Controller
{
    /**
     * @var CodeStatModule|Module
     **/
    public $module;
    
    public $color = true;
    
    protected $climate;
    
    public function __construct($id, Module $module, CLImate $CLImate, array $config = [])
    {
        $this->climate = $CLImate;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex($showErrors = false)
    {
        $service = $this->module->statService;
        $summary = $service->makeStatistic($this->module->prepareFiles());
        foreach ($summary as $name => &$row) {
            $row = ['Group' => $name] + $row;
        }
        $total = $service->summaryStatistic($summary);
        $total = ['Group' => 'Total'] + $total;
        $summary = $summary + [$total];
        if ($this->color) {
            $summary = $this->colorize(array_values($summary));
        }
        $this->headline('YII-2 Code Statistic', 'lightYellow');
        $this->climate->table($summary);

        if($showErrors === true){
            $this->headline('Failed for resolve', 'lightYellow');
            $this->climate->table($service->errorList());
        }
    }
    
    public function actionListFiles()
    {
        Output::info('The following files will be processed accordingly module configuration');
        $files = $this->module->prepareFiles();
        Output::arrayList($files);
        Output::separator();
        Output::info('Total: ' . count($files));
    }
    
    protected function colorize(array $summary)
    {
        $colorized = [];
        foreach ($summary as $i => $row) {
            foreach ($row as $key => $value) {
                if ($key === 'Group') {
                    $value = $this->wrap($value, 'yellow');
                }
                if ($i == count($summary) - 1) {
                    $value = $this->wrap($value, 'light_cyan');
                }
                $key = $this->wrap($key, 'green');
                $colorized[$i][$key] = (string)$value;
            }
        }
        return $colorized;
    }
    
    protected function wrap($string, $color)
    {
        return "<bold><$color>$string</$color></bold>";
    }
    
    protected function headline($string, $color)
    {
        $this->climate->green()->border('=', 110)->$color()->tab(4)->out($string);
    }
}
