<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class CheckNewPodcastsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('podcast:check-new')
            ->setDescription('Checks new podcasts')
            ->addArgument('page',InputArgument::OPTIONAL,'Page number. Default: 1',1);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currentPage = $input->getArgument('page');
        $html = $this->getContainer()->get('app.services.cocavi_rinoceronte_service')->getHTML($currentPage);

        $crawler = new Crawler($html);

        $arrayOfPodcasts = $this->getContainer()->get('app.services.cocavi_rinoceronte_service')->getPodcastsFromCrawler($crawler);

        foreach ($arrayOfPodcasts as $podcast){
            if(!array_key_exists('id',$podcast)){
                $command = $this->getApplication()->find('podcast:save');


                $arguments = array(
                    'command' => 'podcast:save',
                    'url'    => $podcast['link'],
                    'name' => $podcast['date']
                );

                $greetInput = new ArrayInput($arguments);
                $command->run($greetInput, $output);
            }
        }
    }
}
