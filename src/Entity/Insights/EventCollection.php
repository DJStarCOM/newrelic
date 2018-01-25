<?php

namespace DJStarCOM\NewRelic\Entity\Insights;

class EventCollection extends \ArrayObject implements \JsonSerializable
{
    public function add(Event $event)
    {
        $this->append($event);
        return $this;
    }

    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }
}
