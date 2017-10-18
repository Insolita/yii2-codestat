<?php
/**
 * Created by solly [18.10.17 7:53]
 */

namespace tests\stub\one;

use yii\base\Model;

class StubModel extends Model
{
    public function methodOne()
    {
        $scenarios = [self::SCENARIO_DEFAULT => []];
        foreach ($this->getValidators() as $validator) {
            foreach ($validator->on as $scenario) {
                $scenarios[$scenario] = [];
            }
            foreach ($validator->except as $scenario) {
                $scenarios[$scenario] = [];
            }
        }
        $names = array_keys($scenarios);
        
        foreach ($this->getValidators() as $validator) {
            if (empty($validator->on) && empty($validator->except)) {
                foreach ($names as $name) {
                    foreach ($validator->attributes as $attribute) {
                        $scenarios[$name][$attribute] = true;
                    }
                }
            } elseif (empty($validator->on)) {
                foreach ($names as $name) {
                    if (!in_array($name, $validator->except, true)) {
                        foreach ($validator->attributes as $attribute) {
                            $scenarios[$name][$attribute] = true;
                        }
                    }
                }
            } else {
                foreach ($validator->on as $name) {
                    foreach ($validator->attributes as $attribute) {
                        $scenarios[$name][$attribute] = true;
                    }
                }
            }
        }
        
        foreach ($scenarios as $scenario => $attributes) {
            if (!empty($attributes)) {
                $scenarios[$scenario] = array_keys($attributes);
            }
        }
        
        return $scenarios;
    }
    
    public function methodTwo()
    {
        $reflector = new \ReflectionClass($this);
        return $reflector->getShortName();
    }
}
