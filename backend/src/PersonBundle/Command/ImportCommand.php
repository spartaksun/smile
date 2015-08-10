<?php
namespace PersonBundle\Command;

use PhpOrient\PhpOrient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('db:import')
            ->setDescription('Import someone')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Who do you want to import data?'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        mb_internal_encoding('utf-8');

        $client = $this->getClient();



        $file = fopen('/var/www/smile/backend/data/2.txt', 'r');
        if ($file) {

            $i = 0;
            while (($row = fgetcsv($file, null, "|")) !== false && $i < 5000000) {
                $i++;
                if ($i < 2) continue;

                array_walk($row, function (& $element) {
                    $element = @iconv('cp1251', 'utf-8//IGNORE', $element . " ");
                    $element = trim($element);

                });

                $streetName = @iconv('cp1251', 'utf-8//IGNORE', $row[6] . " ");
                $this->parseStreet($streetName);
            }
        }

        $output->writeln('Import finished');
    }

    protected function getClient()
    {
        $client = new PhpOrient();
        $client->hostname = '127.0.0.1';
        $client->port = 2424;
        $client->username = 'root';
        $client->password = 'root';

        $client->connect();
        $client->dbOpen('Smile', 'root', 'root');

        return $client;
    }

    private function makeAddress($row)
    {
        /* [0] =>
string(30) "Системный номер "
[1] =>
string(16) "Код ДРФО "
[2] =>
string(34) "Країна проживання "
[3] =>
string(36) "Область проживання "
[4] =>
string(32) "Район проживання "
[5] =>
string(68) "Назва населенного пункту проживання "
[6] =>
string(45) "Назва вулиці проживання "
[7] =>
string(47) "Номер будинку проживання "
[8] =>
string(49) "Літера будинку проживання "
[9] =>
string(43) "Номер квартири/кімнати "
[10] =>
string(67) "Номер телефону за місцем проживання "
[11] =>
string(78) "Дата реєстрації коду за місцем проживання "
[12] =>
string(11) "Особа " */
    }

    private function prepareAddresses(array $row, & $result)
    {
        $result[$row[3]][$row[4]][$row[5]][$row[6]][$row[7] . $row[8]] = $row[9];
    }



    private function parseStreet($fullName)
    {
        $prefixIds = [
            'ВУЛ' => 1,
            'ПР-Т' => 2,
            'ПРОВ' => 3,
            'Ж-М' => 4,
            'Б-Р' => 5,
            'ШОСЕ' => 6,
            'М-Н' => 7,
            'КВАР.' => 8,
            'М-ЧКО' => 9,
            'МКР-Н' => 10,
            'ПРО-Д' => 11,
            'ЗА-Д' => 12,
            'ТУПИК' => 13,
            'ДОР.' => 14,
            'ПЛ' => 15,
            'ДІЛЬН' => 16,
            'В-ЗД' => 17,
            'НАБ' => 18,
            'УЗВІЗ' => 19,
            'САД' => 20,
            'ТЕР-Я' => 21,
            'ЛІНІЯ' => 22,
            'ТРАКТ' => 23,
            'АЛЕЯ' => 24,
            'П-СТ' => 25,
            'ШЛЯХ' => 26,
            'В-Ч' => 27,
            'СЕЛ' => 28,
            'СПУСК' => 29,
            'СКВЕР' => 30,
            'СТ' => 31,
            'ПАРК' => 32,
            'П-Д' => 33,
            'УРОЧ.' => 34,
            'КУР-Т' => 35,
            'ПРИЧ' => 36,
            'ОБ-Д' => 37,
            'Р-Д' => 38,
            'ПЛЯЖ' => 39,
        ];

        $position = mb_strpos($fullName, " ");
        $prefix = trim(mb_substr($fullName, 0, $position), "\s\.");

        if($prefix == 'НЕВ') {
            return false;
        }

        $prefixId = array_key_exists($prefix, $prefixIds) ? $prefixIds[$prefix] : 0;
        $cityName = trim(mb_substr($fullName, $position + 1));

        if($cityName == 'НЕВІДОМА' || $cityName == 'ВІДСУТНЯ') {
            return false;
        }

        return [
            'prefix' => $prefixId,
            'cityName' => $cityName,
        ];


    }
}