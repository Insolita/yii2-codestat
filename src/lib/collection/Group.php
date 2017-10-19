<?php
/**
 * Created by solly [18.10.17 4:46]
 */

namespace insolita\codestat\lib\collection;

class Group
{
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string|callable
     */
    private $rule;
    
    /**
     * @var array
     */
    private $files = [];
    
    /**
     * @param string          $name
     * @param string|callable $rule
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($name, $rule)
    {
        if (!is_string($name) || empty($name)) {
            throw new \InvalidArgumentException($name . ' should be non-empty string');
        }
        if (empty($rule) || !(is_string($rule) || is_callable($rule))) {
            throw new \InvalidArgumentException($name . ' should be non-empty string or callable');
        }
        $this->name = $name;
        $this->rule = $rule;
    }
    
    /**
     * @param string $file
     */
    public function addFile($file)
    {
        if (!in_array($file, $this->files)) {
            $this->files[] = $file;
        }
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
    
    /**
     * @return int
     */
    public function getNumberOfClasses()
    {
        return count($this->files);
    }
    
    /**
     * @param \ReflectionClass $reflection
     *
     * @return boolean
     */
    public function validate(\ReflectionClass $reflection)
    {
        if (is_string($this->rule)) {
            return $reflection->isSubclassOf($this->rule);
        } elseif (is_callable($this->rule)) {
            return call_user_func($this->rule, $reflection);
        } else {
            return false;
        }
    }
}
