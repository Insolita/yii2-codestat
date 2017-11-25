<?php
/**
 * Created by solly [18.10.17 4:41]
 */

namespace insolita\codestat\controllers;

use function array_keys;
use function array_values;
use insolita\codestat\CodeStatModule;
use insolita\codestat\helpers\Output;
use const PHP_EOL;
use function str_repeat;
use yii\base\Module;
use yii\console\Controller;
use yii\console\widgets\Table;
use yii\helpers\Console;
use yii\helpers\FileHelper;

class DefaultController extends Controller
{
    /**
     * @var CodeStatModule|Module
     **/
    public $module;
    
    public $color = true;
    
    public function actionIndex()
    {
        $service = $this->module->statService;
        $summary = $service->makeStatistic($this->prepareFiles());
        foreach ($summary as $name => &$row) {
            $row = ['Group' => $name] + $row;
        }
        $total = $service->summaryStatistic($summary);
        $total = ['Group' => 'Total'] + $total;
        $headers = array_keys($total);
        $summary = $summary + [$total];
        foreach ($summary as $name => &$row) {
            $row = array_values($row);
        }
        if ($this->color) {
            $summary = $this->colorize(array_values($summary));
        }
        $this->headline('YII-2 Code Statistic', Console::FG_YELLOW);
        $table = new Table();
        echo $table->setHeaders($headers)->setRows($summary)->run();
       // echo $table->setHeaders(array_keys($total))->setRows([array_values($total)])->run();
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
                if ($key === 0) {
                    $value = $this->wrap($value, Console::FG_YELLOW);
                }
                if ($i == count($summary) - 1) {
                    $value = $this->wrap($value, Console::FG_CYAN);
                }
                $colorized[$i][$key] = $value;
            }
        }
        return $colorized;
    }
    
    protected function wrap($string, $color)
    {
        return Console::ansiFormat($string, [Console::BOLD, $color]);
    }
    
    protected function headline($string, $color)
    {
        list($width) = Console::getScreenSize();
        $width = $width? $width-6:100;
        $string = $this->wrap($string, $color);
        $stringWidth = mb_strwidth($string, \Yii::$app->charset);
        $stringIndent = ($stringWidth<=3||$stringWidth>=$width)?0:round(($width - $stringWidth)/3);
        echo str_repeat('=', $width).PHP_EOL;
        echo str_repeat(' ', $stringIndent).$this->wrap($string, $color).PHP_EOL;
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
