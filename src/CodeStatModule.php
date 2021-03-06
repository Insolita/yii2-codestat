<?php
/**
 * Created by solly [18.10.17 4:33]
 */

namespace insolita\codestat;

use Exception;
use insolita\codestat\lib\classdetect\TokenizerDetector;
use insolita\codestat\lib\CodestatService;
use insolita\codestat\lib\collection\GroupCollection;
use insolita\codestat\lib\contracts\ClassDetectorInterface;
use insolita\codestat\lib\contracts\CodestatServiceInterface;
use Yii;
use yii\base\Action;
use yii\base\BaseObject;
use yii\base\Behavior;
use yii\base\Component;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Module;
use yii\base\Widget;
use yii\console\Controller as ConsoleController;
use yii\db\ActiveQuery;
use yii\db\BaseActiveRecord;
use yii\di\Instance;
use yii\helpers\FileHelper;
use yii\rest\Controller as RestController;
use yii\web\AssetBundle;
use yii\web\Controller as WebController;
use function array_merge;

class CodeStatModule extends Module
{
    public $defaultRoute = 'default/summary';
    /**
     * array, list of directories that will be scanned
     *
     * @var array
     */
    public $scanTargets = [];
    
    /**
     * array, list of patterns excluding from the results matching file or directory paths
     *
     * @see \yii\helpers\FileHelper::findFiles() 'except' doc
     * @var array
     */
    public $exceptTargets = [
            'config*',
            'vendor*',
            '*web/*',
            '*runtime/*',
            '*views/*',
        ];
    
    /**
     * Custom group analyse function, with input Group, should return array with metric names and values
     *
     * @see CodestatServiceInterface::analyse()
     * @example
     * 'analyseCallback = function(Group $group){
     *       $metrics=$customAnalyzer->analyze($group->getFiles());
     *       return ['totalFiles'=>count($group->getFiles()), 'metric1'=>$metrics[some], ...etc];
     * }
     * @var null|callable
     */
    public $analyseCallback;
    
    /**
     * @var string|CodestatServiceInterface
     */
    public $statService = CodestatService::class;
    /**
     * @var string|ClassDetectorInterface
     */
    public $classDetector = TokenizerDetector::class;
    /**
     * @var array|GroupCollection
     * Leave empty for use defaults
     */
    public $groupRules;

    /**
     * List of phploc metrics showed for advanced, common, directory and file actions, by default all available metrics
     * will be showed
     * @var array
     */
    public $metrics = [];

    public function init()
    {
        $this->checkConfig();
        $this->prepareScanTargets();
        $this->prepareRules();
        $this->prepareService();
        parent::init();
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function checkConfig()
    {
        if (empty($this->scanTargets)) {
            throw new InvalidConfigException('scanTargets can`t be empty');
        }
        if (empty($this->groupRules)) {
            $this->groupRules = self::defaultRules();
        }
        if (!is_array($this->scanTargets) || !is_array($this->exceptTargets)) {
            throw new InvalidConfigException('scanTargets and exceptTargets must be array');
        }
        if (!(is_array($this->groupRules) || $this->groupRules instanceof GroupCollection)) {
            throw new InvalidConfigException('groupRules must be array or instance of GroupCollection');
        }
        if (!is_array($this->metrics)) {
            $this->metrics = [];
        }
    }
    
    public static function defaultRules()
    {
        return [
            'Actions' => Action::class,
            'ActiveQuery'=>ActiveQuery::class,
            'ActiveRecords' => BaseActiveRecord::class,
            'AssetBundles' => AssetBundle::class,
            'Behaviors' => Behavior::class,
            'ConsoleControllers' => ConsoleController::class,
            'RestControllers' => RestController::class,
            'WebControllers' => WebController::class,
            'Events' => Event::class,
            'Models' => Model::class,
            'Modules' => Module::class,
            'Widgets' => Widget::class,
            'Components' => Component::class,
            'Objects' => BaseObject::class,
            'Exceptions' => Exception::class,
            'PureClass' => function(\ReflectionClass $reflection) {
                return (
                    !$reflection->getParentClass()
                    && !$reflection->isInterface()
                    && !$reflection->isAbstract()
                );
            }
        ];
    }

    public function prepareFiles():array
    {
        $files = [];
        foreach ($this->scanTargets as $dir) {
            $files[] = FileHelper::findFiles($dir, [
                'only' => ['*.php'],
                'except' => $this->exceptTargets,
                'caseSensitive' => false,
                'recursive' => true,
            ]);
        }
        return array_merge(...$files);
    }
    
    protected function prepareScanTargets()
    {
        $this->scanTargets = array_map(function($path) {
            return \Yii::getAlias($path);
        }, $this->scanTargets);
    }
    
    protected function prepareRules()
    {
        if (is_array($this->groupRules)) {
            $this->groupRules = new GroupCollection($this->groupRules);
        }
    }
    
    protected function prepareService()
    {
        $this->classDetector = Instance::ensure($this->classDetector, ClassDetectorInterface::class);
        $this->statService = Yii::createObject($this->statService, [$this->classDetector, $this->groupRules]);
    }
}
