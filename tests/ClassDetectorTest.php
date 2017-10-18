<?php
/**
 * Created by solly [19.10.17 1:17]
 */

namespace tests;

use insolita\codestat\lib\classdetect\RegexpDetector;
use insolita\codestat\lib\classdetect\TokenizerDetector;
use tests\stub\non_psr_class;
use tests\stub\one\StubEvent;
use tests\stub\StubTrait;
use tests\stub\two\StubInterface;

class ClassDetectorTest extends \PHPUnit\Framework\TestCase
{
    public function testRegexpDetector()
    {
        $detector = new RegexpDetector();
        foreach ($this->getFixture() as $filePath => $expect) {
            expect($filePath, $detector->resolveClassName($filePath))->equals($expect);
        }
    }
    
    public function testTokenizeDetector()
    {
        $detector = new TokenizerDetector();
        foreach ($this->getFixture() as $filePath => $expect) {
            expect($filePath, $detector->resolveClassName($filePath))->equals($expect);
        }
    }
    
    protected function getFixture()
    {
        return [
            __DIR__ . '/stub/one/StubEvent.php' => StubEvent::class,
            __DIR__ . '/stub/StubTrait.php' => StubTrait::class,
            __DIR__ . '/stub/two/StubInterface.php' => StubInterface::class,
            __DIR__ . '/stub/one/views/default/index.php' => null,
            __DIR__ . '/stub/two/m170412_230002_migration.php' => '\m170412_230002_migration',
            __DIR__ . '/ClassDetectorTest.php' => self::class,
            __DIR__ . '/stub/views/default/NonPsr.php' => non_psr_class::class,
        ];
    }
}
