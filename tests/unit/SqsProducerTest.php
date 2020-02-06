<?php

namespace tests\unit;

use BrighteCapital\QueueClient\queue\sqs\SqsContext;
use BrighteCapital\QueueClient\queue\sqs\SqsProducer;
use Enqueue\Sqs\SqsClient;
use Enqueue\Sqs\SqsDestination;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;

class SqsProducerTest extends TestCase
{
    /**
     * @var \BrighteCapital\QueueClient\queue\sqs\SqsContext|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;
    /**
     * @var \BrighteCapital\QueueClient\queue\sqs\SqsProducer
     */
    private $producer;
    /**
     * @var \Enqueue\Sqs\SqsDestination|\PHPUnit\Framework\MockObject\MockObject
     */
    private $destination;
    /**
     * @var \Enqueue\Sqs\SqsDestination|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sqsClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->createMock(SqsContext::class);
        $this->producer = new SqsProducer($this->context);
        $this->destination = $this->createMock(SqsDestination::class);
        $this->sqsClient = $this->createMock(SqsClient::class);
    }

    public function testSend()
    {
        $msg = $this->createMock(SqsMessage::class);
        $region = 'ap-east-2';
        $delay = 10;
        $queueName = 'queue.name.fifo';
        $queueUrl = 'queue.url.abc.com.amazon';
        $deDupId = '1';
        $groupId = '1';
        $messageBody = 'this is the messageBody';

        $msg->expects($this->once())
            ->method('getBody')
            ->willReturn($messageBody);

        $this->destination->expects($this->once())
            ->method('getRegion')
            ->willReturn($region);

        $msg->expects($this->exactly(2))
            ->method('getDelaySeconds')
            ->willReturn($delay);

        $msg->expects($this->once())
            ->method('getHeaders')
            ->willReturn([]);

        $properties = [
            'service' => 'salesforce',
            'method' => 'createAccount'
        ];
        $msg->expects($this->once())
            ->method('getProperties')
            ->willReturn($properties);

        $this->destination->expects($this->once())
            ->method('getQueueName')
            ->willReturn($queueName);

        $msg->expects($this->exactly(2))
            ->method('getMessageDeduplicationId')
            ->willReturn($deDupId);

        $msg->expects($this->exactly(2))
            ->method('getMessageGroupId')
            ->willReturn($groupId);


        $this->context->expects($this->once())
            ->method('getQueueUrl')
            ->willReturn($queueUrl);

        $this->context->expects($this->once())->method('getSqsClient')->willReturn($this->sqsClient);

        $expectedArgumentFormat = [
            '@region' => $region,
            'MessageBody' => $messageBody,
            'QueueUrl' => $queueUrl,
            'DelaySeconds' => $delay,
            'MessageAttributes' => [
                'service' => ['DataType' => 'String', 'StringValue' => 'salesforce'],
                'method' => ['DataType' => 'String', 'StringValue' => 'createAccount'],

            ],
            'MessageDeduplicationId' => $deDupId,
            'MessageGroupId' => $groupId,
        ];

        $this->sqsClient->expects($this->once())->method('sendMessage')->with($expectedArgumentFormat);
        $this->producer->send($this->destination, $msg);
    }
}