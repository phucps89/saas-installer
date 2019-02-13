<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2/13/2019
 * Time: 6:15 AM
 */

namespace Saas\Installer;


use Cz\Git\GitRepository;
use Saas\Installer\Utils\Common;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetupEnvCommand extends Command
{
    const SAAS_EXTENSION = 'Saas Extension';
    const ZEPHIR_PARSER = 'Zephir Parser';
    const GIT_REPO_ZEPHIR_PARSER = 'https://github.com/phalcon/php-zephir-parser.git';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('setup-env')
            ->setDescription('Create a new Laravel application');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $arrEnv = [
            1 => self::ZEPHIR_PARSER,
            2 => self::SAAS_EXTENSION,
        ];
        $question = new ChoiceQuestion('Se;ect environment?', $arrEnv);
        $ans = $helper->ask($input, $output, $question);

        switch ($ans){
            case self::ZEPHIR_PARSER:
                $this->installZephirParser($input, $output);
                break;
        }
    }

    private function installZephirParser(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        if(!Common::isRunningAsRoot()){
            $io->caution("This action must be run as Root permission!!");
            return;
        }

        $phpVersion = Common::getPhpVersion();
        system('apt-get update');
        system("apt-get install php{$phpVersion}-dev gcc make re2c autoconf automake");

        $dirZephir = '/php-zephir-parser';
        if(file_exists($dirZephir)){
            Common::deleteDirectory($dirZephir);
        }

        $io->note("Cloning Zephir Parser...");
        $repo = GitRepository::cloneRepository('https://github.com/phalcon/php-zephir-parser.git', $dirZephir);

        $io->note("Installing Zephir Parser...");
        $logCurDir = getcwd();
        chdir($dirZephir);
        system('phpize');
        system('./configure');
        system('make');
        system('make install');

        chdir($logCurDir);
        Common::deleteDirectory($dirZephir);
        $io->note("Remember to restart Web server!!");
    }


}