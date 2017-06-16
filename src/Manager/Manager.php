<?php

namespace HeimrichHannot\StylesheetManagerBundle\Manager;

use Contao\File;
use HeimrichHannot\StylesheetManagerBundle\Compiler\Compiler;

class Manager
{
    public static function run($strBuffer, $strTemplate)
    {
        if ($strTemplate !== 'fe_page')
        {
            return $strBuffer;
        }

        $strTempDir = TL_ROOT . '/system/tmp/stylesheet-manager';

        // preparation
        list($arrCoreFiles, $arrModuleFiles, $arrProjectFiles) = static::collectFiles();
        static::prepareTempDir($strTempDir);

        // preprocessor specifics
        $strActivePreprocessor = $GLOBALS['STYLESHEET_MANAGER']['activePreprocessor'];
        /** @var Compiler $objCompiler */
        $objCompiler = new $GLOBALS['STYLESHEET_MANAGER']['preprocessors'][$strActivePreprocessor]['class'];
        $objCompiler->setTempDir($strTempDir);

        $objCompiler->prepareTempDir();
        $strComposedFile = $objCompiler->compose($arrCoreFiles, $arrModuleFiles, $arrProjectFiles);
        $objCompiler->compile($strComposedFile);

        // clean up
        array_map('unlink', glob(TL_ROOT . '/assets/css/stylesheet-manager_*.*'));

        // integrate in fe_page
        $strCssFile = 'assets/css/stylesheet-manager_' . uniqid() . '.css';

        copy($strTempDir . '/css/composed.css', TL_ROOT . '/' . $strCssFile);

        return str_replace('<!-- stylesheetManagerCss -->', '<link rel="stylesheet" href="' . $strCssFile . '">', $strBuffer);
    }

    private static function stripStylesheetTags($strFile)
    {
        $arrFile = explode('|', $strFile);

        return $arrFile[0];
    }

    private static function collectFiles()
    {
        // core libs (loaded before everything else)
        $arrCoreFiles = [];

        if (isset($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['core']))
        {
            if (is_array($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['core']))
            {
                $arrCoreFiles = $GLOBALS['TL_STYLESHEET_MANAGER_CSS']['core'];
            }
            elseif (is_string($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['core']))
            {
                $arrCoreFiles[] = $GLOBALS['TL_STYLESHEET_MANAGER_CSS']['core'];
            }
        }

        // modules (contao's ordering used)
        $arrModuleFiles = [];

        $arrTypes = [
            'TL_FRAMEWORK_CSS',
            'TL_CSS',
            'TL_USER_CSS'
        ];

        foreach ($arrTypes as $strType)
        {
            if (!isset($GLOBALS[$strType]) || !is_array($GLOBALS[$strType]))
            {
                continue;
            }

            foreach ($GLOBALS[$strType] as $strName => $strPath)
            {
                $arrModuleFiles[] = static::stripStylesheetTags($strPath);
            }
        }

        // project (can override everything)
        $arrProjectFiles = [];

        if (isset($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['project']))
        {
            if (is_array($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['project']))
            {
                $arrProjectFiles = $GLOBALS['TL_STYLESHEET_MANAGER_CSS']['project'];
            }
            elseif (is_string($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['project']))
            {
                $arrProjectFiles[] = $GLOBALS['TL_STYLESHEET_MANAGER_CSS']['project'];
            }
        }

        return [$arrCoreFiles, $arrModuleFiles, $arrProjectFiles];
    }

    private static function prepareTempDir($strTempDir)
    {
        // compose everyhting to a single file
        if (!file_exists($strTempDir))
        {
            mkdir($strTempDir);
        }
    }
}