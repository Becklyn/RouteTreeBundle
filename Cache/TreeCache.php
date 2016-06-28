<?php

namespace Becklyn\RouteTreeBundle\Cache;

use Becklyn\RouteTreeBundle\Tree\Node;


/**
 *
 */
class TreeCache
{
    /**
     * @var bool
     */
    private $isDebug;

    /**
     * @var string
     */
    private $filePath;



    /**
     * @param string $cacheDir
     * @param bool   $debug
     */
    public function __construct ($cacheDir, $debug)
    {
        $this->filePath = "{$cacheDir}/becklyn/route-tree/tree.cache";
        $this->isDebug = $debug;
    }



    /**
     * @return null|Node[]
     */
    public function getTree ()
    {
        if (!$this->isDebug && is_file($this->filePath))
        {
            return unserialize(file_get_contents($this->filePath));
        }

        return null;
    }



    /**
     * @param array $nodes
     */
    public function setTree (array $nodes)
    {
        // checking could be a race condition, so we silently fail if the dir already exists
        @mkdir(dirname($this->filePath), 0755, true);
        file_put_contents($this->filePath, serialize($nodes));
    }



    /**
     * Clears the cache
     */
    public function clear ()
    {
        if (is_file($this->filePath))
        {
            @unlink($this->filePath);
        }
    }
}
