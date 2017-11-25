<?php
/**
 * Created by solly [18.10.17 22:10]
 */

namespace tests;

use Codeception\AssertThrows;
use Codeception\Specify;
use insolita\codestat\lib\collection\GroupCollection;
use InvalidArgumentException;
use ReflectionClass;
use tests\stub\one\StubComponent;
use tests\stub\one\StubEvent;
use tests\stub\one\StubModule;
use tests\stub\StubTrait;
use tests\stub\two\SiteController;
use yii\base\Component;
use yii\base\Module;
use function array_keys;
use function expect;
use function expect_that;
use function in_array;
use yii\base\BaseObject;

class GroupCollectionTest extends \PHPUnit\Framework\TestCase
{
    use Specify;
    use AssertThrows;
    use StubTrait;
    
    public function testInit()
    {
        $this->should(' be successfully created', function () {
            $collection = new GroupCollection($this->rules());
            expect($collection->count())->equals(count($this->rules()));
            foreach ($collection as $group) {
                expect_that(in_array($group->getName(), array_keys($this->rules())));
            }
        });
        $this->should('throw exception', function ($failRule) {
            $this->assertThrows(InvalidArgumentException::class, function () use ($failRule) {
                new GroupCollection($failRule);
            });
        }, [
            'examples' => [$this->failRules()],
        ]);
    }
    
    public function testFill()
    {
        $this->specify('data should be added in only one of group, or skipped', function () {
            $collection = new GroupCollection(['a' => Module::class, 'b' => Component::class]);
            $collection->fill(new ReflectionClass(StubModule::class));
            expect($collection['a']->getFiles())->contains(__DIR__ . '/stub/one/StubModule.php');
            expect($collection['b']->getFiles())->notContains(__DIR__ . '/stub/one/StubModule.php');
            $collection->fill(new ReflectionClass(StubComponent::class));
            expect($collection['a']->getFiles())->notContains(__DIR__ . '/stub/one/StubComponent.php');
            expect($collection['b']->getFiles())->contains(__DIR__ . '/stub/one/StubComponent.php');
            $collection->fill(new ReflectionClass(SiteController::class));
            expect($collection['a']->getFiles())->notContains(__DIR__ . '/stub/two/SiteController.php');
            expect($collection['b']->getFiles())->contains(__DIR__ . '/stub/two/SiteController.php');
            $collection->fill(new ReflectionClass(StubEvent::class));
            expect($collection['a']->getFiles())->notContains(__DIR__ . '/stub/one/StubEvent.php');
            expect($collection['b']->getFiles())->notContains(__DIR__ . '/stub/one/StubEvent.php');
        });
        
        $this->specify('rule order is important', function () {
            $collection = new GroupCollection(['a' => BaseObject::class, 'b' => Component::class]);
            $collection->fill(new ReflectionClass(StubModule::class));
            expect($collection['a']->getFiles())->contains(__DIR__ . '/stub/one/StubModule.php');
            expect($collection['b']->getFiles())->notContains(__DIR__ . '/stub/one/StubModule.php');
            
            $collection = new GroupCollection(['a' => Component::class, 'b' => BaseObject::class]);
            $collection->fill(new ReflectionClass(StubModule::class));
            expect($collection['a']->getFiles())->contains(__DIR__ . '/stub/one/StubModule.php');
            expect($collection['b']->getFiles())->notContains(__DIR__ . '/stub/one/StubModule.php');
        });
    }
}
