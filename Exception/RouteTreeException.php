<?php

namespace Becklyn\RouteTreeBundle\Exception;


use Throwable;


class RouteTreeException extends \Exception
{
    /**
     * @inheritdoc
     */
    public function __construct (string $message = "", Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

}
