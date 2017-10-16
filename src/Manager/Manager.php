<?php

namespace HeimrichHannot\StylesheetManagerBundle\Manager;

use HeimrichHannot\StylesheetManagerBundle\Compiler\Compiler;

class Manager
{
    public static function run($strBuffer, $strTemplate)
    {
        preg_match('@(<!-- stylesheetManagerCss\.(?<group>.+) -->)@', $strBuffer, $arrMatches);

        if (!is_array($arrMatches) || empty($arrMatches))
        {
            return $strBuffer;
        }

        $strReplace = $arrMatches[0];

        if (isset($arrMatches['group']))
        {
            $strGroup = $arrMatches['group'];
        }
        else
        {
            return $strBuffer;
        }

        $blnUpdate       = false;
        $strMode         = \System::getContainer()->get('kernel')->getEnvironment();
        $strTempDir      = TL_ROOT . '/system/tmp/stylesheet-manager/' . $strGroup . '/' . $strMode;
        $strCssFilesJson = TL_ROOT . '/system/config/stylesheet-manager/' . $strGroup . '/stylesheet-manager.json';
        $strCssFiles     = @file_get_contents($strCssFilesJson);

        if (!$strCssFiles)
        {
            $blnUpdate   = true;
            $strCssFiles = json_encode(
                [
                    'dev'  => '',
                    'prod' => '',
                ]
            );

            $strBase = TL_ROOT . '/system/config/stylesheet-manager/' . $strGroup;

            if (!file_exists($strBase))
            {
                mkdir($strBase, 0777, true);
            }

            file_put_contents($strCssFilesJson, $strCssFiles);
        }

        $arrCssFiles = json_decode($strCssFiles, true);

        $strCssFile            = $arrCssFiles[$strMode];
        $strCssFileNoTimestamp = $strCssFile ? explode('?', $strCssFile)[0] : null;

        // preparation
        list($arrCoreFiles, $arrModuleFiles, $arrProjectFiles) = static::collectFiles($strGroup);

        // check if a regeneration is needed
        if ($blnUpdate || !$strCssFileNoTimestamp || !file_exists(TL_ROOT . '/' . $strCssFileNoTimestamp))
        {
            $blnUpdate = true;
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

        // temp dir
        if (!file_exists($strTempDir))
        {
            $blnUpdate = true;
            mkdir($strTempDir, 0777, true);
        }

        if ($blnUpdate)
        {
            // preprocessor specifics
            $strActivePreprocessor = $GLOBALS['STYLESHEET_MANAGER']['activePreprocessor'];

            /** @var Compiler $objCompiler */
            $objCompiler = new $GLOBALS['STYLESHEET_MANAGER']['preprocessors'][$strActivePreprocessor]['class'];

            if ($objCompiler::getExecutablePath() === null)
            {
                if (!$strCssFileNoTimestamp || !file_exists($strCssFileNoTimestamp))
                {
                    $strCssFile = 'assets/css/composed_' . $strGroup . '_' . $strMode . '.css';

                    if (file_exists(TL_ROOT . '/' . $strCssFile))
                    {
                        $strCssFileNoTimestamp = $strCssFileNoTimestamp ?: $strCssFile;
                        $strCssFile .= '?v=' . time();

                        $arrCssFiles[$strMode] = $strCssFile;
                        file_put_contents($strCssFilesJson, json_encode($arrCssFiles));
                    }
                }

                if (!file_exists($strCssFileNoTimestamp))
                {
                    throw new \Exception(
                        'Neither a previously generated CSS nor a the necessary SCSS lib found: '
                        . $GLOBALS['STYLESHEET_MANAGER']['preprocessors'][$GLOBALS['STYLESHEET_MANAGER']['activePreprocessor']]['bin']
                    );
                }

                // else use the existing css file without an exception
            }
            else
            {
                static::writeFileInfoToFile($arrCoreFiles, $arrModuleFiles, $arrProjectFiles, $strTempDir);

                // clean up
                if ($strCssFileNoTimestamp && file_exists(TL_ROOT . '/' . $strCssFileNoTimestamp))
                {
                    unlink(TL_ROOT . '/' . $strCssFileNoTimestamp);
                }

                if ($strCssFileNoTimestamp && file_exists(TL_ROOT . '/' . $strCssFileNoTimestamp . '.map'))
                {
                    unlink(TL_ROOT . '/' . $strCssFileNoTimestamp . '.map');
                }

                // generate
                $objCompiler->setTempDir($strTempDir);

                $objCompiler->prepareTempDir();
                $strComposedFile = $objCompiler->compose($arrCoreFiles, $arrModuleFiles, $arrProjectFiles, $strGroup);
                $objCompiler->compile($strComposedFile);

                // integrate in fe_page
                $strCssFile = 'assets/css/composed_' . $strGroup . '_' . $strMode . '.css?v=' . time();

                $arrCssFiles[$strMode] = $strCssFile;
                file_put_contents($strCssFilesJson, json_encode($arrCssFiles));
            }
        }

        return str_replace($strReplace, '<link rel="stylesheet" href="' . $strCssFile . '">', $strBuffer);
    }

    private static function checkFilesForUpdate($arrCoreFiles, $arrModuleFiles, $arrProjectFiles, $arrFileInfo)
    {
        foreach (array_merge($arrCoreFiles, $arrModuleFiles, $arrProjectFiles) as $strFile)
        {
            if (!isset($arrFileInfo[$strFile]))
            {
                return true;
            }

            // support web folder
            $strPath = file_exists(TL_ROOT . '/' . $strFile) ? TL_ROOT . '/' . $strFile : TL_ROOT . '/web/' . $strFile;

            $intFileSize   = filesize($strPath);
            $intLastUpdate = filemtime($strPath);

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
            // support web folder
            $strPath = file_exists(TL_ROOT . '/' . $strFile) ? TL_ROOT . '/' . $strFile : TL_ROOT . '/web/' . $strFile;

            $arrFileInfo[$strFile] = [
                'filesize'    => filesize($strPath),
                'last_update' => filemtime($strPath)
            ];
        }

        file_put_contents($strTempDir . '/file-info.json', json_encode($arrFileInfo));
    }

    private static function stripStylesheetTags($strFile)
    {
        $arrFile = explode('|', $strFile);

        return $arrFile[0];
    }

    private static function collectFiles($strGroup)
    {
        $arrConfig = $GLOBALS['TL_STYLESHEET_MANAGER_CSS'][$strGroup];

        // core libs (loaded before everything else)
        $arrCoreFiles = [];

        if (isset($arrConfig['core']))
        {
            if (is_array($arrConfig['core']))
            {
                $arrCoreFiles = array_map(
                    function ($strFile)
                    {
                        return ltrim($strFile, '/');
                    },
                    $arrConfig['core']
                );

            }
            elseif (is_string($arrConfig['core']))
            {
                $arrCoreFiles[] = ltrim($arrConfig['core'], '/');
            }
        }

        // modules (contao's ordering used)
        $arrModuleFiles = [];

        if (!isset($arrConfig['skipModuleCss']) || !$arrConfig['skipModuleCss'])
        {
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
        }

        // project (can override everything)
        $arrProjectFiles = [];

        if (isset($arrConfig['project']))
        {
            if (is_array($arrConfig['project']))
            {
                $arrProjectFiles = array_map(
                    function ($strFile)
                    {
                        return ltrim($strFile, '/');
                    },
                    $arrConfig['project']
                );
            }
            elseif (is_string($arrConfig['project']))
            {
                $arrProjectFiles[] = ltrim($arrConfig['project'], '/');
            }
        }

        return [$arrCoreFiles, $arrModuleFiles, $arrProjectFiles];
    }
}
