<?php
/**
 * Created by solly [18.10.17 4:41]
 */

namespace insolita\codestat\controllers;

use insolita\codestat\CodeStatModule;
use insolita\codestat\helpers\Output;
use yii\base\Module;
use yii\console\Controller;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use function compact;

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
        $total = $service->summaryStatistic($summary);
        //TODO: find yii2 table output
        //Output::info(VarDumper::dumpAsString(compact('total', 'summary')));
        Output::arrayToTable($summary);
        Output::arrayToTable($total);
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
