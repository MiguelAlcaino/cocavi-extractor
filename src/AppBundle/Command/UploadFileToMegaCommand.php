<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UploadFileToMegaCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mega:upload-from-file')
            ->setDescription('Uploads a local file to Mega.nz')
            ->addArgument('path', InputArgument::REQUIRED,'Path of the file')
            ->addArgument('fileName',InputArgument::OPTIONAL,'Optional file name to save inside Mega.nz')
            ->addOption('mega-folder','m',null,'Path of the mega folder');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('path');
        $extension = explode('.',$filePath);
        $extension = array_values(array_slice($extension, -1))[0];;

        $rand = rand();
        $randFileName = $rand.'.'.$extension;

        if($input->hasArgument('fileName')){
            $fileName = $input->getArgument('fileName');
        }else{
            $fileName = $randFileName;
        }


        $megaUser = $this->getContainer()->getParameter('mega_user');
        $megaPassword = $this->getContainer()->getParameter('mega_password');
        if($input->hasOption('mega-folder')){
            $megaFolder = $input->getOption('mega-folder');
        }else{
            $megaFolder = $this->getContainer()->getParameter('mega_folder');
        }

        $command = sprintf('megaput --username=%s --password=%s --path=%s/%s --reload %s',$megaUser,$megaPassword,$megaFolder,$fileName,$filePath);
        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run();

        if(!$process->isSuccessful()){
            throw new ProcessFailedException($process);
        }
        $output->writeln(sprintf('Local file %s has been uploaded correctly!',$fileName));
    }
}
