<?php

namespace LocationBundle;


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
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
