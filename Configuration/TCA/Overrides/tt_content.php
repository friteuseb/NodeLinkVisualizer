<?php
defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'NodeLinkVisualizer',
    'Pi1',
    'Node Link Visualizer'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['nodelinkvisualizer_pi1'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'nodelinkvisualizer_pi1',
    'FILE:EXT:node_link_visualizer/Configuration/FlexForms/flexform_nodelinkvisualizer.xml'
);