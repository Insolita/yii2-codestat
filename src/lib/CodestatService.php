<?php
/**
 * Created by solly [18.10.17 5:34]
 */

namespace insolita\codestat\lib;

use function count;
use insolita\codestat\lib\collection\Group;
use insolita\codestat\lib\collection\GroupCollection;
use insolita\codestat\lib\contracts\ClassDetectorInterface;
use insolita\codestat\lib\contracts\CodestatServiceInterface;
use ReflectionClass;
use SebastianBergmann\PHPLOC\Analyser;


class CodestatService implements CodestatServiceInterface
{
    protected $groups;
    
    private $nonClasses = 0;

    private $withErrors = [];
    
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
     * @see \insolita\codestat\CodeStatModule::$groupRules
     * @param array $files
     * @return array
     */
    public function makeAdvancedStatistic(array $files):array
    {
        foreach ($this->reflectionGenerator($this->classGenerator($files)) as $reflection) {
            $this->groups->fill($reflection);
        }
        $statistic = [];
        foreach ($this->groups as $group) {
            if ($group->getNumberOfClasses() === 0) {
                continue;
            }
            $result = (new Analyser())->countFiles($group->getFiles(), false);
            foreach ($result as $key =>$value){
                $statistic[$group->getName()][] = ['Metric'=>$key, 'Value'=>$value];
            }
            unset($result);
        }
        return $statistic;
    }

    /**
     * Return  phploc statistic for all files
     * @param array $files
     * @return array
     */
    public function makeCommonStatistic(array $files):array
    {
        $statistic = [];
        $result = (new Analyser())->countFiles($files, false);
        foreach ($result as $key =>$value){
            $statistic[] = ['Metric'=>$key, 'Value'=>$value];
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
            } catch (\Throwable $e) {
                $this->withErrors[] = ['class'=>$class, 'error'=>$e->getMessage()];
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
}
