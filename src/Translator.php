<?php

declare(strict_types=1);

namespace Laminas\Mvc\I18n;

use Laminas\I18n\Translator\TranslatorInterface as I18nTranslatorInterface;
use Laminas\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;
use Override;

use function method_exists;
use function sprintf;

class Translator implements
    I18nTranslatorInterface,
    ValidatorTranslatorInterface
{
    public function __construct(protected I18nTranslatorInterface $translator)
    {
    }

    /**
     * Proxy unknown method calls to underlying translator instance
     *
     * Note: this method is only implemented to keep backwards compatibility
     * with pre-2.3.0 code.
     *
     * @deprecated
     *
     * @param string $method
     * @return mixed
     */
    public function __call($method, array $args)
    {
        if (! method_exists($this->translator, $method)) {
            throw new Exception\BadMethodCallException(sprintf(
                'Unable to call method "%s"; does not exist in translator',
                $method
            ));
        }
        return $this->translator->$method(...$args);
    }

    public function getTranslator(): I18nTranslatorInterface
    {
        return $this->translator;
    }

    /**
     * Translate a message using the given text domain and locale
     *
     * @param string $message
     * @param string $textDomain
     * @param string $locale
     * @return string
     */
    #[Override]
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        return $this->translator->translate($message, $textDomain, $locale);
    }

    /**
     * Provide a pluralized translation of the given string using the given text domain and locale
     *
     * @param string $singular
     * @param string $plural
     * @param int $number
     * @param string $textDomain
     * @param string $locale
     * @return string
     */
    #[Override]
    public function translatePlural($singular, $plural, $number, $textDomain = 'default', $locale = null)
    {
        return $this->translator->translatePlural($singular, $plural, $number, $textDomain, $locale);
    }
}
