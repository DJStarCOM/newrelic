<?php

namespace DJStarCOM\NewRelic\Exception;

class NotLoadedNewRelicExtensionException extends \RuntimeException
{
    public function __construct($message = 'NewRelic extension is not loaded', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
