<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventStore\Plugin;


abstract class PluginPriorities
{

    const CONVERT = 2;
    const PROJECTION = -1;
    const PUBLISH = -2;

}