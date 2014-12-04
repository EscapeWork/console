<?php namespace Escape\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

abstract class BaseCommand extends Command 
{

    /**
     * Output
     * @var Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    protected function executeCommand($command)
    {
        $process = new Process($command);
        $process->setTimeout(60 * 15); # 5min
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
    }

    protected function info($info)
    {
        $this->output->writeln('<info>' . $info . '</info>');
    }

    protected function comment($comment)
    {
        $this->output->writeln('<comment>' . $comment . '</comment>');
    }

    protected function error($comment)
    {
        $this->output->writeln('<error>' . $comment . '</error>');
    }
}