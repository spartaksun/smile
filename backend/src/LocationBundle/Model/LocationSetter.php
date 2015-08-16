<?php

namespace LocationBundle\Model;


use LocationBundle\Entity\Country;
use LocationBundle\Entity\Region;
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
        return $this->upsertCountry($locationParams['country']);


    }

    /**
     * Checks if all params are exist
     * @param $params
     * @throws \ErrorException
     */
    private function checkParams($params)
    {
        $expectedParams = [
            'country',
            'district',
            'region',
            'city'
        ];

        foreach ($expectedParams as $key) {
            if (!isset($params[$key])) {
                throw new \ErrorException("Key {$key} not found.");
            }
        }
    }

    /**
     * Insert country if not exists
     * @param $countryName
     * @return Country
     */
    private function upsertCountry($countryName)
    {
        $countryRepo = $this->container
            ->get('orient.em')
            ->getRepository(Country::class);

        $country = $countryRepo->find('name=?', $countryName);
        if (empty($country)) {
            $country = new Country();
            $country->name = $countryName;

            $countryRepo->persist($country);
        }

        return $country;
    }

    /**
     * @param $regionName
     * @param Country $country
     * @return Region
     */
    private function upsertRegion($regionName, Country $country)
    {
        $regionRepo = $this->container
            ->get('orient.em')
            ->getRepository(Region::class);

        $region = $regionRepo->find('name=? AND country=?', [$regionName, $country->getRid()]);
        if(empty($region)) {
            $region = new Region();
            $region->name = $regionName;

            $regionRepo->persist($region);
        }

        return $region;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}