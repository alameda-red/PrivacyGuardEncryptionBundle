<?php

namespace Alameda\Bundle\EncryptionBundle\DependencyInjection;

use Alameda\Bundle\EncryptionBundle\Encryption\EncryptionConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EncryptionConfiguration
{
    /** @var EncryptionConfigurationInterface[] */
    private $configurations = [];

    /**
     * @param EncryptionConfigurationInterface $configuration
     */
    public function addConfiguration(EncryptionConfigurationInterface $configuration)
    {
        $this->configurations[$configuration->getType()] = $configuration;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    public function init(ContainerBuilder $container, array $config)
    {
        /** @var EncryptionConfigurationInterface $configuration */
        foreach ($this->configurations as $configuration) {
            $configuration->setContainerBuilder($container);
            $configuration->init($config);
        }
    }
}
