<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Messenger\Locator;


use Symfony\Component\Messenger\Exception\ExceptionInterface;

class NoProjectionForMessageException extends \LogicException implements ExceptionInterface
{

}