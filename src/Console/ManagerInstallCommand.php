<?php namespace Escape\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Escape\Json\JsonFile;
use Exception;

class ManagerInstallCommand extends BaseCommand
{

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('manager:install')
             ->setDescription('Install the escapework/manager');
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

        try {
            # adicionar no composer
            $this->comment(' -> Editing the composer.json file...');
            $this->addToComposer();
            $this->info(' -> composer.json updated!');
            $this->comment('');
            
            // # composer update
            $this->comment(' -> Executing composer update command...');
            $this->comment(' -> This may take a while...');
            $this->executeCommand('composer update');
            $this->info(' -> composer update finished!');
            $this->comment('');
            
            # adicionar no config/app.php
            $this->comment(' -> Adding the service provider...');
            $this->editConfigFile();
            $this->info(' -> Service provider added...');
            $this->comment('');
            
            # publicando as configurações do escapework/manager
            $this->comment(' -> Publicando as configurações do escapework/manager...');
            $this->executeCommand('php artisan vendor:publish');
            $this->info(' -> Configurações publicadas!');
            $this->comment('');

            $this->comment(' -> escapework/manager instalado com sucesso!');
            $this->info(' -> Execute o comando "php artisan manager:configure" para configurar a base de dados!');
        }
        catch (\Exception $e) {
            $this->error(' -> ' . $e->getMessage());

            exit(1);
        }
    }

    protected function addToComposer()
    {
        $file = getcwd()  . '/composer.json';

        if (! is_file($file)) {
            throw new \Exception("The composer.json file doesn't exist!");
        }

        $json = new JsonFile($file);
        $data = $json->read();

        $data['require']['escapework/manager'] = '~2.1';

        $data['repositories'] = [
            (object) ['type' => 'composer', 'url' => 'http://packages.escape.ppg.br']
        ];

        $json->write($data);
    }

    protected function editConfigFile()
    {
        $file     = getcwd() . '/config/app.php';
        $contents = file_get_contents($file);
        
        $provider = "        'EscapeWork\Manager\Providers\ManagerServiceProvider',";
        $replace  = '# Third Party Service Providers...';

        $newContent = substr_replace(
            $contents, PHP_EOL . $provider, strpos($contents, $replace) + strlen($replace), 0
        );

        file_put_contents($file, $newContent);
    }
}