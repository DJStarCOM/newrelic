<?php

namespace DJStarCOM\NewRelic\Exception;

class InvalidCallerInstanceException extends \InvalidArgumentException
{
    /**
     * InvalidCallerInstanceException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct($message = 'You need to provide a instance of an object', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
