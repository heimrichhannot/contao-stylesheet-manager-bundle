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

    public function compose($arrCoreFiles, $arrModuleFiles, $arrProjectFiles)
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

                    // since SASS doesn't support importing CSS files we must copy the file and change the extension to scss :-(
                    copy(TL_ROOT . '/' . ltrim($strFile, '/'), $strTempScssFile);

                    $strData .= '@import "' . basename($strFile, '.css') . '";' . PHP_EOL;
                }
                else
                {
                    $strData .= '@import "' . $strFile . '";' . PHP_EOL;
                }
            }
        }

        file_put_contents($this->strTempDir . '/scss/composed.scss', $strData);

        return $this->strTempDir . '/scss/composed.scss';
    }

    public function compile($strComposedFile)
    {
        $strCommand = str_replace('##temp_dir##', $this->strTempDir, $GLOBALS['STYLESHEET_MANAGER']['preprocessors']['scss']['cmd']);
        $strCommand = str_replace('##config_file##', TL_ROOT . '/vendor/heimrichhannot/contao-stylesheet-manager-bundle/src/Resources/contao/assets/ruby/config.rb', $strCommand);
        $strCommand = str_replace('##import_path##', TL_ROOT, $strCommand);

        exec($strCommand, $varOutput);

        // check for errors happened
//        foreach ($varOutput as $strMessage)
//        {
//            if (strpos($strMessage, 'Compilation failed') !== false)
//            {
//                throw new \Exception(implode(' | ', $varOutput));
//            }
//        }
    }
}
