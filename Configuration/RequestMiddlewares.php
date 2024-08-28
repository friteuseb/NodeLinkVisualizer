<?php
return [
    'frontend' => [
        'talan-hdf/node-link-visualizer/content-analyzer' => [
            'target' => TalanHdf\NodeLinkVisualizer\Middleware\ContentAnalyzerMiddleware::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'before' => [
                'typo3/cms-frontend/output-compression',
            ],
        ],
    ],
];