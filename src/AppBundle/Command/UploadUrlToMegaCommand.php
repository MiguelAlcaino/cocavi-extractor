<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class UploadUrlToMegaCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mega:upload-from-url')
            ->setDescription('Uploads an internet file to mega')
            ->addArgument('url', InputArgument::REQUIRED,'The url')
            ->addArgument('fileName',InputArgument::OPTIONAL,'Optional file name to save inside Mega.nz');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rand = rand();
        $randFileName = $rand.'.mp3';

        if($input->hasArgument('fileName')){
            $fileName = $input->getArgument('fileName');
        }else{
            $fileName = $randFileName;
        }

        $filePath = "/tmp/".$randFileName;
        file_put_contents($filePath, fopen($input->getArgument('url'), 'r'));

        $megaUser = $this->getContainer()->getParameter('mega_user');
        $megaPassword = $this->getContainer()->getParameter('mega_password');
        $megaFolder = $this->getContainer()->getParameter('mega_folder');
        $command = sprintf('megaput --username=%s --password=%s --path=%s/%s --reload %s',$megaUser,$megaPassword,$megaFolder,$fileName,$filePath);
        $output->writeln($command);
        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run();

        if(!$process->isSuccessful()){
            throw new ProcessFailedException($process);
        }

        $output->writeln($fileName);
    }
}
