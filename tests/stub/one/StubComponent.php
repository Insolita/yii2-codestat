<?php
/**
 * Created by solly [18.10.17 18:16]
 */

namespace tests\stub\one;

use tests\stub\two\StubInterface;
use yii\base\Component;

class StubComponent extends Component implements StubInterface
{
    private $one;
    
    private $two;
    
    public function one()
    {
        return $this->one;
    }
    
    public function two()
    {
        return $this->two;
    }
    
    public function three()
    {
        return 'three';
    }
    
}
