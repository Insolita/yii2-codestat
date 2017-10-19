<?php
/**
 * Created by solly [19.10.17 1:27]
 */

namespace insolita\codestat\lib\classdetect;

use insolita\codestat\lib\contracts\IClassDetector;
use function is_null;
use const T_INTERFACE;
use const T_TRAIT;

/**
 * The Realization honestly copy-pasted
 * from \SebastianBergmann\PHPLOC\Analyser::preProcessFile()
 */
class TokenizerDetector implements IClassDetector
{
    public function resolveClassName($filePath)
    {
        $className = $this->processFile($filePath);
        if (is_null($className)) {
            return null;
        } else {
            return $className;
        }
    }
    
    /**
     * @param array $tokens
     * @param int   $i
     *
     * @return string
     */
    protected function getNamespaceName(array $tokens, $i)
    {
        if (isset($tokens[$i + 2][1])) {
            $namespace = $tokens[$i + 2][1];
            for ($j = $i + 3; ; $j += 2) {
                if (isset($tokens[$j]) && $tokens[$j][0] == T_NS_SEPARATOR) {
                    $namespace .= '\\' . $tokens[$j + 1][1];
                } else {
                    break;
                }
            }
            return $namespace;
        }
        return false;
    }
    
    /**
     * @param string $namespace
     * @param array  $tokens
     * @param int    $i
     *
     * @return string
     */
    protected function getClassName($namespace, array $tokens, $i)
    {
        $i += 2;
        if (!isset($tokens[$i][1])) {
            return 'invalid class name';
        }
        $className = $tokens[$i][1];
        
        $namespaced = $className === '\\';
        
        while (\is_array($tokens[$i + 1]) && $tokens[$i + 1][0] !== T_WHITESPACE) {
            $className .= $tokens[++$i][1];
        }
        
        if (!$namespaced && $namespace !== false) {
            $className = $namespace . '\\' . $className;
        }
        
        return $className;
    }
    
    /**
     * Extract namespace and class from file
     *
     * @param string $filename
     * Return FQN className
     *
     * @return string|null
     */
    protected function processFile($filename)
    {
        $tokens = \token_get_all(\file_get_contents($filename));
        $numTokens = \count($tokens);
        $namespace = null;
        $className = null;
        
        for ($i = 0; $i < $numTokens; $i++) {
            if (\is_string($tokens[$i])) {
                continue;
            }
            
            switch ($tokens[$i][0]) {
                case T_NAMESPACE:
                    $namespace = $this->getNamespaceName($tokens, $i);
                    break;
                
                case T_CLASS:
                case T_TRAIT:
                case T_INTERFACE:
                    if (!$this->isClassDeclaration($tokens, $i)) {
                        continue;
                    }
                    $className = $this->getClassName($namespace, $tokens, $i);
                    break;
            }
        }
        return $className;
    }
    
    /**
     * @param array $tokens
     * @param int   $start
     *
     * @return bool
     */
    private function getPreviousNonWhitespaceTokenPos(array $tokens, $start)
    {
        if (isset($tokens[$start - 1])) {
            if (isset($tokens[$start - 1][0])
                && $tokens[$start - 1][0] == T_WHITESPACE
                && isset($tokens[$start - 2])) {
                return $start - 2;
            } else {
                return $start - 1;
            }
        }
        
        return false;
    }
    
    /**
     * @param array $tokens
     * @param int   $i
     *
     * @return bool
     */
    private function isClassDeclaration(array $tokens, $i)
    {
        $n = $this->getPreviousNonWhitespaceTokenPos($tokens, $i);
        
        return !isset($tokens[$n])
            || !\is_array($tokens[$n])
            || !\in_array($tokens[$n][0], [T_DOUBLE_COLON, T_NEW], true);
    }
}
