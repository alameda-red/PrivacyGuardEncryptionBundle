<?php

namespace Alameda\Bundle\EncryptionBundle\Tests\Unit\Event\GnuPG;

use Alameda\Bundle\EncryptionBundle\Event\Request\DecryptSubscriber;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author Sebastian Kuhlmann <zebba@hotmail.de>
 * @package Alameda\Bundle\EncryptionBundle
 */
class DecryptSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider nonRelevantRequests
     */
    public function testOnKernelRequestNonRelevantRequests(GetResponseEvent $args)
    {
        $decrypter = $this->prophesize('Alameda\Bundle\EncryptionBundle\Encryption\DecrypterInterface');
        $decrypter = $decrypter->reveal();

        $subscriber = new DecryptSubscriber($decrypter);
        $subscriber->onKernelRequest($args);
    }

    public function nonRelevantRequests()
    {
        $nonMasterRequest = $this->prophesize('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        $nonMasterRequest->isMasterRequest()->willReturn(false)->shouldBeCalled();
        $nonMasterRequest = $nonMasterRequest->reveal();

        $headerBag = $this->prophesize('Symfony\Component\HttpFoundation\HeaderBag');
        $headerBag->get('Content-type', null)->willReturn(null)->shouldBeCalled();
        $headerBag = $headerBag->reveal();

        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');
        $request->headers = $headerBag;

        $notEncrypted = $this->prophesize('Symfony\Component\HttpKernel\Event\GetResponseEvent');;
        $notEncrypted->isMasterRequest()->willReturn(true);
        $notEncrypted->getRequest()->willReturn($request);
        $notEncrypted = $notEncrypted->reveal();

        return [
            [$nonMasterRequest],
            [$notEncrypted]
        ];
    }

    public function testOnKernelRequestRelevantRequest()
    {
        $headerBag = $this->prophesize('Symfony\Component\HttpFoundation\HeaderBag');
        $headerBag->get('Content-type', null)->willReturn('application/pgp-encrypted')->shouldBeCalled();
        $headerBag = $headerBag->reveal();

        $parameterBag = $this->prophesize('Symfony\Component\HttpFoundation\ParameterBag');
        $parameterBag->set(Argument::containingString('decrypted'), Argument::type('string'))->shouldBeCalled();
        $parameterBag->set(Argument::containingString('signature'), Argument::any())->shouldBeCalled();
        $parameterBag = $parameterBag->reveal();

        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');
        $request->getContent()->willReturn('foo')->shouldBeCalled();
        $request->headers = $headerBag;
        $request->request = $parameterBag;
        $request = $request->reveal();

        $args = $this->prophesize('Symfony\Component\HttpKernel\Event\GetResponseEvent');;
        $args->isMasterRequest()->willReturn(true);
        $args->getRequest()->willReturn($request);
        $args = $args->reveal();

        $decrypter = $this->prophesize();
        $decrypter->willImplement('Alameda\Bundle\EncryptionBundle\Encryption\DecrypterInterface');
        $decrypter->willImplement('Alameda\Bundle\EncryptionBundle\Encryption\AuthorizedSignatureInterface');
        $decrypter->decrypt(Argument::type('string'))->willReturn('foo');
        $decrypter->getSignature()->willReturn('bar');
        $decrypter = $decrypter->reveal();

        $subscriber = new DecryptSubscriber($decrypter);
        $subscriber->onKernelRequest($args);
    }
}
