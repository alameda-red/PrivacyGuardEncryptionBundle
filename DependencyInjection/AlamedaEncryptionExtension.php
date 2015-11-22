<?php

namespace Alameda\Bundle\EncryptionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Sebastian Kuhlmann <zebba@hotmail.de>
 * @package Alameda\Bundle\EncryptionBundle
 */
class AlamedaEncryptionExtension extends Extension
{
    /** @var EncryptionConfiguration */
    private $configuration;

    /**
     * @param EncryptionConfiguration $configuration
     */
    public function setEncryptionConfiguration(EncryptionConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // call init() on all registered configurations

        $this->configuration->init($container, $config);
    }
}
