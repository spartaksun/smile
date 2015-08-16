<?php
/**
 * Created by A.Belyakovskiy.
 * Date: 8/16/15
 * Time: 8:17 PM
 */

namespace PersonBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('test');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {

        $setter = $this->getContainer()->get('location.setter');
        $country = $setter->setByNames([
            'country' => 'НАРНИЯ'
        ]);

        var_dump($country);

    }
}
