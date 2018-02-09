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
            'class'                   => '\HeimrichHannot\StylesheetManagerBundle\Compiler\Scss',
            'bin'                     => 'sass',
            'cmdDev'                  => sprintf('##lib## --default-encoding=%s --style expanded --load-path ##import_path## ##temp_file## ##output_path##', strtoupper(\Contao\Config::get('characterSet'))),
            'cmdProd'                 => sprintf('##lib## --default-encoding=%s --style compressed --sourcemap=none --load-path ##import_path## ##temp_file## ##output_path##', strtoupper(\Contao\Config::get('characterSet'))),
            'recursivelyWatchImports' => true
        ]
    ]
];

