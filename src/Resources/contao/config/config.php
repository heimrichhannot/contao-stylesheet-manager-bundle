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
            'cmdDev'  => '/usr/bin/compass compile --app-dir "##temp_dir##" --config "##config_file##" -I "##import_path##"',
            // TODO add -e production in Contao 4
            'cmdProd' => '/usr/bin/compass compile --app-dir "##temp_dir##" --config "##config_file##" -I "##import_path##"',
        ]
    ]
];