<?php

namespace Kep\Installer\Console;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Creat a new Kep application')
            ->addArgument('name', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->verifyApplicationDoesntExist(
            $directory = ($input->getArgument('name')) ? getcwd().'/'.$input->getArgument('name') : getcwd(),
            $output
        );

        $output->writeln('<info>Crafting application...</info>');

        $composer = $this->findComposer();

        $commands = [
            'mkdir '.$input->getArgument('name'),
            'mkdir '.$input->getArgument('name').'/v1',
            'mkdir '.$input->getArgument('name').'/v1/controllers',
            'mkdir '.$input->getArgument('name').'/v1/models',
            'mkdir '.$input->getArgument('name').'/v1/seeds',
            'cd '.$input->getArgument('name'),
            $composer.' require getkep/kep 0.3.2'
        ];

        $process = new Process(implode(' && ', $commands), $directory, null, null, null);

        $process->run(function ($type, $line) use ($output) {
           $output->write($line);
        });

        $output->writeln('<comment>Application ready! Start scaling APIs.</comment>');
    }

    protected function verifyApplicationDoesntExist($directory, OutputInterface $output)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }

        $output->writeln($directory);
    }

    protected function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" composer.phar';
        }
        return 'composer';
    }
}