<?php

namespace LocationBundle\Model;


use LocationBundle\Entity\Country;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds location tree
 * @package LocationBundle
 */
class LocationSetter implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;


    /**
     * Insert country if not exists
     * @param $name
     * @return Country
     */
    public function setCountryByName($name)
    {
        $countryRepo = $this->container
            ->get('orient.em')
            ->getRepository(Country::class);

        $country = $countryRepo->find('name=?', $name);
        if (empty($country)) {
            $country = new Country();
            $country->name = $name;

            $countryRepo->persist($country);
        }

        return $country;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}