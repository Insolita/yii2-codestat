<?php
/**
 * Created by solly [18.10.17 17:50]
 */

namespace tests\stub\two;

use yii\base\Object;

final class StubFinal extends Object
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
