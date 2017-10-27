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

                    // support web folder
                    $strPath = file_exists(TL_ROOT . '/' . ltrim($strFile, '/')) ?
                        TL_ROOT . '/' . ltrim($strFile, '/') : TL_ROOT . '/web/' . ltrim($strFile, '/');

                    copy($strPath, $strTempScssFile);

                    $strData .= '@import "' . basename($strFile, '.css') . '";' . PHP_EOL;
                }
                elseif ($strExtension == 'scss')
                {
                    $strData .= '@import "' . str_replace('.scss', '', $strFile) . '";' . PHP_EOL;
                }
            }
        }

        $this->strTempFile = $this->strTempDir . '/scss/composed_' . $strGroup . '_' . $this->strMode . '.scss';
        $this->strOutputFile = TL_ROOT . '/assets/css/composed_' . $strGroup . '_' . $this->strMode . '.css';

        file_put_contents($this->strTempFile, $strData);

        return $this->strTempFile;
    }

    public function compile($strComposedFile)
    {
        $strCommand = str_replace(
            '##lib##',
            Compiler::getExecutablePath(),
            $GLOBALS['STYLESHEET_MANAGER']['preprocessors']['scss']['cmd' . ucfirst($this->strMode)]
        );

        $strCommand = str_replace(
            '##temp_file##',
            escapeshellarg($this->strTempFile),
            $strCommand
        );

        $strCommand = str_replace(
            '##output_path##',
            $this->strOutputFile,
            $strCommand
        );

        $strCommand = str_replace('##import_path##', escapeshellarg(TL_ROOT), $strCommand);

        exec($strCommand, $varOutput);
    }
}
