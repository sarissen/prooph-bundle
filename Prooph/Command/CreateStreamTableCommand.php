<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Prooph\Command;


use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateStreamTableCommand extends Command
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * CreateProjectionTableCommand constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('afs:prooph:stream-table')
             ->setDescription('Creates the default stream table');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = <<<EOT
CREATE TABLE `event_streams` (
  `no` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `real_stream_name` VARCHAR(150) NOT NULL,
  `stream_name` CHAR(41) NOT NULL,
  `metadata` LONGTEXT NOT NULL,
  `category` VARCHAR(150),
  CHECK (`metadata` IS NOT NULL OR JSON_VALID(`metadata`)),
  PRIMARY KEY (`no`),
  UNIQUE KEY `ix_rsn` (`real_stream_name`),
  KEY `ix_cat` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
EOT;
        $statement = $this->connection->prepare($sql);
        $statement->execute();

        $output->writeln('Done');
    }
}