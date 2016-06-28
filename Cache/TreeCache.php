<?php

namespace Becklyn\RouteTreeBundle\Cache;

use Becklyn\RouteTreeBundle\Tree\Node;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelInterface;


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
     * @param KernelInterface $kernel
     */
    public function __construct (KernelInterface $kernel)
    {
        $this->isDebug = $kernel->isDebug();
        $this->filePath = $kernel->getCacheDir() . "/becklyn/route-tree/tree.cache";
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
}
