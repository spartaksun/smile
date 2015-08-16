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
     * @param array $locationParams
     */
    public function setByNames(array $locationParams)
    {
        $this->checkParams($locationParams);
        $country = $this->setCountryByName($locationParams['country']);

    }

    /**
     * Checks if all params are exist
     * @param $params
     * @throws \ErrorException
     */
    private function checkParams($params)
    {
        $expectedParams = [
            'country', 'district', 'region', 'city'
        ];

        foreach($expectedParams as $key => $value) {
            if(!isset($params[$key])) {
                throw new \ErrorException("Key {$key} not found.");
            }
        }
    }

    /**
     * Insert country if not exists
     * @param $name
     * @return Country
     */
    private function setCountryByName($name)
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