<?php 

namespace Escape\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Question\Question;

abstract class BaseCommand extends Command 
{
    /**
     * Output
     * @var Symfony\Component\Console\Output\InputInterface
     */
    protected $input;

    /**
     * Output
     * @var Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    public function setInputInterface(InputInterface $input)
    {
        $this->input = $input;
    }

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

    protected function ask($question, $default = null)
    {
        $helper   = $this->getHelper('question');
        $question = new Question('<comment>' . $question . '</comment> ', false);

        return $helper->ask($this->input, $this->output, $question) ?: $default;
    }
}