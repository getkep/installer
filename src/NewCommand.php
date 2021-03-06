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
            'cd '.$input->getArgument('name'),
            'mkdir v1',
            'mkdir v1/controllers',
            'mkdir v1/models',
            'mkdir v1/seeds',
            'echo '.'"'.$this->configFile().'"'.' >> config.php',
            'echo '.'"'.$this->indexFile().'"'.' >> v1/index.php',
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

    protected function configFile()
    {
        return "<?php
        class configuration
        {
            public function config()
            {
                return [
                    'directory' => 'v1',
                    'connections' => [
                        'mysql' => [
                            'driver' => 'mysqli',
                            'host' => 'localhost',
                            'database' => 'Data',
                            'username' => 'root',
                            'password' => 'password',
                        ],
                    ],
                    'authentication' => [
                        'mysql' => [
                            'activate' => true,
                            'table' => 'Tabela',
                            'column' => 'Coluna',
                        ],
                    ],
                ];
            }
        }";
    }

    protected function indexFlie()
    {
        return "<?php
        require_once '../vendor/autoload.php';

        use GetKep\Kep\Routing\Route;
        Route::group('v1', function () {

        });
        ";
    }

    protected function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" composer.phar';
        }
        return 'composer';
    }
}