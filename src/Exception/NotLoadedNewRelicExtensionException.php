<?php

namespace DJStarCOM\NewRelic\Exception;

class NotLoadedNewRelicExtensionException extends \RuntimeException
{
    /**
     * NotLoadedNewRelicExtensionException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct($message = 'NewRelic extension is not loaded', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
