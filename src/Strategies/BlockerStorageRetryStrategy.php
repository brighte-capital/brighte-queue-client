<?php

namespace BrighteCapital\QueueClient\Strategies;

use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use Exception;
use Interop\Queue\Message;

class BlockerStorageRetryStrategy extends BlockerRetryStrategy
{
    /**
     * @param Message $message
     * @throws Exception
     */
    protected function onMaxRetryReached(Message $message): void
    {
        parent::onMaxRetryReached($message);

        $this->storeMessage($message);
    }
}
