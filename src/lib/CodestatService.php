<?php
/**
 * Created by solly [18.10.17 5:34]
 */

namespace insolita\codestat\lib;

use insolita\codestat\lib\collection\Group;
use insolita\codestat\lib\collection\GroupCollection;
use insolita\codestat\lib\contracts\ClassDetectorInterface;
use insolita\codestat\lib\contracts\CodestatServiceInterface;
use ReflectionClass;
use SebastianBergmann\PHPLOC\Analyser;
use yii\helpers\ArrayHelper;
use function count;
use function in_array;

class CodestatService implements CodestatServiceInterface
{
    protected $groups;
    
    private $nonClasses = 0;

    private $withErrors = [];
    
    /**
     * @var \insolita\codestat\lib\contracts\ClassDetectorInterface
     */
    private $classDetector;

    private static $percentMetrics = [
        'cloc'=>'loc',
        'ncloc'=>'loc',
        'lloc'=>'loc',
        'llocClasses'=>'lloc',
        'llocFunctions'=>'lloc',
        'llocGlobal'=>'lloc',
        'globalConstantAccesses'=>'globalAccesses',
        'globalVariableAccesses'=>'globalAccesses',
        'superGlobalVariableAccesses'=>'globalAccesses',
        'instanceAttributeAccesses'=>'attributeAccesses',
        'staticAttributeAccesses'=>'attributeAccesses',
        'instanceMethodCalls'=>'methodCalls',
        'staticMethodCalls'=>'methodCalls',
        'abstractClasses'=>'classes',
        'concreteClasses'=>'classes',
        'nonStaticMethods'=>'methods',
        'staticMethods'=>'methods',
        'publicMethods'=>'methods',
        'nonPublicMethods'=>'methods',
        'namedFunctions'=>'functions',
        'anonymousFunctions'=>'functions',
        'globalConstants'=>'constants',
        'classConstants'=>'constants',

    ];
    public static $metricNames = [
        'directories'                 =>  'Directories',
        'files'                       => 'Files',
        'loc'                         => 'Lines of Code (loc) ',
        'cloc'                        =>  'Comment Lines of Code (cloc)',
        'ncloc'                       =>  'Non-Comment Lines of Code (ncloc)',
        'lloc'                        =>  'Logical Lines of Code (lloc)',
        'llocClasses'                 =>  'Classes',
        'classLlocAvg'                =>  'Average Class Length',
        'classLlocMin'                =>  'Minimum Class Length',
        'classLlocMax'                =>  'Maximum Class Length',
        'methodLlocAvg'               =>  'Average Method Length',
        'methodLlocMin'               =>  'Minimum Method Length',
        'methodLlocMax'               =>  'Maximum Method Length',
        'llocFunctions'               =>  'Functions',
        'llocByNof'                   =>  'Average Function Length',
        'llocGlobal'                  =>  'Not in classes or functions',
        'ccnByLloc'                   =>  'Average Complexity per LLOC',
        'classCcnAvg'                 =>  'Average Complexity per Class',
        'classCcnMin'                 =>  'Minimum Class Complexity',
        'classCcnMax'                 =>  'Maximum Class Complexity',
        'methodCcnAvg'                =>  'Average Complexity per Method',
        'methodCcnMin'                =>  'Minimum Method Complexity',
        'methodCcnMax'                =>  'Maximum Method Complexity',
        'globalAccesses'              =>  'Global Accesses',
        'globalConstantAccesses'      =>  'Global Constants',
        'globalVariableAccesses'      =>  'Global Variables',
        'superGlobalVariableAccesses' =>  'Super-Global Variables',
        'attributeAccesses'           =>  'Attribute Accesses',
        'instanceAttributeAccesses'   =>  'Non-Static Attribute Accesses',
        'staticAttributeAccesses'     =>  'Static Attribute Accesses',
        'methodCalls'                 =>  'Method Calls',
        'instanceMethodCalls'         =>  'Non-Static Method Calls',
        'staticMethodCalls'           =>  'Static Method Calls',
        'namespaces'                  =>  'Namespaces',
        'interfaces'                  =>  'Interfaces',
        'traits'                      =>  'Traits',
        'classes'                     =>  'Classes',
        'abstractClasses'             =>  'Abstract Classes',
        'concreteClasses'             =>  'Concrete Classes',
        'methods'                     =>  'Methods',
        'nonStaticMethods'            => 'Non-Static Methods',
        'staticMethods'               =>  'Static Methods',
        'publicMethods'               =>  'Public Methods',
        'nonPublicMethods'            =>  'Non-Public Methods',
        'functions'                   =>  'Functions',
        'namedFunctions'              =>  'Named Functions',
        'anonymousFunctions'          => 'Anonymous Functions',
        'constants'                   =>  'Constants',
        'classConstants'              => 'Class Constants',
        'globalConstants'             =>  'Global Constants',
        'testClasses'                 => 'Test Classes',
        'testMethods'                 =>  'Test Methods',
    ];
    
    public function __construct(ClassDetectorInterface $classDetector, GroupCollection $groups)
    {
        $this->groups = $groups;
        $this->classDetector = $classDetector;
    }
    
    /**
     * Return summary from partial phploc statistic information per each defined group
     * @see \insolita\codestat\CodeStatModule::$groupRules
     * @param array $files
     * @param callable|null  $analyseCallback
     *
     * @return array
     */
    public function makeStatistic(array $files, $analyseCallback = null):array
    {
        foreach ($this->reflectionGenerator($this->classGenerator($files)) as $reflection) {
            $this->groups->fill($reflection);
        }
        $statistic = [];
        foreach ($this->groups as $group) {
            if (is_callable($analyseCallback)) {
                $statistic[$group->getName()] = call_user_func($analyseCallback, $group);
            } else {
                $statistic[$group->getName()] = $this->makeSummary($group);
            }
        }
        return $statistic;
    }

