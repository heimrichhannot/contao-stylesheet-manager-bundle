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
            'config'  => 'vendor/heimrichhannot/contao-stylesheet-manager-bundle/src/Resources/contao/assets/ruby/config.rb',
            'bin'     => '/usr/bin/compass',
            'cmdDev'  => '##lib## compile --app-dir "##temp_dir##" --config "##config_file##" -I "##import_path##"',
            'cmdProd' => '##lib## compile -e production --app-dir "##temp_dir##" --config "##config_file##" -I "##import_path##"',
        ]
    ]
];
