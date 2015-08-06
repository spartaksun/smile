<?php

namespace AppBundle\Command;


use PhpOrient\PhpOrient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PersonCommand extends ContainerAwareCommand
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

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $offset = $input->getArgument('offset');
        if (!$offset) {
            return;
        }

        $limit = $input->getArgument('limit');

        if (empty($limit)) {
            $limit = 1000000;
        }

        mb_internal_encoding('utf-8');

        $i = 0;
        $values = [];

        $file = new \SplFileObject($input->getArgument('path'));
        $fileIterator = new \LimitIterator($file, $offset, $limit);

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

                    $dateCodeStart = " date(" . strtotime($row[13]) * 1000 . ")";
                    $dateCodeEnd = empty($row[14])
                        ? $dateCodeStart
                        : " date(" . strtotime($row[14]) * 1000 . ")";

                    if (empty($row[6])) {
                        $birthDate = "date(-30610234800000)";
                    } else {
                        $birthDate = " date(" . strtotime($row[6]) * 1000 . ")";
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

                    $values[] = "(
                            {$row[0]},
                            {$row[1]},
                            {$dateCodeStart},
                            {$dateCodeEnd},
                            '{$firstNameRu}',
                            '{$firstNameUkr}',
                            '{$lastNameRu}',
                            '{$lastNameUkr}',
                            '{$patronymicRu}',
                            '{$patronymicUkr}',
                            {$birthDate},
                            '{$birthLocality}'
                            )";

                    if (count($values) >= 5000) {
                        $output->writeln($i);
                        $this->insert($values);
                    }

                } else {
                    $output->writeln('Skip');
                    var_export($row);
                }
            } catch (\Exception $e) {
                $output->writeln('Exception ' . $e->getMessage());
                foreach($values as $value) {
                    $value = [$value];
                    try{
                        $this->insert($value);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        if (!empty($values)) {
            $output->writeln("insert before exit " . count($values));
            $this->insert($values);
        }
    }

    private function insert(& $values)
    {
        $command = <<<SQL
insert into Person ( sys_id,
ident_code,
ident_code_date_start,
ident_code_date_end,
first_name_ru,
first_name_ukr,
last_name_ru,
last_name_ukr,
patronymic_ru,
patronymic_ukr,
birth_date,
birth_locality ) VALUES
SQL
 . implode(",", $values);


        $values = [];
        $client = $this->getClient();
        $client->connect();
        $client->dbOpen('Smile', 'smile', 'smile');
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

    protected function getClient()
    {
        $client = new PhpOrient();
        $client->hostname = '127.0.0.1';
        $client->port = 2424;
        $client->username = 'root';
        $client->password = 'hello';


        return $client;
    }
}