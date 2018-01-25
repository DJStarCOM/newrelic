<?php

namespace DJStarCOM\NewRelic\Entity\Insights;

use Respect\Validation\Validator;

class Event implements \JsonSerializable
{
    public $eventType;

    public function jsonSerialize()
    {
        Validator::stringType()
            ->notEmpty()
            ->setName('eventType')
            ->check($this->eventType);

        return $this;
    }
}
