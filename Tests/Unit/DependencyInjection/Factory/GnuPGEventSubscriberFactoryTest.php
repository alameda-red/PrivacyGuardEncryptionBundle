<?php

namespace Alameda\Bundle\EncryptionBundle\Tests\Unit\DependencyInjection\Factory;

use Alameda\Bundle\EncryptionBundle\DependencyInjection\Factory\EventSubscriberFactory;
use Prophecy\Argument;

/**
 * @author Sebastian Kuhlmann <zebba@hotmail.de>
 * @package Alameda\Bundle\EncryptionBundle
 */
class GnuPGEventSubscriberFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterDecryptSubscriber()
    {
        $definition = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $definition->setPublic(false)->shouldBeCalled();
        $definition->setArguments(Argument::type('array'))->shouldBeCalled();
        $definition->setFactoryService(Argument::type('string'))->shouldBeCalled();
        $definition->setFactoryMethod('createDecrypter')->shouldBeCalled();
        $definition->addTag(Argument::type('string'))->shouldBeCalled();
        $definition = $definition->reveal();

        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container
            ->register(Argument::containingString('decrypt.gnupg.'), Argument::type('string'))
            ->willReturn($definition)
            ->shouldBeCalled()
        ;
        $container = $container->reveal();

        $factory = new EventSubscriberFactory();

        $listenerId = $factory->registerDecryptSubscriber($container, 'id', 'decryptKey', 'decryptPassword', true);

        $this->assertGreaterThan(2, strlen($listenerId));
        $this->assertStringEndsWith('id', $listenerId);
    }

    public function testRegisterEncryptSubscriber()
    {
        $definition = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $definition->setPublic(false)->shouldBeCalled();
        $definition->setArguments(Argument::type('array'))->shouldBeCalled();
        $definition->setFactoryService(Argument::type('string'))->shouldBeCalled();
        $definition->setFactoryMethod('createEncrypter')->shouldBeCalled();
        $definition->addTag(Argument::type('string'))->shouldBeCalled();
        $definition = $definition->reveal();

        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container
            ->register(Argument::containingString('encrypt.gnupg'), Argument::type('string'))
            ->willReturn($definition)
            ->shouldBeCalled()
        ;
        $container = $container->reveal();

        $factory = new EventSubscriberFactory();

        $listenerId = $factory->registerEncryptSubscriber($container, 'id', 'encryptKey', 'signKey');

        $this->assertGreaterThan(2, strlen($listenerId));
        $this->assertStringEndsWith('id', $listenerId);
    }
}
