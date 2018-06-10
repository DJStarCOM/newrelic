<?php

namespace DJStarCOM\NewRelic\Formatter;

interface FormatterInterface
{
    /**
     * @param array $array
     * @return mixed
     */
    public function format(array $array);
}
