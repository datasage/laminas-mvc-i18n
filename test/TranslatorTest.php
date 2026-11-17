<?php

declare(strict_types=1);

namespace LaminasTest\Mvc\I18n;

use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\I18n\Translator;
use Laminas\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    public function testIsAnI18nTranslator(): void
    {
        $translator = new Translator($this->createStub(I18nTranslator::class));
        $this->assertInstanceOf(TranslatorInterface::class, $translator);
    }

    public function testIsAValidatorTranslator(): void
    {
        $translator = new Translator($this->createStub(I18nTranslator::class));
        $this->assertInstanceOf(ValidatorTranslatorInterface::class, $translator);
    }

    public function testCanRetrieveComposedTranslator(): void
    {
        $i18nTranslator = $this->createStub(I18nTranslator::class);
        $translator     = new Translator($i18nTranslator);
        $this->assertSame($i18nTranslator, $translator->getTranslator());
    }

    public function testCanProxyToComposedTranslatorMethods(): void
    {
        $i18nTranslator = $this->createMock(I18nTranslator::class);
        $i18nTranslator->expects($this->once())
            ->method('setLocale')
            ->with($this->equalTo('en_US'));

        $translator = new Translator($i18nTranslator);
        $translator->setLocale('en_US');
    }
}
