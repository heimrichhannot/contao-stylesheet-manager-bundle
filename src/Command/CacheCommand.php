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

        $files = [
            '/system/tmp/stylesheet-manager',
            '/assets/css/composed_dev.css',
            '/assets/css/composed_dev.css.map',
            '/assets/css/composed_prod.css',
            '/system/config/stylesheet-manager.json'
        ];

        echo PHP_EOL;

        foreach ($files as $file)
        {
            if (file_exists($this->rootDir . $file))
            {
                if (is_dir($this->rootDir . $file))
                {
                    static::deleteDirectory($this->rootDir . $file);
                }
                else
                {
                    unlink($this->rootDir . $file);
                }

                if (!file_exists($this->rootDir . $file))
                {
                    echo $file . ' deleted successfully' . PHP_EOL . PHP_EOL;
                }
                else
                {
                    echo 'Error: ' . $file . ' not deleted' . PHP_EOL . PHP_EOL;
                }
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