<?php

namespace HeimrichHannot\StylesheetManagerBundle\Compiler;

class Scss extends Compiler
{
    public function prepareTempDir()
    {
        if (!file_exists($this->strTempDir . '/css'))
        {
            mkdir($this->strTempDir . '/css');
        }

        if (!file_exists($this->strTempDir . '/scss'))
        {
            mkdir($this->strTempDir . '/scss');
        }
    }

    public function compose($arrCoreFiles, $arrModuleFiles, $arrProjectFiles, $strGroup)
    {
        $strData = '';

        foreach (['core' => $arrCoreFiles, 'modules' => $arrModuleFiles, 'project' => $arrProjectFiles] as $strType => $arrFiles)
        {
            $strData .= '// ' . $strType . PHP_EOL;

            foreach ($arrFiles as $strFile)
            {
                $strExtension = pathinfo($strFile, PATHINFO_EXTENSION);

                if ($strExtension == 'css')
                {
                    $strTempScssFile = $this->strTempDir . '/scss/_' . basename($strFile, '.css') . '.scss';

                    // since CSS imports must be at the top of a css file (which would break the order)
                    // we must copy the file and change the extension to scss :-(
                    copy(TL_ROOT . '/' . ltrim($strFile, '/'), $strTempScssFile);

                    $strData .= '@import "' . basename($strFile, '.css') . '";' . PHP_EOL;
                }
                elseif ($strExtension == 'scss')
                {
                    $strData .= '@import "' . str_replace('.scss', '', $strFile) . '";' . PHP_EOL;
                }
            }
        }

        file_put_contents($this->strTempDir . '/scss/composed_' . $strGroup . '_' . $this->strMode . '.scss', $strData);

        return $this->strTempDir . '/scss/composed_' . $strGroup . '_' . $this->strMode . '.scss';
    }

    public function compile($strComposedFile)
    {
        $strCommand = str_replace(
            '##lib##',
            $GLOBALS['STYLESHEET_MANAGER']['preprocessors']['scss']['bin'],
            $GLOBALS['STYLESHEET_MANAGER']['preprocessors']['scss']['cmd' . ucfirst($this->strMode)]
        );

        $strCommand = str_replace(
            '##temp_dir##',
            $this->strTempDir,
            $strCommand
        );

        $strCommand = str_replace(
            '##config_file##',
            TL_ROOT . '/' . ltrim($GLOBALS['STYLESHEET_MANAGER']['preprocessors']['scss']['config'], '/'),
            $strCommand
        );

        $strCommand = str_replace('##import_path##', TL_ROOT, $strCommand);

        exec($strCommand, $varOutput);
    }
}
