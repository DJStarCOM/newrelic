<?php

namespace DJStarCOM\NewRelic;

use DJStarCOM\NewRelic\Config\TransactionConfig;
use DJStarCOM\NewRelic\Stub\Foo;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    private $transaction;
    public static $transactionName;
    public static $applicationNameStarted;
    public static $customParameters;
    public static $endTransaction;
    public static $exceptionMessage;
    public static $exception;
    public static $applicationName;
    public static $extensionAvailable;
    public static $transactionsCount;
    public static $isBackground;

    private $config;

    public function setUp()
    {
        $this->config = new TransactionConfig();
        $this->config->applicationName = 'RTG';
        $this->config->transactionName = 'Track';
        self::$extensionAvailable = true;
        $this->transaction = new Transaction(new Foo(), $this->config);
        self::$endTransaction = false;
        self::$transactionsCount = 0;
    }

    public function testHasAbilityToSetApplicationName()
    {
        $this->transaction->bar();

        $this->assertEquals('RTG', self::$applicationName);
    }

    public function testCanSetBackgroundJob()
    {
        $this->transaction->bar();

        $this->assertEquals(true, self::$isBackground);
    }

    public function testStartATransaction()
    {
        $this->transaction->bar();

        $this->assertEquals('RTG', self::$applicationNameStarted);
    }

    public function testCanSetTransactionName()
    {
        $this->transaction->bar();

        $this->assertEquals('Track', self::$transactionName);
    }

    public function testCanAddComplexArgumentsToNewRelic()
    {
        $complexArgument = [
            0 => 'simple',
            1 => ['array' => 'simple'],
            2 => ['string' => 'simple', 'named array' => ['json'], 'object' => new \stdClass()]
        ];

        $this->transaction->bar($complexArgument[0], $complexArgument[1], $complexArgument[2]);

        $this->assertEquals([
            '0' => 'simple',
            'array' => 'simple',
            'string' => 'simple',
            'named array' => '["json"]',
            'object' => '{}'
        ], self::$customParameters);
    }

    public function testArgumentAreCorrectedPassedToObject()
    {
        $argument = 'NRI';

        $returnedArgument = $this->transaction->bar($argument);

        $this->assertEquals($argument, $returnedArgument);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage NRI
     */
    public function testExceptionStillTheSame()
    {
        $expectedException = new \InvalidArgumentException('NRI');

        $this->transaction->fooThrowThisException($expectedException);
    }

    public function testExceptionIsRecordedOnNewRelic()
    {
        $expectedException = new \InvalidArgumentException('NRI');

        try {
            $this->transaction->fooThrowThisException($expectedException);
        } catch (\Exception $exception) {
            //Ignoring exceptions
        }

        $this->assertNotEmpty(self::$exceptionMessage);
        $this->assertEquals($expectedException, self::$exception);
    }

    public function testEndTransactionAndSendToNewRelicWhenAnExceptionHappen()
    {
        $expectedException = new \InvalidArgumentException('NRI');

        try {
            $this->transaction->fooThrowThisException($expectedException);
        } catch (\Exception $exception) {
            //Ignoring exceptions
        }

        $this->assertTrue(self::$endTransaction);
    }

    public function testEndTransactionAndSendToNewRelic()
    {
        $this->transaction->bar();

        $this->assertTrue(self::$endTransaction);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNonObject()
    {
        new Transaction('NRI', new TransactionConfig());
    }

    /**
     * @expectedException \DJStarCOM\NewRelic\Exception\NotLoadedNewRelicExtensionException
     */
    public function testExceptionIfExtensionIsNotLoaded()
    {
        self::$extensionAvailable = false;

        new Transaction(new \StdClass, new TransactionConfig());
    }

    public function testTransactioIsStartedOnlyForDesiredMethodCall()
    {
        $this->config->monitoredMethodName = 'foo';

        $this->transaction->bar();
        $this->transaction->foo();

        $this->assertEquals(1, self::$transactionsCount);
    }
}

function newrelic_start_transaction($appName)
{
    TransactionTest::$applicationNameStarted = $appName;
    TransactionTest::$transactionsCount++;
}

function newrelic_name_transaction($transactionName)
{
    TransactionTest::$transactionName = $transactionName;
}

function newrelic_add_custom_parameter($key, $value)
{
    TransactionTest::$customParameters[$key] = $value;
    return true;
}

function newrelic_end_transaction()
{
    TransactionTest::$endTransaction = true;
}

function newrelic_notice_error($exceptionMessage, \Exception $exception = null)
{
    TransactionTest::$exceptionMessage = $exceptionMessage;
    TransactionTest::$exception = $exception;
}

function extension_loaded($extension)
{
    return TransactionTest::$extensionAvailable;
}

function newrelic_set_appname($appName)
{
    TransactionTest::$applicationName = $appName;
}

function newrelic_background_job($isBackground)
{
    TransactionTest::$isBackground = $isBackground;
}
