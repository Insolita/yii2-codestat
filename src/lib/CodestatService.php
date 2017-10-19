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
use function array_column;
use function array_fill_keys;
use function array_filter;
use function array_keys;
use function array_sum;
use function call_user_func;
use function in_array;
use function is_null;

/**
 *
 */
class CodestatService implements CodestatServiceInterface
{
    protected $groups;
    
    private $nonClasses = 0;
    
    private $withErrors = 0;
    
    /**
     * @var \insolita\codestat\lib\contracts\ClassDetectorInterface
     */
    private $classDetector;
    
    public function __construct(ClassDetectorInterface $classDetector, GroupCollection $groups)
    {
        $this->groups = $groups;
        $this->classDetector = $classDetector;
    }
    
    /**
     * @param array $files
     * @param null  $analyseCallback
     *
     * @return array
     */
    public function makeStatistic(array $files, $analyseCallback = null)
    {
        foreach ($this->reflectionGenerator($this->classGenerator($files)) as $reflection) {
            $this->groups->fill($reflection);
        }
        $statistic = [];
        foreach ($this->groups as $group) {
            if (is_callable($analyseCallback)) {
                $statistic[$group->getName()] = call_user_func($analyseCallback, $group);
            } else {
                $statistic[$group->getName()] = $this->analyse($group);
            }
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
    
    public function withErrorsCounter()
    {
        return $this->withErrors;
    }
    
    public function nonClassesCounter()
    {
        return $this->nonClasses;
    }
    
    /**
     * @param array $files
     *
     * @return \Generator
     */
    public function classGenerator(array $files)
    {
        foreach ($files as $filePath) {
            $className = $this->classDetector->resolveClassName($filePath);
            if (is_null($className)) {
                $this->nonClasses += 1;
            } else {
                yield $className;
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
            } catch (\Exception $e) {
                $this->withErrors += 1;
            }
        }
    }
    
    /**
     * @param \insolita\codestat\lib\collection\Group $group
     *
     * @return array
     */
    protected function analyse(Group $group)
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
        } else {
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
    }
}
