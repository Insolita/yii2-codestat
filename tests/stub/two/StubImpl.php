<?php
/**
 * Created by solly [18.10.17 8:22]
 */

namespace tests\stub\two;

class StubImpl implements StubInterface
{
    public function one()
    {
        return 'one';
    }
    
    public function two()
    {
        return 'two';
    }
    
    public function three()
    {
        return 'three';
    }
    
}
