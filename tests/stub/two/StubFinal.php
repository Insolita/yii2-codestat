<?php
/**
 * Created by solly [18.10.17 17:50]
 */

namespace tests\stub\two;

use yii\base\BaseObject;

final class StubFinal extends BaseObject
{
    public function one()
    {
        return 'a';
    }
    
    public function two()
    {
        return 'b';
    }
    
    protected function three()
    {
        return 'c';
    }
}
