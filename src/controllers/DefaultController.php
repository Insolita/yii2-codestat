<?php
/**
 * Created by solly [18.10.17 4:41]
 */

namespace insolita\codestat\controllers;

use function count;
use function file_exists;
use insolita\codestat\CodeStatModule;
use insolita\codestat\helpers\Output;
use League\CLImate\CLImate;
use yii\base\Module;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\FileHelper;

class DefaultController extends Controller
{
    public $defaultAction = 'summary';
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

    /**
     *  Show summary from partial phploc statistic information per each defined group
     * @param bool $showErrors
     * @return int
     */
    public function actionSummary(bool $showErrors = false)
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

        if($showErrors !== true){
            return ExitCode::OK;
        }
        $this->headline('Failed for resolve', 'lightYellow');
        if(!count($service->errorList())){
            $this->climate->info('Errors not found');
        } else{
            $this->climate->table($service->errorList());
        }
        return ExitCode::OK;
    }

    /**
     *  Return full phploc statistic per each defined group
     * @param string|null $groupName
     * @return int
     */
    public function actionAdvanced(?string $groupName = null):int
    {
        $service = $this->module->statService;
        $statistic = $service->makeAdvancedStatistic($this->module->prepareFiles(), $this->module->metrics);
        $this->headline('YII-2 Code Statistic', 'green');

        if ($groupName !== null) {
            if (!isset($statistic[$groupName])) {
                $this->stderr('Undefined group ' . $groupName);
                return ExitCode::DATAERR;
            }
            $this->headline($groupName, 'lightYellow');
            $this->climate->table($statistic[$groupName]);
            return ExitCode::OK;
        }

        foreach ($statistic as $group =>$data) {
            $this->headline($group, 'lightYellow');
            $this->climate->table($data);
            if (!$this->confirm('Show next group?')) {
                break;
            }
        }
        return ExitCode::OK;
    }

    /**
     * Return  phploc statistic for all files
     * @return int
     */
    public function actionCommon():int
    {
        $service = $this->module->statService;
        $statistic = $service->makeCommonStatistic($this->module->prepareFiles(), $this->module->metrics);
        $this->headline('YII-2 Code Statistic', 'green');
        $this->climate->table($statistic);
        return ExitCode::OK;
    }

    /**
     * Return  phploc statistic for concrete directory
     * @param string $dir - Path or path alias
     * @return int
     */
    public function actionDirectory(string $dir):int
    {
        $service = $this->module->statService;
        $dir = \Yii::getAlias($dir);
        if (!is_dir($dir)) {
            $this->stderr('Directory not found by path ' . $dir);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $statistic = $service->makeCommonStatistic(FileHelper::findFiles($dir, [
            'only' => ['*.php'],
            'caseSensitive' => false,
            'recursive' => true,
        ]), $this->module->metrics);
        $this->headline('YII-2 Code Statistic', 'green');
        $this->climate->table($statistic);
        return ExitCode::OK;
    }

    /**
     * Return  phploc statistic for concrete file
     * @param string $filePath - Path or path alias
     * @return int
     */
    public function actionFile(string $filePath):int
    {
        $service = $this->module->statService;
        $filePath = \Yii::getAlias($filePath);
        if (!file_exists($filePath)) {
            $this->stderr('File not found by path ' . $filePath);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $statistic = $service->makeCommonStatistic([$filePath], $this->module->metrics);
        $this->headline('YII-2 Code Statistic', 'green');
        $this->climate->table($statistic);
        return ExitCode::OK;
    }

    /**
     * Show files that will be processed accordingly module configuration
     */
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
                $colorized[$i][$key] = (string) $value;
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
