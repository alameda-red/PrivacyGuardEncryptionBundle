<?php

namespace Alameda\Bundle\EncryptionBundle\Tests\Unit\Event\GnuPG;

use Alameda\Bundle\EncryptionBundle\Event\Response\EncryptSubscriber;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @author Sebastian Kuhlmann <zebba@hotmail.de>
 * @package Alameda\Bundle\EncryptionBundle
 */
class EncryptSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider nonRelevantResponses
     */
    public function testOnKernelResponsetNonRelevantResponses(FilterResponseEvent $args, $configureRoute)
    {
        $encrypter = $this->prophesize('Alameda\Bundle\EncryptionBundle\Encryption\EncrypterInterface');
        $encrypter = $encrypter->reveal();

        $router = $this->prophesize('Symfony\Component\Routing\RouterInterface');

        if($configureRoute) {
            $route = $this->prophesize('Symfony\Component\Routing\Route');
            $route->hasOption('encryption')->willReturn(true)->shouldBeCalled();
            $route->getOption('encryption')->willReturn('bar')->shouldBeCalled();
            $route = $route->reveal();

            $collection = $this->prophesize('Symfony\Component\Routing\RouteCollection');
            $collection->get('foo')->willReturn($route);
            $collection = $collection->reveal();

            $router->getRouteCollection()->willReturn($collection);
        }

        $router = $router->reveal();

        $subscriber = new EncryptSubscriber('id', $encrypter, $router);
        $subscriber->onKernelResponse($args);
    }

    public function nonRelevantResponses()
    {
        $nonMasterRequest = $this->prophesize('Symfony\Component\HttpKernel\Event\FilterResponseEvent');
        $nonMasterRequest->isMasterRequest()->willReturn(false)->shouldBeCalled();
        $nonMasterRequest = $nonMasterRequest->reveal();

        $parameterBag = $this->prophesize('Symfony\Component\HttpFoundation\ParameterBag');
        $parameterBag->get('_route')->willReturn('foo')->shouldBeCalled();
        $parameterBag = $parameterBag->reveal();

        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');
        $request->attributes = $parameterBag;

        $nonConfiguredRoute = $this->prophesize('Symfony\Component\HttpKernel\Event\FilterResponseEvent');
        $nonConfiguredRoute->isMasterRequest()->willReturn(true)->shouldBeCalled();
        $nonConfiguredRoute->getRequest()->willReturn($request);
        $nonConfiguredRoute = $nonConfiguredRoute->reveal();

        return [
            [$nonMasterRequest, false],
            [$nonConfiguredRoute, true]
        ];
    }

    public function testOnKernelRequestRelevantResponses()
    {
        $attributesBag = $this->prophesize('Symfony\Component\HttpFoundation\ParameterBag');
        $attributesBag->get('_route')->willReturn('foo')->shouldBeCalled();
        $attributesBag = $attributesBag->reveal();

        $requestBag = $this->prophesize('Symfony\Component\HttpFoundation\ParameterBag');
        $requestBag = $requestBag->reveal();

        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');
        $request->attributes = $attributesBag;
        $request->request = $requestBag;
        $request = $request->reveal();

        $headerBag = $this->prophesize('Symfony\Component\HttpFoundation\HeaderBag');
        $headerBag->set(Argument::type('string'), Argument::type('string'))->shouldBeCalled();
        $headerBag = $headerBag->reveal();

        $response = $this->prophesize('Symfony\Component\HttpFoundation\Response');
        $response->headers = $headerBag;
        $response->getContent()->willReturn('decrypted')->shouldBeCalled();
        $response->setContent('encrypted')->shouldBeCalled();
        $response = $response->reveal();

        $args = $this->prophesize('Symfony\Component\HttpKernel\Event\FilterResponseEvent');;
        $args->isMasterRequest()->willReturn(true);
        $args->getRequest()->willReturn($request);
        $args->getResponse()->willReturn($response);
        $args->setResponse(Argument::type('Symfony\Component\HttpFoundation\Response'))->shouldBeCalled();
        $args = $args->reveal();

        $encrypter = $this->prophesize('Alameda\Bundle\EncryptionBundle\Encryption\EncrypterInterface');
        $encrypter->encrypt(Argument::type('string'))->willReturn('encrypted');
        $encrypter = $encrypter->reveal();

        $route = $this->prophesize('Symfony\Component\Routing\Route');
        $route->hasOption('encryption')->willReturn(true)->shouldBeCalled();
        $route->getOption('encryption')->willReturn('id')->shouldBeCalled();
        $route = $route->reveal();

        $collection = $this->prophesize('Symfony\Component\Routing\RouteCollection');
        $collection->get('foo')->willReturn($route);
        $collection = $collection->reveal();

        $router = $this->prophesize('Symfony\Component\Routing\RouterInterface');
        $router->getRouteCollection()->willReturn($collection);
        $router = $router->reveal();

        $subscriber = new EncryptSubscriber('id', $encrypter, $router);
        $subscriber->onKernelResponse($args);
    }
}
