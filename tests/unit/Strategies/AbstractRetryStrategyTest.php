<?php

namespace App\Test\Strategies;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Storage\NullStorage;
use BrighteCapital\QueueClient\Strategies\AbstractRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Enqueue\Sqs\SqsMessage;
use Interop\Queue\Message;
use Psr\Log\NullLogger;

class AbstractRetryStrategyTest extends BaseTestCase
{
    protected $client;
    protected $retry;
    protected $logger;
    protected $notification;
    protected $message;
    /** @var AbstractRetryStrategy */
    protected $strategy;

    protected function setUp()
    {
        parent::setUp();
        $this->client = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();
        $this->retry = $this->getMockBuilder(Retry::class)->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(NullLogger::class)->disableOriginalConstructor()->getMock();
        $this->notification = $this
            ->getMockBuilder(NullNotificationChannel::class)->disableOriginalConstructor()->getMock();
        $this->message = $this->getMockBuilder(SqsMessage::class)->disableOriginalConstructor()->getMock();

        $anonymousClass = new class (
            $this->retry,
            $this->client,
            1,
            $this->logger,
            $this->notification
        ) extends AbstractRetryStrategy {
            public function getClass()
            {
                return $this;
            }

            protected function onMaxRetryReached(Message $message): void
            {
                //Do Nothing
            }
        };
        $this->strategy = $anonymousClass->getClass();
    }

    public function testHandle()
    {
        $this->message->expects($this->once())->method('getProperty')->willReturn(1);
        $this->retry->expects($this->once())->method('getMaxRetryCount')->willReturn(2);
        $this->client->expects($this->once())->method('delay');
        $this->strategy->handle($this->message);
    }

    public function testHandleReachMax()
    {
        $this->message->expects($this->once())->method('getProperty')->willReturn(2);
        $this->retry->expects($this->atLeast(2))->method('getMaxRetryCount')->willReturn(1);
        $this->client->expects($this->never())->method('delay');
        $this->notification->expects($this->once())->method('send');
        $this->logger->expects($this->once())->method('critical');
        $this->strategy->handle($this->message);
    }
}