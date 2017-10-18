<?php
/**
 * Created by solly [18.10.17 8:23]
 */

namespace tests\stub\two;

abstract class StubAbstract
{
    public function one()
    {
        return 'one';
    }
    abstract public function two();
    abstract protected function three();
}
