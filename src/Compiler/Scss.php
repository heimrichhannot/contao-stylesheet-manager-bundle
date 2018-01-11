<?php

namespace HeimrichHannot\StylesheetManagerBundle\Compiler;

use HeimrichHannot\Haste\Util\Container;
use HeimrichHannot\StylesheetManagerBundle\Manager\Manager;
use Symfony\Component\Filesystem\Filesystem;

class Scss extends Compiler
{
    protected static $allowedFileExtensions = ['sass', 'scss'];
    protected static $allowedPrefixes = ['_'];

    public function prepareTempDir()
    {
        if (!file_exists($this->strTempDir . '/css')) {
            mkdir($this->strTempDir . '/css');
        }

        if (!file_exists($this->strTempDir . '/scss')) {
            mkdir($this->strTempDir . '/scss');
        }
    }

    public function compose($arrCoreFiles, $arrModuleFiles, $arrProjectFiles, $strGroup)
    {
        $strData = '';

        foreach (['core' => $arrCoreFiles, 'modules' => $arrModuleFiles, 'project' => $arrProjectFiles] as $strType => $arrFiles) {
            $strData .= '// ' . $strType . PHP_EOL;

            foreach ($arrFiles as $strFile) {
                $strExtension = pathinfo($strFile, PATHINFO_EXTENSION);

                if ($strExtension == 'css') {
                    $strTempScssFile = $this->strTempDir . '/scss/_' . basename($strFile, '.css') . '.scss';

                    // since CSS imports must be at the top of a css file (which would break the order)
                    // we must copy the file and change the extension to scss :-(
                    copy(Manager::getPossiblyPublicAssetFilePath($strFile), $strTempScssFile);

                    $strData .= '@import "' . basename($strFile, '.css') . '";' . PHP_EOL;
                } elseif ($strExtension == 'scss') {
                    $strData .= '@import "' . str_replace('.scss', '', $strFile) . '";' . PHP_EOL;
                }
            }
        }

        $this->strTempFile   = $this->strTempDir . '/scss/composed_' . $strGroup . '_' . $this->strMode . '.scss';
        $this->strOutputFile = Container::getProjectDir() . '/assets/css/composed_' . $strGroup . '_' . $this->strMode . '.css';

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
            escapeshellarg($this->strOutputFile),
            $strCommand
        );

        // ensure, that assets/css directory exists
        $fs = new Filesystem();
        $fs->mkdir(\Contao\System::getContainer()->getParameter('kernel.project_dir') . '/assets/css');

        $strCommand = str_replace('##import_path##', escapeshellarg(Container::getProjectDir()), $strCommand);

        exec($strCommand, $varOutput);
    }

    public function recursivelyCollectImportedFiles($arrFiles, $blnSkipRootFile = false)
    {
        $arrResult = [];

        foreach ($arrFiles as $strFile) {
            if (!$blnSkipRootFile) {
                $arrResult[] = $strFile;
            }

            if (!file_exists(Manager::getPossiblyPublicAssetFilePath($strFile))) {
                throw new \Exception('Stylesheet manager: Source file "' . Manager::getPossiblyPublicAssetFilePath($strFile) . '" does not exist.');
            }

            $strExtension = pathinfo($strFile, PATHINFO_EXTENSION);

            if (!in_array($strExtension, static::$allowedFileExtensions)) {
                continue;
            }

            $strContent = file_get_contents(Manager::getPossiblyPublicAssetFilePath($strFile));

            if ($strContent) {
                preg_match_all('/@import\s*["\'](?<group>[^"\']+)["\'];/i', $strContent, $arrMatches);

                if (is_array($arrMatches['group'])) {
                    foreach ($arrMatches['group'] as $strImportFile) {
                        $strImportFileDir  = rtrim(pathinfo($strFile, PATHINFO_DIRNAME) . '/' . ltrim(pathinfo($strImportFile, PATHINFO_DIRNAME), '/'), '.');
                        $strImportFileName = pathinfo($strImportFile, PATHINFO_FILENAME);

                        $found                 = false;
                        $strImportFileFullPath = '';

                        // check the different variation possibilities
                        foreach (array_merge([''], static::$allowedPrefixes) as $prefix) {
                            foreach (static::$allowedFileExtensions as $extension) {
                                $strPath = rtrim(Manager::getPossiblyPublicAssetFilePath($strImportFileDir), '/') . '/' . $prefix . $strImportFileName . '.' . $extension;

                                if (file_exists($strPath)) {
                                    $found = true;

                                    $prefix = Container::getProjectDir() . DIRECTORY_SEPARATOR;

                                    if (substr($strPath, 0, strlen($prefix)) == $prefix) {
                                        $strImportFileFullPath = substr($strPath, strlen($prefix));
                                    }
                                    break 2;
                                }
                            }
                        }

                        if (!$found) {
                            continue;
                        }

                        $arrResult = array_merge($arrResult, static::recursivelyCollectImportedFiles([
                            $strImportFileFullPath
                        ]));
                    }
                }
            }
        }

        return $arrResult;
    }
}

