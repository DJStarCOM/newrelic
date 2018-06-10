<?php

namespace DJStarCOM\NewRelic;

use DJStarCOM\NewRelic\Config\TransactionConfig;
use DJStarCOM\NewRelic\Exception\InvalidCallerInstanceException;
use DJStarCOM\NewRelic\Exception\NotLoadedNewRelicExtensionException;
use DJStarCOM\NewRelic\Formatter\ArgumentsFormatter;
use DJStarCOM\NewRelic\Formatter\FormatterInterface;

class Transaction
{
    private $instance;
    private $config;
    private $formatter;
    private $isBackground;

    /**
     * Transaction constructor.
     * @param $instance
     * @param TransactionConfig $config
     */
    public function __construct($instance, TransactionConfig $config)
    {
        if (!is_object($instance)) {
            throw new InvalidCallerInstanceException();
        }

        $this->instance = $instance;
        $this->config = $config;
        $this->formatter = new ArgumentsFormatter();

        if (!extension_loaded('newrelic')) {
            throw new NotLoadedNewRelicExtensionException();
        }
    }

    /**
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @param string $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        $this->transactionStart($name, $arguments);

        try {
            return call_user_func_array([$this->instance, $name], $arguments);
        } catch (\Exception $genericException) {
            $this->transactionFail($name, $genericException);
            throw $genericException;
        } finally {
            $this->transactionEnd($name);
        }
    }

    /**
     * @param $customParameters
     */
    private function addNewRelicParameter($customParameters)
    {
        foreach ($customParameters as $key => $value) {
            if (null === $value || is_scalar($value)) {
                newrelic_add_custom_parameter($key, $value);
            } else {
                newrelic_add_custom_parameter($key, @json_encode($value));
            }
        }
    }

    /**
     * @param string $name
     * @param mixed $arguments
     */
    private function transactionStart($name, $arguments)
    {
        if (!$this->shouldBeMonitored($name)) {
            return;
        }

        if (PHP_SAPI === 'cli') {
            newrelic_background_job(true);
        }

        newrelic_set_appname($this->config->applicationName);
        newrelic_start_transaction($this->config->applicationName);
        newrelic_name_transaction($this->config->transactionName);
        $customParameters = $this->formatter->format($arguments);
        $this->addNewRelicParameter($customParameters);
    }

    /**
     * @param $name
     * @return bool
     */
    private function shouldBeMonitored($name)
    {
        return !$this->config->monitoredMethodName || $name == $this->config->monitoredMethodName;
    }

    /**
     * @param $name
     */
    private function transactionEnd($name)
    {
        if (!$this->shouldBeMonitored($name)) {
            return;
        }

        newrelic_end_transaction();
    }

    /**
     * @param $name
     * @param \Exception $genericException
     */
    private function transactionFail($name, \Exception $genericException)
    {
        if (!$this->shouldBeMonitored($name)) {
            return;
        }

        newrelic_notice_error($genericException->getMessage(), $genericException);
    }
}
