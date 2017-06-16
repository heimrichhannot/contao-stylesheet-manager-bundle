<?php

namespace HeimrichHannot\StylesheetManagerBundle\Compiler;

abstract class Compiler
{
    protected $strTempDir;

    public abstract function prepareTempDir();
    public abstract function compose($arrCoreFiles, $arrModuleFiles, $arrProjectFiles);
    public abstract function compile($strComposedFile);

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
