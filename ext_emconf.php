<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Node Link Visualizer',
    'description' => 'Displays internal page links as nodes and arrows using d3.js',
    'category' => 'plugin',
    'author' => 'Cyril Wolfangel',
    'author_email' => 'cyril.wolfangel@gmail.com',
    'state' => 'beta',
    'clearCacheOnLoad' => 1,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];