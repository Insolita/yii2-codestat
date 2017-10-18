<?php
/**
 * Created by solly [18.10.17 16:45]
 */

namespace tests;

use Codeception\Specify;
use function expect_not;
use function expect_that;
use insolita\codestat\lib\collection\Group;
use InvalidArgumentException;
use ReflectionClass;
use tests\stub\one\AutoloadExample;
use tests\stub\one\HelloController;
use tests\stub\two\SectionItem;
use tests\stub\one\StubAction;
use tests\stub\one\StubBehavior;
use tests\stub\one\StubComponent;
use tests\stub\one\StubEvent;
use tests\stub\one\StubModel;
use tests\stub\two\Bootstrapper;
use tests\stub\two\SiteController;
use tests\stub\two\StubAbstract;
use tests\stub\two\StubConcrete;
use tests\stub\two\StubFinal;
use tests\stub\two\StubImpl;
use tests\stub\two\StubInterface;
use yii\base\Action;
use yii\base\Behavior;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\Controller;
use yii\base\Event;
use yii\base\Model;
use yii\web\Controller as WebController;
use yii\base\Object;
use function expect;
use yii\base\Widget;

class GroupTest extends \PHPUnit\Framework\TestCase
{
    use Specify;
    
    public function testInitial()
    {
        $this->specify('valid initial values', function () {
            $group = new Group('my', Object::class);
            expect($group->getName())->equals('my');
            expect($group->getFiles())->isEmpty();
            expect($group->getNumberOfClasses())->equals(0);
        });
        $this->specify('bad calls1', function () {
            new Group('my', null);
        }, ['throws' => InvalidArgumentException::class]);
        $this->specify('bad calls2', function () {
            new Group('my', []);
        }, ['throws' => InvalidArgumentException::class]);
        $this->specify('bad calls3', function () {
            new Group(null, InvalidArgumentException::class);
        }, ['throws' => InvalidArgumentException::class]);
        $this->specify('bad calls4', function () {
            new Group('', InvalidArgumentException::class);
        }, ['throws' => InvalidArgumentException::class]);
    }
    
    public function testAddFiles()
    {
        $this->specify('add different files', function () {
            $group = new Group('my', Object::class);
            $group->addFile(__DIR__ . '/stub/one/StubModel.php');
            $group->addFile(__DIR__ . '/stub/one/StubEvent.php');
            expect($group->getFiles())->count(2);
            expect($group->getNumberOfClasses())->equals(count($group->getFiles()));
            expect($group->getFiles())->equals([
                __DIR__ . '/stub/one/StubModel.php',
                __DIR__ . '/stub/one/StubEvent.php',
            ]);
        });
        $this->specify('add same files, uniqueness verified', function () {
            $group = new Group('my', Object::class);
            $group->addFile(__DIR__ . '/stub/one/StubModel.php');
            $group->addFile(__DIR__ . '/stub/one/StubModel.php');
            expect($group->getFiles())->count(1);
            expect($group->getNumberOfClasses())->equals(count($group->getFiles()));
            expect($group->getFiles())->equals([
                __DIR__ . '/stub/one/StubModel.php',
            ]);
        });
    }
    
    public function testValidation()
    {
        $this->specify('Yii class', function () {
            $group = new Group('my', Object::class);
            expect('same class', $group->validate(new ReflectionClass(Object::class)))->false();
            expect('inherited class', $group->validate(new ReflectionClass(StubAction::class)))->true();
            expect('std class', $group->validate(new ReflectionClass(StubImpl::class)))->false();
            expect('final class', $group->validate(new ReflectionClass(StubFinal::class)))->true();
        });
        $this->specify('closure rule', function () {
            $group = new Group('my', function (\ReflectionClass $reflection) {
                return $reflection->isSubclassOf(Object::class);
            });
            expect('same class', $group->validate(new ReflectionClass(Object::class)))->false();
            expect('inherited class', $group->validate(new ReflectionClass(StubAction::class)))->true();
            expect('std class', $group->validate(new ReflectionClass(StubImpl::class)))->false();
            expect('final class', $group->validate(new ReflectionClass(StubFinal::class)))->true();
        });
        $this->specify('strict parent rule', function () {
            $group = new Group('my', function (\ReflectionClass $reflection) {
                
                return ($reflection->getParentClass()
                    && $reflection->getParentClass()->name === Component::class);
            });
            expect('same class', $group->validate(new ReflectionClass(Component::class)))->false();
            expect('inherited class', $group->validate(new ReflectionClass(StubComponent::class)))->true();
            expect('std class', $group->validate(new ReflectionClass(StubImpl::class)))->false();
            expect('final class', $group->validate(new ReflectionClass(StubFinal::class)))->false();
        });
        $this->specify('Interface', function () {
            $group = new Group('my', StubInterface::class);
            expect('same class', $group->validate(new ReflectionClass(StubInterface::class)))->false();
            expect('inherited class', $group->validate(new ReflectionClass(StubImpl::class)))->true();
            expect('wrong class', $group->validate(new ReflectionClass(SectionItem::class)))->false();
        });
        $this->specify('Abstract', function () {
            $group = new Group('my', StubAbstract::class);
            expect('same class', $group->validate(new ReflectionClass(StubAbstract::class)))->false();
            expect('inherited class', $group->validate(new ReflectionClass(StubConcrete::class)))->true();
            expect('wrong class', $group->validate(new ReflectionClass(SectionItem::class)))->false();
        });
        $this->specify('Final', function () {
            $group = new Group('my', StubFinal::class);
            expect('same class', $group->validate(new ReflectionClass(StubFinal::class)))->false();
            expect('wrong class', $group->validate(new ReflectionClass(SectionItem::class)))->false();
        });
        
        $this->specify('yii assertions', function () {
            $group = new Group('my', Widget::class);
            expect_that($group->validate(new \ReflectionClass(AutoloadExample::class)));
            $group = new Group('my', Controller::class);
            expect_that($group->validate(new \ReflectionClass(HelloController::class)));
            expect_that($group->validate(new \ReflectionClass(SiteController::class)));
            $group = new Group('my', WebController::class);
            expect_not($group->validate(new \ReflectionClass(HelloController::class)));
            expect_that($group->validate(new \ReflectionClass(SiteController::class)));
            $group = new Group('my', Behavior::class);
            expect_that($group->validate(new \ReflectionClass(StubBehavior::class)));
            $group = new Group('my', Action::class);
            expect_that($group->validate(new \ReflectionClass(StubAction::class)));
            $group = new Group('my', Component::class);
            expect_that($group->validate(new \ReflectionClass(StubComponent::class)));
            $group = new Group('my', Event::class);
            expect_that($group->validate(new \ReflectionClass(StubEvent::class)));
            $group = new Group('my', Model::class);
            expect_that($group->validate(new \ReflectionClass(StubModel::class)));
            $group = new Group('my', BootstrapInterface::class);
            expect_that($group->validate(new \ReflectionClass(Bootstrapper::class)));
        });
    }
}
