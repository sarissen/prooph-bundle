<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Command;


use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateSingleStreamCommand extends Command
{

    /**
     * @var EventStore
     */
    private $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('afs:prooph:create')
            ->setDescription('Creates a new prooph single stream.')
            ->setHelp('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->eventStore->create(new Stream(new StreamName('order'), new \ArrayIterator()));
        $this->eventStore->create(new Stream(new StreamName('transaction'), new \ArrayIterator()));

        $output->writeln('Done.');
    }

}