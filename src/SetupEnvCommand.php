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
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetupEnvCommand extends Command
{
    const SAAS_EXTENSION = 'Saas Extension';
    const ZEPHIR_PARSER = 'Zephir Parser';
    const SAAS_MODEL_HOME = 'Saas Model Home';
    const SAAS_SERVICE = 'Saas Service';
    const GIT_REPO_ZEPHIR_PARSER = 'https://github.com/phalcon/php-zephir-parser.git';
    const ENV_DEVELOP = 'develop';
    const ENV_DELIVERY = 'delivery';
    const ENV_PRODUCTION = 'production';
    const GIT_REPO_SAAS_EXTENSION = 'https://github.com/phucps89/saas-ext.git';

    const SM_HOME_PATH = '/usr/local/sm';

    private $os;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('setup-env')
            ->setDescription('Install environment');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->os = Common::operating_system_detection()['id'];
        $this->input = $input;
        $this->output = $output;
        $helper = $this->getHelper('question');
        $arrEnv = [
            1 => self::ZEPHIR_PARSER,
            2 => self::SAAS_EXTENSION,
            3 => self::SAAS_MODEL_HOME,
            4 => self::SAAS_SERVICE,
        ];
        $question = new ChoiceQuestion('Select package:', $arrEnv);
        $ans = $helper->ask($input, $output, $question);

        switch ($ans) {
            case self::ZEPHIR_PARSER:
                $this->installZephirParser();
                break;
            case self::SAAS_EXTENSION:
                $this->installSaasExtension();
                break;
            case self::SAAS_MODEL_HOME:
                $this->installSaasModelHome();
                break;
            case self::SAAS_SERVICE:
                $this->installSassService();
                break;
        }
    }

    private function installZephirParser()
    {
        $io = new SymfonyStyle($this->input, $this->output);
        if (!Common::isRunningAsRoot()) {
            $io->caution("This action must be run as Root permission!!");
            return;
        }

        $io->note("Installing dependencies...");
        $phpVersion = Common::getPhpVersion();
        if ($this->os == 'ubuntu') {
            system("apt-get update");
            system("apt-get --assume-yes install php{$phpVersion}-dev gcc make re2c autoconf automake");
        }
        else if (in_array($this->os, ['centos', 'fedora'])) {
            system("yum update");
            system("yum -y install php-devel gcc make re2c autoconf automake");
        }
        else {
            $io->error("Invalid OS!!");
            return;
        }

        $io->note("Cloning Zephir Parser...");
        $dirZephir = '/php-zephir-parser';
        if (file_exists($dirZephir)) {
            Common::deleteDirectory($dirZephir);
        }
        $repo = GitRepository::cloneRepository(self::GIT_REPO_ZEPHIR_PARSER, $dirZephir);

        $io->note("Installing Zephir Parser...");
        $logCurDir = getcwd();
        chdir($dirZephir);
        system('phpize');
        system('./configure');
        system('make');
        system('make install');

        chdir($logCurDir);
        Common::deleteDirectory($dirZephir);
        $io->newLine();
        $io->note("Add the extension to your php.ini!!");
        $io->section("[Zephir Parser]\nextension=zephir_parser.so");
        exit;
        $io->note("Remember to restart Web server!!");
    }

    private function installSaasExtension()
    {
        $io = new SymfonyStyle($this->input, $this->output);
        if (!Common::isRunningAsRoot()) {
            $io->caution("This action must be run as Root permission!!");
            return;
        }

        $envSelection = [
            1 => self::ENV_DEVELOP,
            2 => self::ENV_DELIVERY,
            3 => self::ENV_PRODUCTION,
        ];
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Select environment:', $envSelection);
        $ans = $helper->ask($this->input, $this->output, $question);

        if (in_array($ans, [self::ENV_PRODUCTION, self::ENV_DELIVERY])) {
            $io->warning('Comming soon');
            return;
        }

        $io->note("Installing dependencies...");
        $phpVersion = Common::getPhpVersion();
        if ($this->os == 'ubuntu') {
            system("apt-get update");
            system("apt-get --assume-yes install php{$phpVersion}-dev gcc make re2c autoconf automake");
        }
        else if (in_array($this->os, ['centos', 'fedora'])) {
            system("yum update");
            system("yum -y install php-devel gcc make re2c autoconf automake");
        }
        else {
            $io->error("Invalid OS!!");
            return;
        }

        $io->note("Cloning Saas extension...");
        $dirSaasExt = '/saas-ext';
        if (file_exists($dirSaasExt)) {
            Common::deleteDirectory($dirSaasExt);
        }
        $repo = GitRepository::cloneRepository(self::GIT_REPO_SAAS_EXTENSION, $dirSaasExt);

        $io->note("Installing Saas extension...");
        $logCurDir = getcwd();
        chdir($dirSaasExt);
        system('phpize');
        system('./configure');
        system('make');
        system('make install');

        chdir($logCurDir);
        Common::deleteDirectory($dirSaasExt);
        $io->newLine();
        $io->note("Add the extension to your php.ini!!");
        $io->section("[Saas Extension]\nextension=saas.so");
        $io->note("Remember to restart Web server!!");
    }

    private function installSaasModelHome()
    {
        $io = new SymfonyStyle($this->input, $this->output);
        if (!Common::isRunningAsRoot()) {
            $io->caution("This action must be run as Root permission!!");
            return;
        }

        if (!file_exists(self::SM_HOME_PATH)) {
            mkdir(self::SM_HOME_PATH);
        }

        $helper = $this->getHelper('question');
        do{
            $question = new Question('Which system user do you want to run SAAS?');
            $ans = $helper->ask($this->input, $this->output, $question);

        }while(empty($ans) || !chown(self::SM_HOME_PATH, $ans));
        $this->chmod_r(self::SM_HOME_PATH, 0755, 0755);
        $this->chown_r(self::SM_HOME_PATH, $ans);
    }

    /**
     * Changes permissions on files and directories within $dir and dives recursively
     * into found subdirectories.
     * @param $dir
     * @param $dirPermissions
     * @param $filePermissions
     */
    function chmod_r($dir, $dirPermissions, $filePermissions)
    {
        $dp = opendir($dir);
        while ($file = readdir($dp)) {
            if (($file == ".") || ($file == ".."))
                continue;

            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($fullPath)) {
                echo('DIR:' . $fullPath . " - $dirPermissions\n");
                chmod($fullPath, $dirPermissions);
                $this->chmod_r($fullPath, $dirPermissions, $filePermissions);
            }
            else {
                echo('FILE:' . $fullPath . " - $filePermissions\n");
                chmod($fullPath, $filePermissions);
            }

        }
        closedir($dp);
    }

    /**
     * Changes permissions on files and directories within $dir and dives recursively
     * into found subdirectories.
     * @param $dir
     * @param $user
     */
    function chown_r($dir, $user)
    {
        $dp = opendir($dir);
        while ($file = readdir($dp)) {
            if (($file == ".") || ($file == ".."))
                continue;

            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($fullPath)) {
                echo('DIR:' . $fullPath . "\n");
                chown($fullPath, $user);
                $this->chown_r($fullPath, $user);
            }
            else {
                echo('FILE:' . $fullPath . "\n");
                chown($fullPath, $user);
            }

        }
        closedir($dp);
    }


    private function installSassService()
    {
        $io = new SymfonyStyle($this->input, $this->output);
        if (!Common::isRunningAsRoot()) {
            $io->caution("This action must be run as Root permission!!");
            return;
        }

        if (!file_exists(self::SM_HOME_PATH)) {
            exit("Sass Model home directory does not exist");
        }
        else {
            if (!is_writable(self::SM_HOME_PATH)) {
                exit("Sass Model home directory is not writable");
            }
        }

        $result = exec('command -v java >/dev/null && echo "yes" || echo "no"');
        if($result == 'no'){
            $io->caution("Please install java before!!");
            return;
        }

        $envSelection = [
            1 => self::ENV_DEVELOP,
            2 => self::ENV_DELIVERY,
            3 => self::ENV_PRODUCTION,
        ];
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Select environment:', $envSelection);
        $ans = $helper->ask($this->input, $this->output, $question);

        if (in_array($ans, [self::ENV_PRODUCTION, self::ENV_DELIVERY])) {
            $io->warning('Comming soon');
            return;
        }

        $io->note("Installing dependencies...");
        if ($this->os == 'ubuntu') {
            system("apt-get update");
            system("apt-get --assume-yes wget supervisor");
        }
        else if (in_array($this->os, ['centos', 'fedora'])) {
            system("yum update");
            system("yum -y install wget supervisor");
        }
        else {
            $io->error("Invalid OS!!");
            return;
        }

        $logCurDir = getcwd();

        $io->note("Download Saas Service...");
        chdir(self::SM_HOME_PATH);
        system("wget -q https://s3.amazonaws.com/seldat-dev-public/saas/ext/develop/saas.jar -O saas.jar");

        $saasServiceFile = self::SM_HOME_PATH . DIRECTORY_SEPARATOR . 'saas.jar';
        do{
            $question = new Question('Which system user do you want to run SAAS Service?');
            $ans = $helper->ask($this->input, $this->output, $question);

        }while(empty($ans) || !chown($saasServiceFile, $ans));

        $io->note("Installing Saas Service...");
        chdir('/etc/supervisor/conf.d');
        $configSupervisor = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'saas_supervisor');
        $configSupervisor = str_replace(':username', $ans, $configSupervisor);
        system("echo '{$configSupervisor}' > saas.conf");
        system('supervisorctl update saas');
        system('supervisorctl restart saas');
//        system('supervisorctl update');

        chdir($logCurDir);

        $io->success("DONE");
    }


}