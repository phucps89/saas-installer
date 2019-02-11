<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/29/2019
 * Time: 3:51 PM
 */

namespace Saas\Installer;

use GuzzleHttp\Client;
use Saas\Installer\Utils\Functions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use ZipArchive;
use Symfony\Component\Filesystem\Filesystem;

class InstallerCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Create a new Saas Product Application')
            ->addArgument('name', InputArgument::REQUIRED)
            ->addArgument('path', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nameProject = $input->getArgument('name');
        $dirPath = $input->getArgument('path') ?? getcwd() . DIRECTORY_SEPARATOR;
        if(!Functions::isAbsolutePath($dirPath)){
            $dirPath = getcwd().DIRECTORY_SEPARATOR.$dirPath.DIRECTORY_SEPARATOR;
        }
        $projectPath = $dirPath . $nameProject;
        $envPath = $projectPath . DIRECTORY_SEPARATOR . '.env';

        var_dump($projectPath);
    }


    /**
     * Download the temporary Zip to the given file.
     *
     * @param  string  $zipFile
     * @param  string  $version
     * @return $this
     */
    protected function download($zipFile, $version = 'master')
    {
        switch ($version) {
            case 'develop':
                $filename = 'latest-develop.zip';
                break;
            case 'master':
                $filename = 'latest.zip';
                break;
        }
        $response = (new Client)->get('http://cabinet.laravel.com/'.$filename);
        file_put_contents($zipFile, $response->getBody());
        return $this;
    }

    /**
     * Extract the Zip file into the given directory.
     *
     * @param  string  $zipFile
     * @param  string  $directory
     * @return $this
     */
    protected function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;
        $archive->open($zipFile);
        $archive->extractTo($directory);
        $archive->close();
        return $this;
    }

    /**
     * Clean-up the Zip file.
     *
     * @param  string  $zipFile
     * @return $this
     */
    protected function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);
        @unlink($zipFile);
        return $this;
    }

    /**
     * Make sure the storage and bootstrap cache directories are writable.
     *
     * @param  string  $appDirectory
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return $this
     */
    protected function prepareWritableDirectories($appDirectory, OutputInterface $output)
    {
        $filesystem = new Filesystem;
        try {
            $filesystem->chmod($appDirectory.DIRECTORY_SEPARATOR."bootstrap/cache", 0755, 0000, true);
            $filesystem->chmod($appDirectory.DIRECTORY_SEPARATOR."storage", 0755, 0000, true);
        } catch (IOExceptionInterface $e) {
            $output->writeln('<comment>You should verify that the "storage" and "bootstrap/cache" directories are writable.</comment>');
        }
        return $this;
    }
}