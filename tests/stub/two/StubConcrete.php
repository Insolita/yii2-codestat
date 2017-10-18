<?php
/**
 * Created by solly [18.10.17 8:24]
 */

namespace tests\stub\two;

class StubConcrete extends StubAbstract
{
    public function two()
    {
        return 2;
    }
    
    protected function three()
    {
        return 3;
    }
    
}
