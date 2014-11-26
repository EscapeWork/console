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
        $process = new Process('git clone git@github.com:escapecriativacao/laravel-bootstrap.git ' . $directory);
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            $output->writeln('<error>Erro ao clonar o projeto! ' . $process->getErrorOutput() . '</error>');
        }

        $output->writeln('<comment> -> Projeto clonado</comment>');
    }

    protected function bootstrap($directory, OutputInterface $output)
    {
        $this->executeCommand('cd ' . $directory);
        $this->executeCommand('npm install');
    }

    protected function executeCommand($command)
    {
        $process = new Process($command);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new Exception($process->getErrorOutput());
        }
    }
}