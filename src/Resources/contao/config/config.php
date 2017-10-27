<?php

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['modifyFrontendPage']['run'] = ['HeimrichHannot\StylesheetManagerBundle\Manager\Manager', 'run'];

/**
 * Configuration
 */
$GLOBALS['STYLESHEET_MANAGER'] = [
    'activePreprocessor' => 'scss',
    'preprocessors'      => [
        'scss' => [
            'class'   => '\HeimrichHannot\StylesheetManagerBundle\Compiler\Scss',
            'bin'     => 'sass',
            'cmdDev'  => '##lib## --style expanded --load-path ##import_path## ##temp_file## ##output_path##',
            'cmdProd' => '##lib## --style compressed --load-path ##import_path## ##temp_file## ##output_path##',
        ]
    ]
];
