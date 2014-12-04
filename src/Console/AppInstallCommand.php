<?php namespace Escape\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
                ->addArgument('name', InputArgument::REQUIRED);
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
        $this->setOutputInterface($output);
        
        $this->verifyApplicationDoesntExist($directory = getcwd().'/'.$input->getArgument('name'));

        $this->info('Crafting application...');

        $this->cloneRepo($directory);
        $this->bootstrap($directory);

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
            $output->error('Application already exists!');
            exit(1);
        }
    }

    protected function cloneRepo($directory)
    {
        $this->comment('-> Cloning the escapecriativacao/laravel-bootstrap repository...');
        $this->executeCommand('git clone git@github.com:escapecriativacao/laravel-bootstrap.git ' . $directory);
    }

    protected function bootstrap($directory)
    {
        chdir($directory);

        $this->comment('-> Installing npm dependencies...');
        $this->executeCommand('npm install');

        $this->comment('-> Installing composer dependencies...');
        $this->executeCommand('composer install');

        $this->comment('-> Generating laravel key...');
        $this->executeCommand('php artisan key:generate');

        $this->comment('-> Removing .git directory...');
        $this->executeCommand('rm -rf .git');

        $this->comment('-> Initializing a new .git directory...');
        $this->executeCommand('git init');
    }
}