    /**
     * Return full phploc statistic per each defined group
     * @param array $files
     * @param array $metrics
     * @return array
     * @see \insolita\codestat\CodeStatModule::$groupRules
     */
    public function makeAdvancedStatistic(array $files, array $metrics = []):array
    {
        foreach ($this->reflectionGenerator($this->classGenerator($files)) as $reflection) {
            $this->groups->fill($reflection);
        }
        $statistic = [];
        foreach ($this->groups as $group) {
            if ($group->getNumberOfClasses() === 0) {
                continue;
            }
            $result = (new Analyser())->countFiles($group->getFiles(), true);
            foreach (static::$metricNames as $key => $label) {
                if (!empty($metrics) && !in_array($key, $metrics, true)) {
                    continue;
                }
                $value = $this->calcPercentMetric($result, $key);
                $statistic[$group->getName()][$label] = $value;
            }
            unset($result);
        }
        return $statistic;
    }

    /**
     * Return  phploc statistic for all files
     * @param array $files
     * @param array $metrics
     * @return array
     */
    public function makeCommonStatistic(array $files, array $metrics = []):array
    {
        $statistic = [];
        $result = (new Analyser())->countFiles($files, true);
        foreach (static::$metricNames as $key => $label) {
            if (!empty($metrics) && !in_array($key, $metrics, true)) {
                continue;
            }
            $value = $this->calcPercentMetric($result, $key);
            $statistic[$label] = $value;
        }
        return $statistic;
    }
    
    /**
     * @param array $statistic
     *
     * @return array|mixed
     */
    public function summaryStatistic(array $statistic)
    {
        $result = [];
        if (!empty($statistic)) {
            $firstRow = reset($statistic);
            if (count($statistic) === 1) {
                $result = $firstRow;
            } else {
                $firstKeys = array_keys($firstRow);
                foreach ($firstKeys as $key) {
                    if (mb_strpos($key, '/') !== false) {
                        $result[$key] = $this->columnAvg(array_column($statistic, $key));
                    } else {
                        $result[$key] = array_sum(array_column($statistic, $key));
                    }
                }
            }
        }
        return $result;
    }
    
    public function withErrorsCounter():int
    {
        return count($this->withErrors);
    }

    public function errorList():array
    {
        return $this->withErrors;
    }
    /**
     * @param array $files
     *
     * @return \Generator
     */
    public function classGenerator(array $files)
    {
        foreach ($files as $filePath) {
            try{
                $className = $this->classDetector->resolveClassName($filePath);
                if ($className === null) {
                    ++$this->nonClasses;
                } else {
                    yield $className;
                }
            }catch (\Throwable $e){
                $this->withErrors[] = $e->getMessage();
            }
        }
    }
    
    /**
     * @param array $column
     *
     * @return float|int
     */
    protected function columnAvg(array $column)
    {
        return count($column) > 0 ? round(array_sum($column) / count($column), 2) : 0;
    }
    
    /**
     * @param \Generator $classGen
     *
     * @return \Generator|\ReflectionClass[]
     */
    protected function reflectionGenerator($classGen)
    {
        foreach ($classGen as $class) {
            try {
                $reflection = new ReflectionClass($class);
                if (!$reflection->isInternal()) {
                    yield $reflection;
                }
            } catch (\Throwable $e) {
                $this->withErrors[] = $e->getMessage();
            }
        }
    }
    
    /**
     * @param \insolita\codestat\lib\collection\Group $group
     *
     * @return array
     */
    protected function makeSummary(Group $group)
    {
        $summary = [];
        if ($group->getNumberOfClasses() > 0) {
            $groupMetrics = (new Analyser())->countFiles($group->getFiles(), false);
            $summary['Classes'] = $groupMetrics['classes'];
            $summary['Methods'] = $groupMetrics['methods'];
            $summary['Methods/Class'] = round($groupMetrics['methods'] / $groupMetrics['classes'], 2);
            $summary['Lines'] = $groupMetrics['loc'];
            $summary['LoC'] = $groupMetrics['lloc'];
            $summary['LoC/Method'] = $groupMetrics['methods'] > 0
                ? round($groupMetrics['lloc'] / $groupMetrics['methods'], 2)
                : 0;
            $summary['Complexity'] = $groupMetrics['ccn'];
            $summary['Class/Complexity avg'] = round($groupMetrics['classCcnAvg'], 4);
            return $summary;
        }

        return $summary + array_fill_keys([
                'Classes',
                'Methods/Class',
                'Methods',
                'Lines',
                'LoC',
                'LoC/Method',
                'Complexity',
                'Class/Complexity avg',
            ], 0);
    }

    /**
     * @param array $result
     * @param       $key
     * @return string
     */
    private function calcPercentMetric(array &$result, $key):string
    {
        $value = $result[$key];
        if (is_float($value)) {
            $value = round($value, 2);
        }
        if (isset(self::$percentMetrics[$key])) {
            $delimiter = ArrayHelper::getValue($result, self::$percentMetrics[$key], 0);
            $percent = $delimiter > 0 ? round(($result[$key] / $delimiter) * 100, 2) : 0;
            return $value . ' [' . $percent . '%]';
        }
        return $value;
    }
}
