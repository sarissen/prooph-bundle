<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph;


use AFS\ProophBundle\Prooph\EventSourcing\AggregateChanged;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;
use Ramsey\Uuid\Uuid;

class AggregateChangedMessageFactory implements MessageFactory
{

    /**
     * @inheritdoc
     */
    public function createMessageFromArray(string $messageName, array $messageData): Message
    {
        if (! isset($messageData['message_name'])) {
            $messageData['message_name'] = $messageName;
        }

        if (! isset($messageData['uuid'])) {
            $messageData['uuid'] = Uuid::uuid4();
        }

        if (! isset($messageData['created_at'])) {
            $messageData['created_at'] = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }

        if (! isset($messageData['metadata'])) {
            $messageData['metadata'] = [];
        }

        return AggregateChanged::fromArray($messageData);
    }

}