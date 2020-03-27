<?php

namespace App\Test\Strategies;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Strategies\Retry;

class RetryTest extends BaseTestCase
{
    protected $retry;
    protected function setUp()
    {
        parent::setUp();
        $this->retry = new Retry(0, 0, 'test');
    }

    public function testGetterSetter()
    {
        $this->assertEquals(0, $this->retry->getDelay());
        $this->assertEquals(0, $this->retry->getMaxRetryCount());
        $this->assertEquals('test', $this->retry->getStrategy());
        $this->assertEquals('', $this->retry->getErrorMessage());
        $this->retry->setDelay(2);
        $this->retry->setMaxRetryCount(2);
        $this->retry->setStrategy('strategy');
        $this->retry->setErrorMessage('error');
        $this->assertEquals(2, $this->retry->getDelay());
        $this->assertEquals(2, $this->retry->getMaxRetryCount());
        $this->assertEquals('strategy', $this->retry->getStrategy());
        $this->assertEquals('error', $this->retry->getErrorMessage());
    }
}
