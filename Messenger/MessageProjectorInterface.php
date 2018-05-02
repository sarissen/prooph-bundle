<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Messenger;


interface MessageProjectorInterface
{

    /**
     * @return array
     */
    public static function getProjectedMessages(): array;

}