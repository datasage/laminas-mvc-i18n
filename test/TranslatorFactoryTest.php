<?php

declare(strict_types=1);

namespace LaminasTest\Mvc\I18n;

use ArrayAccess;
use ArrayObject;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\I18n\DummyTranslator;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\Mvc\I18n\TranslatorFactory;
use Laminas\ServiceManager\ServiceManager;
use Locale;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function extension_loaded;

class TranslatorFactoryTest extends TestCase
{
    /** @param array<string, mixed> $services */
    private static function container(array $services = []): ServiceManager
    {
        return new ServiceManager(['services' => $services]);
    }

    public function testFactoryReturnsMvcTranslatorDecoratingTranslatorInterfaceServiceWhenPresent(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $container  = self::container([TranslatorInterface::class => $translator]);

        $factory = new TranslatorFactory();
        $test    = $factory($container, TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertSame($translator, $test->getTranslator());
    }

    /** @return array<string, array{0: class-string}> */
    public static function expectedTranslatorProvider(): array
    {
        return extension_loaded('intl')
            ? ['intl-loaded' => [I18nTranslator::class]]
            : ['no-intl-loaded' => [DummyTranslator::class]];
    }

    /** @param class-string $expected */
    #[DataProvider('expectedTranslatorProvider')]
    public function testFactoryReturnsMvcTranslatorDecoratingDefaultTranslatorWhenNoConfigPresent(
        string $expected
    ): void {
        $factory = new TranslatorFactory();
        $test    = $factory(self::container(), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertInstanceOf($expected, $test->getTranslator());
    }

    /** @param class-string $expected */
    #[DataProvider('expectedTranslatorProvider')]
    public function testFactoryReturnsMvcDecoratorDecoratingDefaultTranslatorWhenNoTranslatorConfigPresent(
        string $expected
    ): void {
        $factory = new TranslatorFactory();
        $test    = $factory(self::container(['config' => []]), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertInstanceOf($expected, $test->getTranslator());
    }

    public function testFactoryReturnsMvcDecoratorDecoratingDummyTranslatorWhenTranslatorConfigIsFalse(): void
    {
        $factory = new TranslatorFactory();
        $test    = $factory(self::container(['config' => ['translator' => false]]), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertInstanceOf(DummyTranslator::class, $test->getTranslator());
    }

    /** @param class-string $expected */
    #[DataProvider('expectedTranslatorProvider')]
    public function testFactoryReturnsMvcDecoratorDecoratingDefaultTranslatorWhenEmptyTranslatorConfigPresent(
        string $expected
    ): void {
        $factory = new TranslatorFactory();
        $test    = $factory(self::container(['config' => ['translator' => []]]), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertInstanceOf($expected, $test->getTranslator());
    }

    /** @return array<string, array{0: array<string, mixed>, 1: class-string}> */
    public static function invalidTranslatorConfig(): array
    {
        $expectedTranslator = extension_loaded('intl')
            ? I18nTranslator::class
            : DummyTranslator::class;

        return [
            'null'    => [['translator' => null], $expectedTranslator],
            'true'    => [['translator' => true], $expectedTranslator],
            'zero'    => [['translator' => 0], $expectedTranslator],
            'int'     => [['translator' => 1], $expectedTranslator],
            'float-0' => [['translator' => 0.0], $expectedTranslator],
            'float'   => [['translator' => 1.1], $expectedTranslator],
            'string'  => [['translator' => 'invalid'], $expectedTranslator],
            'object'  => [['translator' => (object) ['translator' => 'invalid']], $expectedTranslator],
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @param class-string $expected
     */
    #[DataProvider('invalidTranslatorConfig')]
    public function testFactoryReturnsMvcDecoratorDecoratingDefaultTranslatorWithInvalidTranslatorConfig(
        array $config,
        string $expected
    ): void {
        $factory = new TranslatorFactory();
        $test    = $factory(self::container(['config' => $config]), TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);
        $this->assertInstanceOf($expected, $test->getTranslator());
    }

    /**
     * @psalm-return array<non-empty-string,array{0:array<string,mixed>|ArrayAccess<string,mixed>}>
     */
    public static function validTranslatorConfig(): array
    {
        $locale = Locale::getDefault() === 'en-US' ? 'de-DE' : Locale::getDefault();
        $config = [
            'locale'                => $locale,
            'event_manager_enabled' => true,
        ];

        return [
            'array'       => [$config],
            'traversable' => [new ArrayObject($config)],
        ];
    }

    /** @param array<string,mixed>|ArrayAccess<string,mixed> $config */
    #[RequiresPhpExtension('intl')]
    #[DataProvider('validTranslatorConfig')]
    public function testFactoryReturnsConfiguredTranslatorWhenValidConfigIsPresent(
        array|ArrayAccess $config
    ): void {
        $container = self::container(['config' => ['translator' => $config]]);

        $factory = new TranslatorFactory();
        $test    = $factory($container, TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);

        $decorated = $test->getTranslator();
        $this->assertInstanceOf(I18nTranslator::class, $decorated);
        $this->assertSame($decorated, $container->get(TranslatorInterface::class));
        $this->assertEquals($config['locale'], $decorated->getLocale());
        $this->assertTrue($decorated->isEventManagerEnabled());
    }

    /** @param array<string,mixed>|ArrayAccess<string,mixed> $config */
    #[RequiresPhpExtension('intl')]
    #[DataProvider('validTranslatorConfig')]
    public function testFactoryReturnsConfiguredTranslatorInjectedWithTranslatorPluginManagerWhenValidConfigIsPresent(
        array|ArrayAccess $config
    ): void {
        $loaders   = new LoaderPluginManager(new ServiceManager());
        $container = self::container([
            'config'                  => ['translator' => $config],
            'TranslatorPluginManager' => $loaders,
        ]);

        $factory = new TranslatorFactory();
        $test    = $factory($container, TranslatorInterface::class);

        $this->assertInstanceOf(MvcTranslator::class, $test);

        $decorated = $test->getTranslator();
        $this->assertInstanceOf(I18nTranslator::class, $decorated);
        $this->assertSame($decorated, $container->get(TranslatorInterface::class));
        $this->assertEquals($config['locale'], $decorated->getLocale());
        $this->assertTrue($decorated->isEventManagerEnabled());
        $this->assertSame($loaders, $decorated->getPluginManager());
    }
}
