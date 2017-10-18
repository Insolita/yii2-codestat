<?php
/**
 * Created by solly [18.10.17 7:56]
 */

namespace tests\stub\one;

use yii\base\Behavior;

class StubBehavior extends Behavior
{
    public $param1;
    
    public $param2;
    
    public function events()
    {
        return parent::events();
    }
    
    public function someFunc($events, $owner)
    {
        foreach ($events as $event => $handler) {
            $owner->on($event, is_string($handler) ? [$this, $handler] : $handler);
        }
    }
}
