<?php
/**
 * Created by solly [18.10.17 11:02]
 */

namespace insolita\codestat\lib\collection;

class GroupCollection extends BaseCollection
{
    /**@var Group[] $items * */
    protected $items;
    
    /**
     * @param \insolita\codestat\lib\collection\Group[] $items
     */
    public function __construct(array $items = [])
    {
        $rules = [];
        foreach ($items as $name => $rule) {
            if ($rule instanceof Group) {
                $rules[$rule->getName()] = $rule;
            } else {
                $rules[$name] = new Group($name, $rule);
            }
        }
        parent::__construct($rules);
    }
    
    public function fill(\ReflectionClass $reflection)
    {
        foreach ($this->items as $name => $group) {
            if ($group->validate($reflection)) {
                $group->addFile($reflection->getFileName());
                break;
            }
        }
        return $this;
    }
    
    public static function make(array $items)
    {
        return new static($items);
    }
}
