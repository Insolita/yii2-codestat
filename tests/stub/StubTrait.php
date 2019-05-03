<?php
/**
 * Created by solly [18.10.17 22:20]
 */

namespace tests\stub;

use yii\base\Action;
use yii\base\BaseObject;
use yii\base\Behavior;
use yii\base\Component;
use yii\base\Event;
use yii\base\Model;
use yii\base\Module;
use yii\base\Widget;
use yii\console\Controller as ConsoleController;
use yii\db\BaseActiveRecord;
use yii\rest\Controller as RestController;
use yii\web\AssetBundle;
use yii\web\Controller as WebController;

trait StubTrait
{
    protected function files()
    {
        return [
            __DIR__ . '/one/views/default/index.php',
            __DIR__ . '/one/AutoloadExample.php',
            __DIR__ . '/one/HelloController.php',
            __DIR__ . '/two/SiteController.php',
            __DIR__ . '/one/LoginForm.php',
            __DIR__ . '/one/StubAction.php',
            __DIR__ . '/one/StubBehavior.php',
            __DIR__ . '/one/StubEvent.php',
            __DIR__ . '/one/StubModel.php',
            __DIR__ . '/one/StubModule.php',
            __DIR__ . '/two/Bootstrapper.php',
            __DIR__ . '/two/SectionItem.php',
            __DIR__ . '/two/StubAbstract.php',
            __DIR__ . '/two/StubConcrete.php',
            __DIR__ . '/two/StubInterface.php',
            __DIR__ . '/two/StubImpl.php',
        ];
    }
    
    protected function rules()
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
            'Models' => function (\ReflectionClass $reflection) {
                return (
                    $reflection->getParentClass() &&
                    $reflection->getParentClass()->name === Model::class
                );
            },
            'Modules' => Module::class,
            'Widgets' => Widget::class,
            'Components' => Component::class,
            'BaseObjects' => BaseObject::class,
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
    
    protected function failRules()
    {
        return [
            ['a', 'b'],
            ['a' => Event::class, Action::class],
            ['a' => Event::class, 'b' => []],
        ];
    }
}
