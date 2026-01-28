<?php

return [
    /**
     * Default background color for the viewer (CSS color value).
     */
    'default_background' => '#ffffff',

    /**
     * HTTP timeout for external API requests (seconds).
     */
    'timeout' => 10,

    /**
     * Default options passed to $3Dmol.createViewer(...).
     * Merge behavior: component props override these defaults.
     *
     * Example: ['backgroundAlpha' => 0.0, 'disableFog' => true]
     * Docs: https://3dmol.csb.pitt.edu/doc/GLViewer.html
     */
    'viewer_options' => [],

    /**
     * Default options passed to viewer.addModel(...).
     * Merge behavior: component props override these defaults.
     *
     * Example: ['keepH' => true, 'doAssembly' => false]
     * Docs: https://3dmol.csb.pitt.edu/doc/GLViewer.html#addModel
     */
    'model_options' => [],

    /**
     * Default style overrides merged into the base style map.
     *
     * Example: ['stick' => ['radius' => 0.2]]
     * Docs: https://3dmol.csb.pitt.edu/doc/GLViewer.html#setStyle
     */
    'style_options' => [],

    /**
     * Cache settings for resolved molecule data from external APIs.
     */
    'cache' => [
        /**
         * Enable or disable caching for external lookups.
         */
        'enabled' => true,

        /**
         * Cache TTL in seconds.
         */
        'ttl' => 60 * 60 * 24,

        /**
         * Cache key prefix.
         */
        'prefix' => 'molecule_',
    ],
];
