<?php

namespace BrighteCapital\QueueClient\queue;

use BrighteCapital\QueueClient\container\Bindings;
use BrighteCapital\QueueClient\container\Container;
use BrighteCapital\QueueClient\queue\factories\StrategyFactory;
use BrighteCapital\QueueClient\strategies\Retry;
use Interop\Queue\Message;

class BrighteQueueClient
{
    /**
     * @var \BrighteCapital\QueueClient\queue\QueueClientInterface
     */
    protected $client;

    /** @var array */
    protected $config;

    /** @var BlockerHandlerInterface */
    protected $blockerHandler;

    /**
     * BrighteQueueClient constructor.
     * @param array $config
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        Bindings::register($config);
    }

    /**
     * @param JobManagerInterface $jobManager
     * @param int $timeout timeout
     * @return mixed
     * @throws \Exception
     */
    public function processMessage(JobManagerInterface $jobManager, $timeout = 0): void
    {
        $message = $this->receive($timeout);
        /** @var BlockerHandlerInterface $blockerHandler */
        $blockerHandler = Container::instance()->get('BlockerHandler');

        /** @var Job $job */
        $job = $jobManager->create($message);

        if ($blockerHandler->checkAndHandle($job) === true) {
            return;
        }

        $job = $jobManager->process($job);

        if ($job->getSuccess() === true) {
            $this->acknowledge($message);

            return;
        }

        $this->reject($message, new Retry($job->getMaxRetry(), $job->getDelay(), $job->getRetryStrategy()));
    }

    /**
     * @param int $timeout timeout
     * @return \Interop\Queue\Message
     * @throws \Exception
     */
    public function receive($timeout = 0): Message
    {
        $message = $this->client->receive($timeout);

        return $message;
    }

    /**
     * @param string $body body
     * @param array $properties properties
     * @param array $headers headers
     * @return \Interop\Queue\Message
     */
    public function createMessage(string $body, array $properties = [], array $headers = []): Message
    {
        return $this->client->createMessage($body, $properties, $headers);
    }

    /**
     * @param \Interop\Queue\Message $message message
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\Exception\InvalidDestinationException
     * @throws \Interop\Queue\Exception\InvalidMessageException
     */
    public function send(Message $message): void
    {
        $this->client->send($message);
    }

    /**
     * @param \Interop\Queue\Message $message message
     */
    public function acknowledge(Message $message): void
    {
        $this->client->acknowledge($message);
    }

    /**
     * @param \Interop\Queue\Message $message message
     * @param \BrighteCapital\QueueClient\strategies\Retry $retry
     * @throws \Exception
     */
    public function reject(Message $message, Retry $retry = null): void
    {
        $strategy = StrategyFactory::create($retry);
        $strategy->handle($message);
    }
}
