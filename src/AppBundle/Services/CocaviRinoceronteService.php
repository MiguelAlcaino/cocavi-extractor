<?php

namespace AppBundle\Services;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 08/02/17
 * Time: 23:08
 */
class CocaviRinoceronteService
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * CocaviRinoceronteService constructor.
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param Crawler $crawler
     * @return array
     */
    public function getPodcastsFromCrawler(Crawler $crawler){
        /** @var EntityManager $manager */
        $manager = $this->doctrine->getManager();

        $crawler = $crawler->filter('div.items li a');

        $arrayOfPodcasts  = array();
        /** @var \DOMElement $domElement */
        foreach ($crawler as $domElement) {
            $onclick = $domElement->getAttribute('onclick');
            $link = str_replace('doSet(','',$onclick);
            $link = str_replace(')','',$link);
            $data = explode(',',$link);
            $link = str_replace("'",'',$data[0]);

            $arrayPodcast = array(
                'link' => $link,
                'date' => str_replace("'",'',$data[1])
            );

            $podcast = $manager->getRepository('AppBundle:Podcast')->findOneBy(array(
                'originalUrl' => $link
            ));
            if(!is_null($podcast)){
                $arrayPodcast['megaFileName'] = $podcast->getMegaName();
                $arrayPodcast['id'] = $podcast->getId();
            }

            $arrayOfPodcasts[] = $arrayPodcast;

        }

        return $arrayOfPodcasts;
    }

    /**
     * @param int $page
     * @return string
     */
    public function getHTML($page = 1){
        $baseUrl = 'http://www.rinoceronte.fm/podcast/9';
        if($page != 1){
            $url = $baseUrl.'?Podcast_page='.$page;
        }else{
            $url = $baseUrl;
        }
        return file_get_contents($url);
    }
}
