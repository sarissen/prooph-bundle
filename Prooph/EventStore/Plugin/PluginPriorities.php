<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\EventStore\Plugin;


abstract class PluginPriorities
{

    const CONVERT_LOAD = -1;
    const PROJECTION = -1;
    const PUBLISH = -2;

}