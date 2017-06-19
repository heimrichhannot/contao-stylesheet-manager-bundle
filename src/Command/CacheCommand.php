<?php

namespace HeimrichHannot\StylesheetManagerBundle\Command;

use Contao\CoreBundle\Analyzer\HtaccessAnalyzer;
use Contao\CoreBundle\Command\AbstractLockedCommand;
use Contao\CoreBundle\Util\SymlinkUtil;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CacheCommand extends AbstractLockedCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('stylesheetmanager:cache:clear')->setDescription('Symlinks the public resources into the web directory.');
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output)
    {
        $this->io      = new SymfonyStyle($input, $output);
        $this->rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));

        $tmpDir = $this->rootDir . '/system/tmp/stylesheet-manager';
        $css    = $this->rootDir . '/assets/css/composed.css';
        $cssMap = $this->rootDir . '/assets/css/composed.css.map';

        echo PHP_EOL;

        if (file_exists($tmpDir))
        {
            static::deleteDirectory($tmpDir);

            if (!file_exists($tmpDir))
            {
                echo str_replace($this->rootDir, '', $tmpDir) . ' deleted successfully' . PHP_EOL . PHP_EOL;
            }
            else
            {
                echo 'Error: ' . str_replace($this->rootDir, '', $tmpDir) . ' not deleted' . PHP_EOL . PHP_EOL;
            }
        }

        if (file_exists($css))
        {
            unlink($css);

            if (!file_exists($css))
            {
                echo str_replace($this->rootDir, '', $css) . ' deleted successfully' . PHP_EOL . PHP_EOL;
            }
            else
            {
                echo 'Error: ' . str_replace($this->rootDir, '', $css) . ' not deleted' . PHP_EOL . PHP_EOL;
            }
        }

        if (file_exists($cssMap))
        {
            unlink($cssMap);

            if (!file_exists($cssMap))
            {
                echo str_replace($this->rootDir, '', $cssMap) . ' deleted successfully' . PHP_EOL . PHP_EOL;
            }
            else
            {
                echo 'Error: ' . str_replace($this->rootDir, '', $cssMap) . ' not deleted' . PHP_EOL . PHP_EOL;
            }
        }

        return 0;
    }

    protected function deleteDirectory($dir)
    {
        if (!file_exists($dir))
        {
            return true;
        }

        if (!is_dir($dir))
        {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item)
        {
            if ($item == '.' || $item == '..')
            {
                continue;
            }

            if (!static::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item))
            {
                return false;
            }
        }

        return rmdir($dir);
    }
}