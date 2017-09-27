<?php

namespace HeimrichHannot\StylesheetManagerBundle\Compiler;

use Symfony\Component\Process\ExecutableFinder;

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

    /**
     * Get the compiler executable binary path
     * @return string|null The executable path or null if not found
     */
    public static function getExecutablePath()
    {
        $finder = new ExecutableFinder();
        // mac executable = /usr/local/bin
        return $finder->find($GLOBALS['STYLESHEET_MANAGER']['preprocessors'][$GLOBALS['STYLESHEET_MANAGER']['activePreprocessor']]['bin'], null, ['/usr/local/bin']);
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
