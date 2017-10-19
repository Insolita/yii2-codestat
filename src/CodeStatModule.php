<?php
/**
 * Created by solly [18.10.17 4:33]
 */

namespace insolita\codestat;

use insolita\codestat\lib\classdetect\RegexpDetector;
use insolita\codestat\lib\CodestatService;
use insolita\codestat\lib\collection\GroupCollection;
use insolita\codestat\lib\contracts\IClassDetector;
use insolita\codestat\lib\contracts\ICodestatService;
use Yii;
use yii\base\Action;
use yii\base\Behavior;
use yii\base\Component;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Module;
use yii\base\Object;
use yii\base\Widget;
use yii\console\Controller as ConsoleController;
use yii\db\BaseActiveRecord;
use yii\di\Instance;
use yii\rest\Controller as RestController;
use yii\test\Fixture;
use yii\web\AssetBundle;
use yii\web\Controller as WebController;
use function is_array;

/**
 *
 */
class CodeStatModule extends Module
{
    /**
     * array, list of directories that will be scanned
     *
     * @var array
     */
    public $scanTargets
        = [
            //
        ];
    
    /**
     * array, list of patterns excluding from the results matching file or directory paths
     *
     * @see \yii\helpers\FileHelper::findFiles() 'except' doc
     * @var array
     */
    public $exceptTargets
        = [
            'config*',
            'vendor*',
            '*web/*',
            '*runtime/*',
            '*views/*',
        ];
    
    /**
     * Custom group analyse function, with input Group, should return array with metric names and values
     *
     * @see CodestatService::analyse()
     * @example
     * 'analyseCallback = function(Group $group){
     *       $metrics=$customAnalyzer->analyze($group->getFiles());
     *       return ['totalFiles'=>count($group->getFiles()), 'metric1'=>$metrics[some], ...etc];
     * }
     * @var null|callable
     */
    public $analyseCallback;
    
    /**
     * @var string|ICodestatService
     */
    public $statService = CodestatService::class;
    /**
     * @var string|IClassDetector
     */
    public $classDetector = RegexpDetector::class;
    /**
     * @var array|GroupCollection
     * Leave empty for use defaults
     */
    public $groupRules;
    
    /**
     *
     */
    public function init()
    {
        $this->checkConfig();
        $this->prepareScanTargets();
        $this->prepareRules();
        $this->prepareService();
        parent::init();
    }
    
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
    }
    
    public static function defaultRules()
    {
        return [
            'Actions' => Action::class,
            'ActiveRecords' => BaseActiveRecord::class,
            'AssetBundles' => AssetBundle::class,
            'Behaviors' => Behavior::class,
            'ConsoleControllers' => ConsoleController::class,
            'RestControllers' => RestController::class,
            'WebControllers' => WebController::class,
            'Events' => Event::class,
            'Fixtures'=> Fixture::class,
            'Models' => Model::class,
            'Modules' => Module::class,
            'Widgets' => Widget::class,
            'Components' => Component::class,
            'Objects' => Object::class,
            'PureClass' => function (\ReflectionClass $reflection) {
                return (
                    !$reflection->getParentClass()
                    && !$reflection->isInterface()
                    && !$reflection->isAbstract()
                    && !$reflection->isAnonymous()
                );
            }
        ];
    }
    
    protected function prepareScanTargets()
    {
        $this->scanTargets = array_map(function ($path) {
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
        $this->classDetector = Instance::ensure($this->classDetector, IClassDetector::class);
        $this->statService = Yii::createObject($this->statService, [$this->classDetector, $this->groupRules]);
    }
}
