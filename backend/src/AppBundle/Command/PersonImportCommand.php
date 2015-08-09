<?php

namespace AppBundle\Command;


use PhpOrient\PhpOrient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpOrient\Protocols\Binary\Operations;

/**
 * Import people from CSV file
 * @package AppBundle\Command
 */
class PersonImportCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('person:import')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'path to souce file'
            )
            ->addArgument(
                'offset',
                InputArgument::REQUIRED,
                'Enter offset'
            )
            ->addArgument(
                'limit',
                InputArgument::OPTIONAL,
                'Enter limit'
            );
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        mb_internal_encoding('utf-8');

        $offset = $input->getArgument('offset');
        if (!$offset) {
            return $output->writeln('Error: offset can not be empty');
        }

        $limit = $input->getArgument('limit');
        if (empty($limit)) {
            $limit = 1000;
        }

        $file = new \SplFileObject($input->getArgument('path'));
        $fileIterator = new \LimitIterator($file, $offset, $limit);

        $i = 0;
        foreach ($fileIterator as $num => $line) {
            $i++;
            $row = str_getcsv($line, "|");

            try {
                $row = $this->convert($row);

                if (count($row) > 7
                    && !empty($row[0])
                    && !empty($row[1])
                    && !empty($row[3])
                    && !empty($row[4])
                ) {

                    $lastNamePr = $this->prepareName($row[3]);
                    if (empty($lastNamePr[1]) || empty($lastNamePr[2])) {
                        continue;
                    }

                    $lastNameUkr = $lastNamePr[1][0];
                    $lastNameRu = $lastNamePr[2][0];

                    $firstNamePr = $this->prepareName($row[4]);
                    if (empty($firstNamePr[1]) || empty($firstNamePr[2])) {
                        continue;
                    }

                    $firstNameUkr = $firstNamePr[1][0];
                    $firstNameRu = $firstNamePr[2][0];

                    $patronymicUkr = '';
                    $patronymicRu = '';

                    if (!empty($row[5])) {
                        $patronymicPr = $this->prepareName($row[5]);
                        if (!empty($patronymicPr[1]) && !empty($patronymicPr[2])) {
                            $patronymicUkr = $patronymicPr[1][0];
                            $patronymicRu = $patronymicPr[2][0];
                        }
                    }

                    $dateCodeStart = date("Y-m-d 00:00:00", strtotime($row[13]));
                    $dateCodeEnd = empty($row[14])
                        ? $dateCodeStart
                        : date("Y-m-d 00:00:00", strtotime($row[14]));

                    if (empty($row[6])) {
                        $birthDate = "1000-01-01 00:00:00";
                    } else {
                        $birthDate = date("Y-m-d 00:00:00", strtotime($row[6]));
                    }

                    $locationRid = $this->locationRid(
                        [
                            'country' => !empty($row[7]) ? trim($row[7]) : null,
                            'region' => !empty($row[8]) ? trim($row[8]) : null,
                            'district' => !empty($row[9]) ? trim($row[9]) : null,
                            'city' => !empty($row[10]) ? trim($row[10]) : null,
                        ]
                    );

                    $values = [
                        'id' => $row[1],
                        'sys' => $row[0],
                        'birth_date' => $birthDate,
                        'start_date' => $dateCodeStart,
                        'end_date' => $dateCodeEnd,
                        'first_ru' => $firstNameRu,
                        'last_ru' => $lastNameRu,
                        'first_ukr' => $firstNameUkr,
                        'last_ukr' => $lastNameUkr,
                        'patronymic_ru' => $patronymicRu,
                        'patronymic_ukr' => $patronymicUkr,
                        'born_at' => $locationRid,
                    ];

                    $this->insert($values);

                } else {
                    $output->writeln('Skip: ' . var_export($row . true));
                }

            } catch (\Exception $e) {
                $output->writeln('Exception ' . $e->getMessage());
            }
        }

        return $output->writeln('Finish!');
    }

    /**
     * Bind location to person
     * @param $personRid
     * @param $locationRid
     * @return mixed
     */
    private function bindLocation($personRid, $locationRid)
    {
        $client = $this->prepareClient();
        $bornAt = $client->command("CREATE EDGE BornAt FROM {$personRid} TO {$locationRid}");

        return $bornAt->getRid()->__toString();
    }

    /**
     * Create location or select if exists
     * @param array $location
     * @return string
     */
    private function locationRid(array $location)
    {
        $country = $this->getCountry($location['country']);
        $region = !empty($location['region'])
            ? $this->getRegion($location['region'], $country)
            : null;

        if (!empty($region)) {
            $district = !empty($location['district'])
                ? $this->getDistrict($location['district'], $region)
                : null;
        }

        if (!empty($district)) {
            return $this->getCity($location['city'], $district);
        }

        return $this->getCity($location['city'], $country);
    }

    /**
     * Insert person if not exists
     * @param $values
     */
    private function insert($values)
    {
        $client = $this->prepareClient();
        $person = $client->command('SELECT FROM Person WHERE id = ' . $values['id'] . '');

        $set = <<<HERE
            [{ "@type":"d",
            "@class":"Name",
            "start_date": "$values[start_date]",
            "end_date": "$values[end_date]",
            "first_ru": "$values[first_ru]",
            "last_ru": "$values[last_ru]",
            "first_ukr": "$values[first_ukr]",
            "last_ukr": "$values[last_ukr]",
            "patronymic_ru": "$values[patronymic_ru]",
            "patronymic_ukr": "$values[patronymic_ukr]" }]
HERE;

        if (!$person) {
            $command = "insert into Person SET
                id={$values['id']} ,
                sys={$values['sys']},
                birth_date='{$values['birth_date']}',
                \"name\" = {$set}";
            $person = $client->command($command);
            $this->bindLocation($person->getRid()->__toString(), $values['born_at']);

        } else {
            $command = "UPDATE Person ADD \"name\" = {$set} WHERE id={$values['id']}";
            $client->command($command);
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    private function prepareName($name)
    {
        preg_match_all("~(\D+)\s+\(((\D+))\)~", $name, $matches);

        return $matches;
    }

    /**
     * Convert CSV row into array and decode to utf-8
     * @param $row
     * @return mixed
     */
    private function convert($row)
    {
        array_walk($row, function (& $element, $key) {
            if ($key > 2 && $key <= 10) {
                $element = @iconv('cp1251', 'utf-8//IGNORE', $element . " ");
                $element = str_replace("'", "`", $element);
            }
            $element = trim($element);
        });

        return $row;
    }

    /**
     * Insert country or select it if exists
     * @param $locationName
     * @return string
     */
    private function getCountry($locationName)
    {
        $client = $this->prepareClient();
        $location = $client->command('SELECT FROM Country WHERE name = "' . $locationName . '"');

        /* @var $location \PhpOrient\Protocols\Binary\Data\Record */
        if (empty($location)) {
            $insert = 'INSERT INTO Country SET name="' . $locationName . '" ';
            $location = $client->command($insert);
        }

        return $location->getRid()->__toString();
    }

    /**
     * @return PhpOrient
     */
    private function prepareClient()
    {
        $client = $this->getContainer()->get('orient');
        $client->connect();
        $client->dbOpen('Smile', 'smile', 'smile');

        return $client;
    }

    /**
     * Insert city or select it if exists
     * @param $cityName
     * @param $location
     * @return string
     */
    private function getCity($cityName, $location)
    {
        $client = $this->prepareClient();
        $sql = "SELECT FROM City WHERE  name = \"{$cityName}\" AND location = {$location} ";
        $loc = $client->command($sql);

        /* @var $loc \PhpOrient\Protocols\Binary\Data\Record */
        if (empty($loc)) {
            $insert = 'INSERT INTO City SET name="' . $cityName . '", location = ' . $location;
            $loc = $client->command($insert);
        }

        return $loc->getRid()->__toString();
    }

    /**
     * Insert region or select it if exists
     * @param $region
     * @param $country
     * @return string
     */
    private function getRegion($region, $country)
    {
        $client = $this->prepareClient();
        $sql = 'SELECT FROM Region WHERE country = ' . $country . ' AND name = "' . $region . '"';
        $location = $client->command($sql);

        /* @var $location \PhpOrient\Protocols\Binary\Data\Record */
        if (empty($location)) {
            $insert = 'INSERT INTO Region SET name="' . $region . '", country = ' . $country;
            $location = $client->command($insert);
        }

        return $location->getRid()->__toString();
    }

    /**
     * Insert district or select it if exists
     * @param $districtName
     * @param $region
     * @return string
     */
    private function getDistrict($districtName, $region)
    {
        $client = $this->prepareClient();
        $sql = 'SELECT FROM District WHERE region = ' . $region . ' AND name = "' . $districtName . '"';
        $location = $client->command($sql);

        /* @var $location \PhpOrient\Protocols\Binary\Data\Record */
        if (empty($location)) {
            $insert = 'INSERT INTO District SET name="' . $districtName . '", region = ' . $region;
            $location = $client->command($insert);
        }

        return $location->getRid()->__toString();
    }

}
