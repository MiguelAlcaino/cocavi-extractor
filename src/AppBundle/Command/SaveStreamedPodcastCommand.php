<?php

namespace AppBundle\Command;

use AppBundle\Entity\StreamedPodcast;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SaveStreamedPodcastCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('podcast:save-streamed')
            ->setDescription('Saves a streamed podcast, stored locally, in Mega.nz and creates a new entry in StreamedPodcast table.')
            ->addArgument('path', InputArgument::REQUIRED,'Path of the file');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('path');
        $today = new \DateTime('today');
        $tomorrow = clone $today;
        $tomorrow->add(new \DateInterval('P1D'));

        /** @var EntityManager $manager */
        $manager = $this->getContainer()->get('doctrine')->getManager();
        $streamedPodcast = $manager->getRepository('AppBundle:StreamedPodcast')->getOneBetweenDates(
            $today,
            $tomorrow
        );

        if(is_null($streamedPodcast)){
            $streamedPodcast = new StreamedPodcast();

            $fileName = explode("/",$filePath);
            $fileName = array_values(array_slice($fileName, -1))[0];;
            $output->writeln($fileName);
            $streamedPodcast->setFileName($fileName);
        }


        $command = $this->getApplication()->find('mega:upload-from-file');

        $arguments = array(
            'command' => 'mega:upload-from-file',
            'path'    => $filePath,
            'fileName' => $streamedPodcast->getFileName(),
            '--mega-folder' => $this->getContainer()->getParameter('mega_streamed_podcasts_folder')
        );

        $greetInput = new ArrayInput($arguments);
        $command->run($greetInput, $output);

        $manager->persist($streamedPodcast);
        $manager->flush();

        $output->writeln(sprintf("Podcast %s has been saved successfully!",$streamedPodcast->getFileName()));
    }
}
