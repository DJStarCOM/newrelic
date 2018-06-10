<?php

namespace DJStarCOM\NewRelic\Formatter;

class ArgumentsFormatter implements FormatterInterface
{
    /**
     * @param array $arguments
     * @return \ArrayObject
     */
    public function format(array $arguments)
    {
        $output = new \ArrayObject();
        foreach ($arguments as $key => $value) {
            if (null === $value || is_scalar($value)) {
                $output[$key] = $value;
            } else {
                $this->expandArgument($value, $output);
            }
        }

        return $output;
    }

    private function expandArgument($argument, &$flatten)
    {
        foreach ($argument as $key => $value) {
            if (null === $value || is_scalar($value)) {
                $flatten[$key] = $value;
            } else {
                $flatten[$key] = @json_encode($value);
            }
        }
    }
}
