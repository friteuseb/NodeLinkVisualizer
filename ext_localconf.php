<?php
defined('TYPO3') or die();

use TalanHdf\NodeLinkVisualizer\Controller\NodeLinkVisualizerController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
    'NodeLinkVisualizer',
    'Pi1',
    [
        NodeLinkVisualizerController::class => 'show'
    ],
    // non-cacheable actions
    [
        NodeLinkVisualizerController::class => 'show'
    ]
);