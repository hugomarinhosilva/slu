<?php

namespace UFT\SluBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AtualizarGruposEmailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('AtualizarGruposEmail')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emailManager =
        $this->getContainer()->get('uft.grupo_email.manager');
        $emailManager->atualizarGrupos();
        $emailManager->atualizarListaDeGrupos();

        $output->writeln('Lista de Grupos Atualizada.');
    }

}
