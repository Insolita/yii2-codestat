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
use yii\helpers\FileHelper;

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
            $row = ['name' => $name] + $row;
        }
        $total = $service->summaryStatistic($summary);
        $total = ['name' => 'Total'] + $total;
        $this->climate->lightGreen()->border('*', 100)
            ->tab()->tab()->tab()->tab()->lightYellow()->out('YII-2 Code Statistic')->lightGreen()
            ->border('*', 100);
        $this->climate
            ->green()->table($summary + [$total]);
    }
    
    public function actionListFiles()
    {
        Output::info('The following files will be processed accordingly module configuration');
        $files = $this->prepareFiles();
        Output::arrayList($files);
        Output::separator();
        Output::info('Total: ' . count($files));
    }
    
    /**
     * @return array
     */
    protected function prepareFiles()
    {
        $files = [];
        foreach ($this->module->scanTargets as $dir) {
            $files += FileHelper::findFiles($dir, [
                'only' => ['*.php'],
                'except' => $this->module->exceptTargets,
                'recursive' =>
                    true,
            ]);
        }
        return $files;
    }
}
