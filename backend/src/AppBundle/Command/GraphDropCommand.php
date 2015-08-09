<?php

namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Truncates and drops all classes in graph
 * @package AppBundle\Command
 */
class GraphDropCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('graph:drop');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('orient');
        $client->connect();
        $client->dbOpen('Smile', 'smile', 'smile');

        foreach($this->classNames() as $class) {
            $client->command("TRUNCATE CLASS {$class}");
            $client->command("Drop CLASS {$class}");
        }
    }

    /**
     * @return array of classes to clean
     */
    private function classNames()
    {
        return [
            'Person',
            'Name',
            'Country',
            'Region',
            'District',
            'City',
            'Street',
            'Building',
            'Location',
            'BornAt',
        ];
    }
}