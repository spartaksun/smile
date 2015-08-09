<?php

namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates vertex and edge classes
 * @package AppBundle\Command
 */
class GraphInitCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('graph:init');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('orient');
        $client->connect();
        $client->dbOpen('Smile', 'smile', 'smile');

        $commands = [

             /* Person */

            'CREATE CLASS Name EXTENDS V',

            'CREATE PROPERTY Name.start_date DATE',
            'CREATE PROPERTY Name.end_date DATE',
            'CREATE PROPERTY Name.first_ru STRING',
            'CREATE PROPERTY Name.last_ru STRING',
            'CREATE PROPERTY Name.first_ukr STRING',
            'CREATE PROPERTY Name.last_ukr STRING',
            'CREATE PROPERTY Name.patronymic_ru STRING',
            'CREATE PROPERTY Name.patronymic_ukr STRING',

            'CREATE CLASS Person EXTENDS V',

            'CREATE PROPERTY Person.sys LONG',
            'CREATE PROPERTY Person.id LONG',
            'CREATE PROPERTY Person.birth_date DATE',
            'CREATE PROPERTY Person.name EMBEDDEDLIST Name',
            'CREATE INDEX Person.id UNIQUE',

             /* Locations */

            'CREATE CLASS Location ABSTRACT',
            'CREATE PROPERTY Location.name STRING',

            'CREATE CLASS Country EXTENDS Location',
            'CREATE INDEX Country.name UNIQUE',

            'CREATE CLASS Region EXTENDS Location',
            'CREATE PROPERTY Region.country LINK Country',
            'CREATE INDEX name_country ON Region (name, country) UNIQUE',

            'CREATE CLASS District EXTENDS Location',
            'CREATE PROPERTY District.region LINK Region',
            'CREATE INDEX name_region ON District (name, region) UNIQUE',

            'CREATE CLASS City EXTENDS Location',
            'CREATE PROPERTY City.location LINK Location',
            'CREATE INDEX name_location ON City (name, location) UNIQUE',

            'CREATE CLASS Street EXTENDS Location',
            'CREATE PROPERTY Street.city LINK City',
            'CREATE INDEX name_city ON Street (name, city) UNIQUE',

            'CREATE CLASS Building EXTENDS V',
            'CREATE PROPERTY Building.street LINK Street',
            'CREATE PROPERTY Building.number STRING',
            'CREATE PROPERTY Building.point STRING',
            'CREATE INDEX number_point_street ON Building (number, point, street) UNIQUE',

            'CREATE CLASS BornAt EXTENDS E'

        ];

        foreach($commands as $command) {
            $client->command($command);
        }
    }

}