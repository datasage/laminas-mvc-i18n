<?php

declare(strict_types=1);

namespace Laminas\Mvc\I18n\Router;

use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\Router\Exception;
use Laminas\Router\Http\RouteInterface;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\RequestInterface as Request;
use Override;
use Traversable;

use function iterator_to_array;

/**
 * Translator aware tree route stack.
 *
 * @template TRoute of RouteInterface
 * @extends TreeRouteStack<TRoute>
 */
class TranslatorAwareTreeRouteStack extends TreeRouteStack implements TranslatorAwareInterface
{
    /**
     * Translator used for translatable segments.
     */
    protected ?Translator $translator = null;

    /**
     * Whether the translator is enabled.
     */
    protected bool $translatorEnabled = true;

    /**
     * Translator text domain to use.
     */
    protected string $translatorTextDomain = 'default';

    /**
     * Override TreeRouteStack::factory()
     *
     * Overrides TreeRouteStack::factory() in order to inject the configured
     * translator_text_domain, if present, prior to returning the instance.
     *
     * @param iterable $options
     */
    #[Override]
    public static function factory($options = []): static
    {
        $instance = parent::factory($options);

        $flatOptions = $options;
        if ($flatOptions instanceof Traversable) {
            $flatOptions = iterator_to_array($flatOptions);
        }

        if (isset($flatOptions['translator_text_domain'])) {
            $instance->setTranslatorTextDomain($flatOptions['translator_text_domain']);
        }

        return $instance;
    }

    /**
     * match(): defined by RouteInterface
     *
     * @see    RouteInterface::match()
     *
     * @param  integer|null $pathOffset
     * @return RouteMatch|null
     */
    #[Override]
    public function match(Request $request, $pathOffset = null, array $options = [])
    {
        if ($this->hasTranslator() && $this->isTranslatorEnabled() && ! isset($options['translator'])) {
            $options['translator'] = $this->getTranslator();
        }

        if (! isset($options['text_domain'])) {
            $options['text_domain'] = $this->getTranslatorTextDomain();
        }

        return parent::match($request, $pathOffset, $options);
    }

    /**
     * assemble(): defined by RouteInterface interface.
     *
     * @see    RouteInterface::assemble()
     *
     * @return mixed
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    #[Override]
    public function assemble(array $params = [], array $options = [])
    {
        if ($this->hasTranslator() && $this->isTranslatorEnabled() && ! isset($options['translator'])) {
            $options['translator'] = $this->getTranslator();
        }

        if (! isset($options['text_domain'])) {
            $options['text_domain'] = $this->getTranslatorTextDomain();
        }

        return parent::assemble($params, $options);
    }

    /**
     * setTranslator(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::setTranslator()
     *
     * @param  string     $textDomain
     */
    #[Override]
    public function setTranslator(?Translator $translator = null, $textDomain = null): static
    {
        $this->translator = $translator;

        if ($textDomain !== null) {
            $this->setTranslatorTextDomain($textDomain);
        }

        return $this;
    }

    /**
     * getTranslator(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::getTranslator()
     */
    #[Override]
    public function getTranslator(): ?Translator
    {
        return $this->translator;
    }

    /**
     * hasTranslator(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::hasTranslator()
     */
    #[Override]
    public function hasTranslator(): bool
    {
        return $this->translator !== null;
    }

    /**
     * setTranslatorEnabled(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::setTranslatorEnabled()
     *
     * @param  bool $enabled
     */
    #[Override]
    public function setTranslatorEnabled($enabled = true): static
    {
        $this->translatorEnabled = (bool) $enabled;
        return $this;
    }

    /**
     * isTranslatorEnabled(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::isTranslatorEnabled()
     */
    #[Override]
    public function isTranslatorEnabled(): bool
    {
        return $this->translatorEnabled;
    }

    /**
     * setTranslatorTextDomain(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::setTranslatorTextDomain()
     *
     * @param  string $textDomain
     */
    #[Override]
    public function setTranslatorTextDomain($textDomain = 'default'): static
    {
        $this->translatorTextDomain = (string) $textDomain;
        return $this;
    }

    /**
     * getTranslatorTextDomain(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::getTranslatorTextDomain()
     */
    #[Override]
    public function getTranslatorTextDomain(): string
    {
        return $this->translatorTextDomain;
    }
}
