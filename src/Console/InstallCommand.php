<?php namespace Escape\Console;

use ZipArchive;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Exception;

class InstallCommand extends \Symfony\Component\Console\Command\Command 
{

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('install')
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
        $this->verifyApplicationDoesntExist(
            $directory = getcwd().'/'.$input->getArgument('name'),
            $output
        );

        $output->writeln('<info>Crafting application...</info>');

        $this->cloneRepo($directory, $output);
        $this->bootstrap($directory, $output);

        $output->writeln('<comment>Application ready! Go build something amazing.</comment>');
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory, OutputInterface $output)
    {
        if (is_dir($directory))
        {
            $output->writeln('<error>Application already exists!</error>');

            exit(1);
        }
    }

    protected function cloneRepo($directory, OutputInterface $output)
    {
        $this->comment('-> Cloning the escapecriativacao/laravel-bootstrap repository...', $output);
        $process = new Process('git clone git@github.com:escapecriativacao/laravel-bootstrap.git ' . $directory);
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            $output->writeln('<error>Erro ao clonar o projeto! ' . $process->getErrorOutput() . '</error>');
        }
    }

    protected function bootstrap($directory, OutputInterface $output)
    {
        chdir($directory);

        $this->comment('-> Installing npm dependencies...', $output);
        $this->executeCommand('npm install', $output);

        $this->comment('-> Installing composer dependencies...', $output);
        $this->executeCommand('composer install', $output);

        $this->comment('-> Generating laravel key...', $output);
        $this->executeCommand('php artisan key:generate', $output);

        $this->comment('-> Removing .git directory...', $output);
        $this->executeCommand('rm -rf .git', $output);

        $this->comment('-> Initializing a new .git directory...', $output);
        $this->executeCommand('git init', $output);
    }

    protected function executeCommand($command, OutputInterface $output)
    {
        $process = new Process($command);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new Exception($process->getErrorOutput());
        }
    }

    protected function comment($comment, OutputInterface $output)
    {
        $output->writeln('<comment>' . $comment . '</comment>');
    }
}