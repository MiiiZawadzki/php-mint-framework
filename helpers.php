<?php

if (!function_exists('app_version')) {
    /**
     * Get application version from composer.json
     *
     * @return string
     */
    function app_version(): string
    {
        $composerPath = __DIR__.'/composer.json';

        if (!file_exists($composerPath)) {
            return '0.0.0';
        }

        $composerData = json_decode(file_get_contents($composerPath), true);

        return $composerData['version'] ?? '0.0.0';
    }
}
