<?php
/**
 * Created by solly [18.10.17 4:41]
 */

namespace insolita\codestat\controllers;

use insolita\codestat\CodeStatModule;
use insolita\codestat\helpers\Output;
use League\CLImate\CLImate;
use function str_repeat;
use Yii;
use yii\base\Module;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use function mb_strwidth;

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
    
    public function actionIndex()
    {
        $service = $this->module->statService;
        $summary = $service->makeStatistic($this->prepareFiles());
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
    }
    
    public function actionListFiles()
    {
        Output::info('The following files will be processed accordingly module configuration');
        $files = $this->prepareFiles();
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
    
    /**
     * @return array
     */
    protected function prepareFiles()
    {
        $files = [];
        foreach ($this->module->scanTargets as $dir) {
            $files = array_merge(FileHelper::findFiles($dir, [
                'only' => ['*.php'],
                'except' => $this->module->exceptTargets,
                'caseSensitive' => false,
                'recursive' => true,
            ]), $files);
        }
        return $files;
    }
}
