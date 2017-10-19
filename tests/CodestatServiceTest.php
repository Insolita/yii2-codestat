<?php
/**
 * Created by solly [18.10.17 7:51]
 */

namespace tests;

use Codeception\Specify;
use insolita\codestat\lib\classdetect\RegexpDetector;
use insolita\codestat\lib\CodestatService;
use insolita\codestat\lib\collection\GroupCollection;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\PHPLOC\Analyser;
use tests\stub\StubTrait;
use yii\base\Behavior;
use yii\base\Controller as BaseController;
use yii\base\Object;
use function expect;

class CodestatServiceTest extends TestCase
{
    use StubTrait;
    use Specify;
    
    protected $classList = [];
    
    protected $service;
    
    public function testMakeStatistic()
    {
        $this->specify('simple success scenario', function () {
            $service = new CodestatService(
                new RegexpDetector(),
                new GroupCollection(['a' => BaseController::class, 'b' => Behavior::class])
            );
            $result = $service->makeStatistic($this->files());
            expect($result)->hasKey('a');
            expect($result)->hasKey('b');
            foreach ($result as $name => $statistic) {
                expect($statistic)->hasKey('Classes');
            }
            expect($result['a']['Classes'])->equals(2);
            expect($result['b']['Classes'])->equals(1);
        });
        $this->specify('test with not matched files', function () {
            $service = new CodestatService(
                new RegexpDetector(),
                new GroupCollection(['u' => TestCase::class])
            );
            $result = $service->makeStatistic($this->files());
            expect($result)->count(1);
            foreach ([
                    'Classes',
                    'Methods/Class',
                    'Methods',
                    'Lines',
                    'LoC',
                    'LoC/Method',
                    'Complexity',
                    'Class/Complexity avg',
                ] as $key) {
                expect($result['u'][$key])->equals(0);
            }
        });
        $this->specify('test with custom analyze callback', function () {
            $service = new CodestatService(
                new RegexpDetector(),
                new GroupCollection([
                    'objects' => Object::class,
                    'nonObjects' => function (\ReflectionClass $reflection) {
                        return !$reflection->isSubclassOf(Object::class);
                    },
                ])
            );
            $result = $service->makeStatistic($this->files(), function ($group) {
                $groupMetrics = (new Analyser())->countFiles($group->getFiles(), false);
                $groupMetrics = array_filter($groupMetrics, function ($key) {
                    return in_array($key, ['methods', 'interfaces', 'traits', 'classes', 'functions']);
                }, ARRAY_FILTER_USE_KEY);
                return $groupMetrics;
            });
            expect($result)->count(2);
            foreach ($result as $name => $statistic) {
                expect($statistic)->hasKey('methods');
                expect($statistic)->hasKey('interfaces');
                expect($statistic)->hasKey('traits');
                expect($statistic)->hasKey('classes');
                expect($statistic)->hasKey('functions');
            }
        });
        $this->specify('test real stubs', function () {
            $service = new CodestatService(
                new RegexpDetector(),
                new GroupCollection($this->rules())
            );
            $result = $service->makeStatistic($this->files());
        });
    }
    
    public function testSummaryStatistic()
    {
        $service = new CodestatService(
            new RegexpDetector(),
            new GroupCollection([])
        );
        foreach ($this->summaryFixture() as $name => $fixture) {
            $summary = $service->summaryStatistic($fixture['summary']);
            expect($name, $summary)->equals($fixture['expect']);
        }
    }
    
    protected function summaryFixture()
    {
        return [
            'on empty' => ['summary' => [], 'expect' => []],
            'on oneRow' => [
                'summary' => [
                    'one' => ['a' => 1, 'b' => 2, 'c' => 3],
                ],
                'expect' => ['a' => 1, 'b' => 2, 'c' => 3],
            ],
            'on oneRow with avg' => [
                'summary' => [
                    'one' => ['a' => 1, 'a/b' => 2, 'c' => 3],
                ],
                'expect' => ['a' => 1, 'a/b' => 2, 'c' => 3],
            ],
            'on full' => [
                'summary' => [
                    'one' => ['a' => 1, 'b' => 0.2, 'c' => 3],
                    'two' => ['a' => 2, 'b' => 0.3, 'c' => 4],
                    'three' => ['a' => 3, 'b' => 0.4, 'c' => 5],
                ],
                'expect' => ['a' => 6, 'b' => 0.9, 'c' => 12],
            ],
            'on full  with avg' => [
                'summary' => [
                    'one' => ['a' => 1, 'a/b' => 0.2, 'c' => 3],
                    'two' => ['a' => 2, 'a/b' => 0.3, 'c' => 4],
                    'three' => ['a' => 3, 'a/b' => 0.4, 'c' => 5],
                ],
                'expect' => ['a' => 6, 'a/b' => round(array_sum([0.2, 0.3, 0.4])/3, 2), 'c' => 12],
            ],
        ];
    }
}
