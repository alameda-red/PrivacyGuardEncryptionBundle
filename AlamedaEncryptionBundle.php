<?php

namespace Alameda\Bundle\EncryptionBundle;

use Alameda\Bundle\EncryptionBundle\DependencyInjection\AlamedaEncryptionExtension;
use Alameda\Bundle\EncryptionBundle\DependencyInjection\EncryptionConfiguration;
use Alameda\Bundle\EncryptionBundle\Encryption\GnuPG\GnuPGConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Sebastian Kuhlmann <zebba@hotmail.de>
 * @package Alameda\Bundle\EncryptionBundle
 */
class AlamedaEncryptionBundle extends Bundle
{
    /** @var string */
    const SERVICE_NS = 'alameda_encryption';

    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var AlamedaEncryptionExtension $extension */
        $extension = $container->getExtension(self::SERVICE_NS);

        $encryptionConfiguration = new EncryptionConfiguration();

        if (extension_loaded('gnupg')) {
            $encryptionConfiguration->addConfiguration(new GnuPGConfiguration());
        }

        $extension->setEncryptionConfiguration($encryptionConfiguration);
    }
}
