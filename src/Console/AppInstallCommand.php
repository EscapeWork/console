<?php namespace Escape\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Exception;

class AppInstallCommand extends BaseCommand 
{

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:install')
             ->setDescription('Create a new escape/laravel-bootstrap application.')
             ->addArgument('name', InputArgument::REQUIRED)
             ->addOption('--with-manager', null, InputOption::VALUE_NONE);
    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setInputInterface($input);
        $this->setOutputInterface($output);
        
        $this->verifyApplicationDoesntExist($directory = getcwd().'/'.$input->getArgument('name'));

        $this->info('Crafting application...');

        $this->cloneRepo($directory);
        $this->bootstrap($directory);
        $this->createReadme();
        $this->createDotEnv();

        # manager
        if ($input->getOption('with-manager')) {
            $this->comment(' -> Installing escapework/manager... This may take a while...');

            $command = $this->getApplication()->find('manager:install');
            $input   = new ArrayInput(['command' => 'manager:install']);
            $code = $command->run($input, $output);

            if ($code == 0) {
                $this->info(' -> escapework/manager successfully installed!');
            }
        }

        $this->comment('Application ready! Go build something amazing.');
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory)
    {
        if (is_dir($directory)) {
            $this->error(' -> Application already exists!!!');
            exit(1);
        }
    }

    protected function cloneRepo($directory)
    {
        $this->comment(' -> Cloning the escapecriativacao/laravel-bootstrap repository...');
        $this->executeCommand('git clone git@github.com:escapecriativacao/laravel-bootstrap.git ' . $directory);

        chdir($directory);
    }

    protected function bootstrap($directory)
    {
        $this->comment(' -> Installing npm dependencies...');
        $this->executeCommand('npm install');

        $this->comment(' -> Installing composer dependencies...');
        $this->executeCommand('composer install');

        $this->comment(' -> Generating laravel key...');
        $this->executeCommand('php artisan key:generate');

        $this->comment(' -> Removing .git directory...');
        $this->executeCommand('rm -rf .git');

        $this->comment(' -> Initializing a new .git directory...');
        $this->executeCommand('git init');
    }

    protected function createReadme()
    {
        $file = fopen('readme.md', 'w');
        ftruncate($file, 0);
        fwrite($file, file_get_contents('https://gist.githubusercontent.com/luisdalmolin/b90f23bb0fc068c6e805/raw/bac945128518b783b0c552b05ecd0ea92fde6ed7/readme.md'));
        fclose($file);
    }

    protected function createDotEnv()
    {
        $file = fopen('.env', 'w');
        ftruncate($file, 0);
        fwrite($file, file_get_contents('https://gist.githubusercontent.com/luisdalmolin/b90f23bb0fc068c6e805/raw/71d0f23e6f5f625397b1274bcdaf223e1ea15ded/.env'));
        fclose($file);
    }
}