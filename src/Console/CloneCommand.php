<?php 

namespace Escape\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Exception;

class CloneCommand extends BaseCommand 
{
    /**
     * Teams
     */
    protected $teams = [
        'escape'  => 'escapecria',
        'morgan'  => 'morgan-bbb',
        'e-lucre' => 'elucre',
        'idea4'   => 'idea4',
    ];

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('clone')
             ->setDescription('Clona um projeto e executa o chain de dependências que precisa ser executado')
             ->addArgument('repo', InputArgument::REQUIRED)
             ->addOption('team', 't', InputOption::VALUE_OPTIONAL, $description = 'O time que o repositorio deve ser clonado', 'escapecria')
             ->addOption('--sudo', null, InputOption::VALUE_NONE);
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
        
        $this->verifyApplicationDoesntExist($directory = getcwd().'/'.$input->getArgument('repo'));
        $this->cloneRepo($directory);
        $this->bootstrap();
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
            $this->error(' -> Este projeto já existe!!!');
            exit(1);
        }
    }

    protected function cloneRepo($directory)
    {
        $repo = $this->input->getArgument('repo');
        $team = $this->getTeam();

        $this->comment(' -> Clonando o repositório ' . $repo . '... Aguarde...');
        $this->executeCommand('git clone git@bitbucket.org:'.$team.'/'.$repo.'.git');
        $this->info(' -> Repositório clonado com sucesso!');

        chdir($directory);
    }

    protected function bootstrap()
    {
        if (is_file('package.json')) {
            $this->comment(' -> Installing npm dependencies...');
            $this->executeCommand($this->input->getOption('sudo') ? 'sudo npm install' : 'npm install');
        }

        if (is_file('bower.json')) {
            $this->comment(' -> Installing bower dependencies...');
            $this->executeCommand('bower install');
        }

        if (is_file('composer.json')) {
            $this->comment(' -> Installing composer dependencies...');
            $this->executeCommand('composer install');
        }

        if (is_dir('storage')) {
            $this->comment(' -> Dando permissão de escrita no diretório storage...');
            $this->executeCommand('chmod -R 777 storage');
        }

        if (is_dir('app/storage')) {
            $this->comment(' -> Dando permissão de escrita no diretório storage...');
            $this->executeCommand('chmod -R 777 app/storage');
        }

        if (is_file('.env.example')) {
            $this->comment(' -> Criando o arquivo .env');
            $this->executeCommand('cp .env.example .env');
        }
    }

    protected function getTeam()
    {
        $team = $this->input->getOption('team');

        return isset($this->teams[$team]) ? $this->teams[$team] : $team;
    }
}