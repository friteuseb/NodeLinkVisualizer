<?php
defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'NodeLinkVisualizer',
    'Pi1',
    [
        \TalanHdf\NodeLinkVisualizer\Controller\NodeLinkVisualizerController::class => 'show'
    ],
    // non-cacheable actions
    [
        \TalanHdf\NodeLinkVisualizer\Controller\NodeLinkVisualizerController::class => 'show'
    ]
);