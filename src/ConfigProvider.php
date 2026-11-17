<?php

declare(strict_types=1);

namespace Laminas\Mvc\I18n;

use Laminas\Router\Http\TreeRouteStack;

class ConfigProvider
{
    /**
     * Provide dependency configuration for an application integrating i18n.
     *
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Provide dependency configuration for an application integrating i18n.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getDependencyConfig(): array
    {
        return [
            'aliases'    => [
                'MvcTranslator' => Translator::class,
            ],
            'delegators' => [
                'HttpRouter'          => [Router\HttpRouterDelegatorFactory::class],
                TreeRouteStack::class => [Router\HttpRouterDelegatorFactory::class],
            ],
            'factories'  => [
                Translator::class => TranslatorFactory::class,
            ],
        ];
    }
}
