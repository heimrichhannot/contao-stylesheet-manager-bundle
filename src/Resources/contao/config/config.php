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
    'preprocessors' => [
        'scss' => [
            'class' => '\HeimrichHannot\StylesheetManagerBundle\Compiler\Scss',
            'cmd' => '/usr/bin/compass compile --app-dir "##temp_dir##" --config "##config_file##" -I "##import_path##"'
        ]
    ]
];