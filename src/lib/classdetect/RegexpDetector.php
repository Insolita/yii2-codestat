<?php
/**
 * Created by solly [19.10.17 1:12]
 */

namespace insolita\codestat\lib\classdetect;

use insolita\codestat\lib\contracts\ClassDetectorInterface;

class RegexpDetector implements ClassDetectorInterface
{
    /**
     * @param string $filePath
     *
     * @return null|string
     */
    public function resolveClassName($filePath)
    {
        $content = file_get_contents($filePath);
        $className = $this->extractClass($content);
        if ($className !== null) {
            $namespace = $this->extractNamespace($content);
            return $namespace . '\\' . $className;
        } else {
            return null;
        }
    }
    
    /**
     * @param string $content
     *
     * @return string
     */
    protected function extractNamespace($content)
    {
        preg_match('/^\s*namespace\s*([\\\\\w]+);\s*$/mis', $content, $matches);
        return $matches[1] ?? '';
    }
    
    /**
     * @param $content
     *
     * @return string|null
     */
    protected function extractClass($content)
    {
        preg_match('/\s*(?:class|trait|interface)\s+([\w]+)\s*[\{]*/mis', $content, $matches);
        return $matches[1] ?? null;
    }
}
