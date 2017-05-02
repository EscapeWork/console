<?php 

namespace Escape\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Exception;

class OptimizeImagesCommand extends BaseCommand 
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('images:optimize')
             ->setDescription('Otimiza as imagens usando os softwares jpegoptim e optipng')
             ->addArgument('path', InputArgument::REQUIRED)
             ->addOption('--quality', null, InputOption::VALUE_OPTIONAL, 'A qualidade das imagens', '80');
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
        $path = $input->getArgument('path');
        
        $this->comment(' -> Otimizando os JPEgs do diretório ' . $path);
        $this->executeCommand('jpegoptim ' . $path . '/**/*.jpg -m '.$input->getOption('quality').' --strip-all');

        $this->comment(' -> Otimizando os PNGs do diretório ' . $path);
        $this->executeCommand('optipng -o7 ' . $path . '/**/*.png');

        $this->info(' -> Imagens otimizadas com sucesso');
    }
}