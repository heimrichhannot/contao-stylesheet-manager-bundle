<?php

namespace HeimrichHannot\StylesheetManagerBundle\Compiler;

abstract class Compiler
{
    protected $strTempDir;
    protected $strMode;

    public abstract function prepareTempDir();

    public abstract function compose($arrCoreFiles, $arrModuleFiles, $arrProjectFiles, $strGroup);

    public abstract function compile($strComposedFile);

    public function __construct()
    {
        $this->strMode = \System::getContainer()->get('kernel')->getEnvironment();
    }

    public function checkIfLibExists()
    {
        $blnExists = shell_exec("which " . escapeshellarg(
                $GLOBALS['STYLESHEET_MANAGER']['preprocessors'][$GLOBALS['STYLESHEET_MANAGER']['activePreprocessor']]['bin']
        )) !== null;

        return $blnExists;
    }

    /**
     * @return mixed
     */
    public function getTempDir()
    {
        return $this->strTempDir;
    }

    /**
     * @param mixed $strTempDir
     */
    public function setTempDir($strTempDir)
    {
        $this->strTempDir = $strTempDir;
    }
}
