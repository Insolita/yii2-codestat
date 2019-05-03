<?php
/**
 * Created by solly [19.10.17 1:05]
 */

namespace insolita\codestat\lib\contracts;

interface ClassDetectorInterface
{
    /**
     * Return FQN by full filePath, or null, if not found
     * @param string $filePath
     *
     * @return string|null
     */
    public function resolveClassName($filePath);
}
