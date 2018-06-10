<?php

namespace DJStarCOM\NewRelic\Entity\Insights;

class EventCollection extends \ArrayObject implements \JsonSerializable
{
    /**
     * @param Event $event
     * @return $this
     */
    public function add(Event $event)
    {
        $this->append($event);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }
}
