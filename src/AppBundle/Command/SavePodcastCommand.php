<?php

namespace AppBundle\Command;

use AppBundle\Entity\Podcast;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

class SavePodcastCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('podcast:save')
            ->setDescription('Saves and upload a podcast file and uploads it to mega')
            ->addArgument('url', InputArgument::REQUIRED,'URL of the file')
            ->addArgument('name',InputArgument::REQUIRED,'Name of the file');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $manager */
        $manager = $this->getContainer()->get('doctrine')->getManager();
        $url = $input->getArgument('url');
        $podcast = $manager->getRepository('AppBundle:Podcast')->findOneBy(array(
            'originalUrl' => $url
        ));

        $lock = new LockHandler($url);

        if(!$lock->lock()){
            $output->writeln('This file is being uploaded yet!');
            return 0;
        }

        if(is_null($podcast)){
            $podcast = new Podcast();
        }
        $podcast->setName($input->getArgument('name'))
            ->setOriginalUrl($input->getArgument('url'));

        $command = $this->getApplication()->find('mega:upload-from-url');

        $fileName = str_replace('http://www.rinoceronte.fm/audio/podcast/','',$podcast->getOriginalUrl());

        $output->writeln($fileName);

        $arguments = array(
            'command' => 'mega:upload-from-url',
            'url'    => $podcast->getOriginalUrl(),
            'fileName' => $fileName
        );

        $greetInput = new ArrayInput($arguments);
        $command->run($greetInput, $output);

        $output->writeln(print_r($fileName,true));
        $podcast->setMegaName($fileName);

        $manager->persist($podcast);
        $manager->flush();

        $output->writeln("LISTO!");
        $lock->release();
    }
}
