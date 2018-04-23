<?php

declare(strict_types=1);


namespace AFS\ProophBundle\Command;


use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateProjectionTableCommand extends Command
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
        $this->setName('afs:prooph:projection-table')
             ->setDescription('Creates the default projection table');
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
CREATE TABLE `projections` (
  `no` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `position` LONGTEXT,
  `state` LONGTEXT,
  `status` VARCHAR(28) NOT NULL,
  `locked_until` CHAR(26),
  CHECK (`position` IS NULL OR JSON_VALID(`position`)),
  CHECK (`state` IS NULL OR JSON_VALID(`state`)),
  PRIMARY KEY (`no`),
  UNIQUE KEY `ix_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
EOT;
        $statement = $this->connection->prepare($sql);
        $statement->execute();

        $output->writeln('Done');
    }

}