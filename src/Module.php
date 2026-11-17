<?php

declare(strict_types=1);

namespace Laminas\Mvc\I18n;

class Module
{
    /**
     * Provide configuration for an application integrating i18n.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
