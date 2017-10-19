<?php
/**
 * Created by solly [18.10.17 5:34]
 */

namespace insolita\codestat\lib;

use insolita\codestat\lib\collection\Group;
use insolita\codestat\lib\collection\GroupCollection;
use insolita\codestat\lib\contracts\IClassDetector;
use insolita\codestat\lib\contracts\ICodestatService;
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
class CodestatService implements ICodestatService
{
    protected $groups;
    
    private $nonClasses = 0;
    
    private $withErrors = 0;
    
    /**
     * @var \insolita\codestat\lib\contracts\IClassDetector
     */
    private $classDetector;
    
    public function __construct(IClassDetector $classDetector, GroupCollection $groups)
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
                    $result[$key] = array_sum(array_column($statistic, $key));
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
        $summary = ['Classes' => $group->getNumberOfClasses()];
        if ($group->getNumberOfClasses() > 0) {
            $groupMetrics = (new Analyser())->countFiles($group->getFiles(), false);
            $groupMetrics = array_filter($groupMetrics, function ($key) {
                return in_array($key, ['methods', 'loc', 'lloc', 'ccn', 'classCcnAvg']);
            }, ARRAY_FILTER_USE_KEY);
            $summary['Methods'] = $groupMetrics['methods'];
            $summary['Methods/Class']
                = round($groupMetrics['methods'] / $group->getNumberOfClasses(), 2);
            $summary['Lines'] = $groupMetrics['loc'];
            $summary['LoC'] = $groupMetrics['lloc'];
            $summary['Complexity'] = $groupMetrics['ccn'];
            $summary['Class/Complexity avg'] = $groupMetrics['classCcnAvg'];
            return $summary;
        } else {
            return $summary +
                array_fill_keys([
                    'Methods/Class',
                    'Methods',
                    'Lines',
                    'LoC',
                    'Complexity',
                    'Class/Complexity avg',
                ], 0);
        }
    }
}
