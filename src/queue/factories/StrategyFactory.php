<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\strategies\AbstractRetryStrategy;
use BrighteCapital\QueueClient\strategies\DefaultRetryStrategy;
use BrighteCapital\QueueClient\strategies\Retry;
use BrighteCapital\QueueClient\strategies\StorageRetryStrategy;

class StrategyFactory
{
    public static function create(QueueClientInterface $queueClient, Retry $retry = null): AbstractRetryStrategy
    {
        if (!$retry) {
            $retry = new Retry(0, 0, DefaultRetryStrategy::class);
        }

        switch ($retry->getStrategy()) {
            case DefaultRetryStrategy::class:
                return new DefaultRetryStrategy($retry, $queueClient);

            case StorageRetryStrategy::class:
                return new StorageRetryStrategy($retry, $queueClient);
        }
    }
}