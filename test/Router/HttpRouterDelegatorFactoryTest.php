<?php

declare(strict_types=1);

namespace LaminasTest\Mvc\I18n\Router;

use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\I18n\Router\HttpRouterDelegatorFactory;
use Laminas\Mvc\I18n\Router\TranslatorAwareTreeRouteStack;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class HttpRouterDelegatorFactoryTest extends TestCase
{
    public function testFactoryReturnsRouterUntouchedIfNotATranslatorAwareTreeRouteStack(): void
    {
        $router   = (object) [];
        $callback = static fn(): object => $router;

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router, $factory(new ServiceManager(), 'HttpRouter', $callback));
    }

    public function testFactoryReturnsTranslatorAwareRouterWithTranslationsDisabledWhenNoTranslatorInContainer(): void
    {
        $router = $this->createMock(TranslatorAwareTreeRouteStack::class);
        $router->expects($this->once())->method('setTranslatorEnabled')->with(false);
        $router->expects($this->never())->method('setTranslator');
        $callback = static fn(): TranslatorAwareTreeRouteStack => $router;

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router, $factory(new ServiceManager(), 'HttpRouter', $callback));
    }

    public function testFactoryInjectsMvcTranslatorIntoRouterWhenPresentInContainer(): void
    {
        $mvcTranslator  = $this->createStub(TranslatorInterface::class);
        $i18nTranslator = $this->createStub(TranslatorInterface::class);

        $router = $this->createMock(TranslatorAwareTreeRouteStack::class);
        $router->expects($this->never())->method('setTranslatorEnabled');
        $router->expects($this->once())->method('setTranslator')->with($this->identicalTo($mvcTranslator));

        $callback = static fn(): TranslatorAwareTreeRouteStack => $router;

        // Both are registered: the MvcTranslator must win.
        $container = new ServiceManager([
            'services' => [
                'MvcTranslator'            => $mvcTranslator,
                TranslatorInterface::class => $i18nTranslator,
            ],
        ]);

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router, $factory($container, 'HttpRouter', $callback));
    }

    public function testFactoryInjectsTranslatorInterfaceIntoRouterWhenPresentInContainer(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);

        $router = $this->createMock(TranslatorAwareTreeRouteStack::class);
        $router->expects($this->never())->method('setTranslatorEnabled');
        $router->expects($this->once())->method('setTranslator')->with($this->identicalTo($translator));

        $callback = static fn(): TranslatorAwareTreeRouteStack => $router;

        $container = new ServiceManager(['services' => [TranslatorInterface::class => $translator]]);

        $factory = new HttpRouterDelegatorFactory();
        $this->assertSame($router, $factory($container, 'HttpRouter', $callback));
    }
}
