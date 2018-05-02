<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Messenger;


class ChainProjector
{
    /**
     * @var callable[]
     */
    private $projectors;

    /**
     * @param callable[] $projectors
     */
    public function __construct(array $projectors)
    {
        if (empty($projectors)) {
            throw new \InvalidArgumentException('A collection of message projectors requires at least one projector.');
        }

        $this->projectors = $projectors;
    }

    public function __invoke($message)
    {
        $results = array();

        foreach ($this->projectors as $projector) {
            $results[] = $projector($message);
        }

        return $results;
    }
}