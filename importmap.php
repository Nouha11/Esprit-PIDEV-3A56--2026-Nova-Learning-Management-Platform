<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'chart.js' => [
        'version' => '4.5.1',
    ],
    '@kurkle/color' => [
        'version' => '0.3.4',
    ],
    'fullcalendar' => [
        'version' => '6.1.20',
    ],
    '@fullcalendar/core/index.js' => [
        'version' => '6.1.20',
    ],
    '@fullcalendar/interaction/index.js' => [
        'version' => '6.1.20',
    ],
    '@fullcalendar/daygrid/index.js' => [
        'version' => '6.1.20',
    ],
    '@fullcalendar/timegrid/index.js' => [
        'version' => '6.1.20',
    ],
    '@fullcalendar/list/index.js' => [
        'version' => '6.1.20',
    ],
    '@fullcalendar/multimonth/index.js' => [
        'version' => '6.1.20',
    ],
    'preact' => [
        'version' => '10.12.1',
    ],
    'preact/compat' => [
        'version' => '10.12.1',
    ],
    '@fullcalendar/core/internal.js' => [
        'version' => '6.1.20',
    ],
    '@fullcalendar/core/preact.js' => [
        'version' => '6.1.20',
    ],
    '@fullcalendar/daygrid/internal.js' => [
        'version' => '6.1.20',
    ],
    'preact/hooks' => [
        'version' => '10.12.1',
    ],
];
