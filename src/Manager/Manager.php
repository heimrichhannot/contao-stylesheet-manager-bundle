<?php

namespace HeimrichHannot\StylesheetManagerBundle\Manager;

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
        $strCssFile = \Config::get('stylesheetManagerCssFilename');

        // preparation
        list($arrCoreFiles, $arrModuleFiles, $arrProjectFiles) = static::collectFiles();

        // check if a regeneration is needed
        if (!$strCssFile || !file_exists(TL_ROOT . '/' . $strCssFile))
        {
            $blnUpdate = true;
        }
        elseif (!file_exists($strTempDir))
        {
            $blnUpdate = true;
            mkdir($strTempDir);
        }
        else
        {
            if (!file_exists($strTempDir . '/file-info.json'))
            {
                $blnUpdate = true;
            }
            else
            {
                $arrFileInfo = json_decode(file_get_contents($strTempDir . '/file-info.json'), true);

                // check for modified files
                $blnUpdate = static::checkFilesForUpdate($arrCoreFiles, $arrModuleFiles, $arrProjectFiles, $arrFileInfo);
            }
        }

        if ($blnUpdate)
        {
            static::writeFileInfoToFile($arrCoreFiles, $arrModuleFiles, $arrProjectFiles, $strTempDir);

            // preprocessor specifics
            $strActivePreprocessor = $GLOBALS['STYLESHEET_MANAGER']['activePreprocessor'];

            /** @var Compiler $objCompiler */
            $objCompiler = new $GLOBALS['STYLESHEET_MANAGER']['preprocessors'][$strActivePreprocessor]['class'];
            $objCompiler->setTempDir($strTempDir);

            $objCompiler->prepareTempDir();
            $strComposedFile = $objCompiler->compose($arrCoreFiles, $arrModuleFiles, $arrProjectFiles);
            $objCompiler->compile($strComposedFile);

            // clean up
            if (file_exists(TL_ROOT . '/' . $strCssFile))
            {
                unlink(TL_ROOT . '/' . $strCssFile);
            }

            // integrate in fe_page
            $strCssFile = 'assets/css/composed.css?v=' . time();

            \Config::persist('stylesheetManagerCssFilename', $strCssFile);
        }

        return str_replace('<!-- stylesheetManagerCss -->', '<link rel="stylesheet" href="' . $strCssFile . '">', $strBuffer);
    }

    private static function checkFilesForUpdate($arrCoreFiles, $arrModuleFiles, $arrProjectFiles, $arrFileInfo)
    {
        foreach (array_merge($arrCoreFiles, $arrModuleFiles, $arrProjectFiles) as $strFile)
        {
            if (!isset($arrFileInfo[$strFile]))
            {
                return true;
            }

            $intFileSize = filesize(TL_ROOT . '/' . $strFile);
            $intLastUpdate = filemtime(TL_ROOT . '/' . $strFile);

            if ($arrFileInfo[$strFile]['filesize'] != $intFileSize || $arrFileInfo[$strFile]['last_update'] != $intLastUpdate)
            {
                return true;
            }
        }

        return false;
    }

    private static function writeFileInfoToFile($arrCoreFiles, $arrModuleFiles, $arrProjectFiles, $strTempDir)
    {
        $arrFileInfo = [];

        foreach (array_merge($arrCoreFiles, $arrModuleFiles, $arrProjectFiles) as $strFile)
        {
            $arrFileInfo[$strFile] = [
                'filesize' => filesize(TL_ROOT . '/' . $strFile),
                'last_update' => filemtime(TL_ROOT . '/' . $strFile)
            ];
        }

        file_put_contents($strTempDir . '/file-info.json', json_encode($arrFileInfo));
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
                $arrCoreFiles = array_map(function($strFile) {
                    return ltrim($strFile, '/');
                }, $GLOBALS['TL_STYLESHEET_MANAGER_CSS']['core']);

            }
            elseif (is_string($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['core']))
            {
                $arrCoreFiles[] = ltrim($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['core'], '/');
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
                $arrModuleFiles[] = ltrim(static::stripStylesheetTags($strPath), '/');
            }
        }

        // project (can override everything)
        $arrProjectFiles = [];

        if (isset($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['project']))
        {
            if (is_array($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['project']))
            {
                $arrProjectFiles = array_map(function($strFile) {
                    return ltrim($strFile, '/');
                }, $GLOBALS['TL_STYLESHEET_MANAGER_CSS']['project']);
            }
            elseif (is_string($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['project']))
            {
                $arrProjectFiles[] = ltrim($GLOBALS['TL_STYLESHEET_MANAGER_CSS']['project'], '/');
            }
        }

        return [$arrCoreFiles, $arrModuleFiles, $arrProjectFiles];
    }
}