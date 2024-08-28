<?php
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addStaticFile(
    'node_link_visualizer',
    'Configuration/TypoScript',
    'Node Link Visualizer'
);