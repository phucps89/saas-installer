<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2/13/2019
 * Time: 10:38 AM
 */

namespace Saas\Installer;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InputLicenceCommand extends Command
{

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('input-licence')
            ->setDescription('Input licence')
            ->addArgument("project_path", InputArgument::OPTIONAL, 'Path to project', getcwd());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        if(!extension_loaded('Zephir Parser')){
            $io->caution('Extension Zephir Parser can not be loaded');
            return;
        }

        if(!extension_loaded('saas')){
            $io->caution('Extension Saas can not be loaded');
            return;
        }

        $path = $input->getArgument("project_path");
        $licencePath = $path . DIRECTORY_SEPARATOR . '.sdkey';
        while(true){
            $licenceContent = $io->ask("Please input licence");
            file_put_contents($licencePath, $licenceContent);
            $io->note("Checking licence...");
            try{
                $licenceId = \Saas\Cores\Index::getLicenceId($licencePath);
            }
            catch (\Exception $e){

            }
            if(empty($licenceId)){
                $io->error('Invalid Licence!!!');
                unlink($licencePath);
            }
            else{
                break;
            }
        }
        $io->success("Done");
    }
}