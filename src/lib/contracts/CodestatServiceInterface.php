<?php
/**
 * Created by solly [19.10.17 1:09]
 */

namespace insolita\codestat\lib\contracts;


interface CodestatServiceInterface
{
    /**
     * @param array $files
     * @param callable|null  $analyseCallback
     *
     * @return array
     */
    public function makeStatistic(array $files, $analyseCallback = null);
    
    /**
     * @param array $statistic
     *
     * @return array
     */
    public function summaryStatistic(array $statistic);

    public function makeAdvancedStatistic(array $files, array $metrics = []);

    public function errorList():array;

    /**
     * Return  phploc statistic for all files
     * @param array $files
     * @param array $metrics
     * @return array
     */
    public function makeCommonStatistic(array $files, array $metrics = []):array;
}
