<?php

namespace AppBundle\Command;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpOrient\Protocols\Binary\Operations;

class PersonCommand extends DbCommand
{

    protected function configure()
    {
        $this->setName('db:person')
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
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

                    $birthLocality = '';
                    if (!empty($row[7])) {
                        $birthLocality .= $row[7] . ", ";
                    }
                    if (!empty($row[8])) {
                        $birthLocality .= $row[8] . " ОБЛ. ";
                    }
                    if (!empty($row[9])) {
                        $birthLocality .= $row[9] . " Р-Н ";
                    }
                    if (!empty($row[10])) {
                        $birthLocality .= $row[10] . " ";
                    }

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

    private function insert($values)
    {
        $client = $this->getContainer()->get('orient');
        $client->connect();
        $client->dbOpen('Smile', 'smile', 'smile');

        $exists = $client->command('SELECT FROM Person WHERE id = ' . $values['id'] . '');


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
        if(!$exists) {
            $command = "insert into Person SET
id={$values['id']} ,
sys={$values['sys']},
birth_date='{$values['birth_date']}',
\"name\" = {$set}
";
        } else {
            $command = "UPDATE Person ADD name = {$set} WHERE id={$values['id']}";
        }

        $client->command($command);

    }

    private function prepareName($name)
    {
        preg_match_all("~(\D+)\s+\(((\D+))\)~", $name, $matches);

        return $matches;
    }


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

}