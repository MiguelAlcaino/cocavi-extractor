<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $baseUrl = 'http://www.rinoceronte.fm/podcast/9';
        if($request->query->has('page')){
            $currentPage = $request->query->get('page');
            $url = $baseUrl.'?Podcast_page='.$currentPage;

        }else{
            $currentPage = 1;
            $url = $baseUrl;
        }

        $html = file_get_contents($url);

        $crawler = new Crawler($html);
        $pagination = $crawler->filter('ul.pagination li');

        $crawler = $crawler->filter('div.items li a');

        $arrayOfPodcasts  = array();
        /** @var \DOMElement $domElement */
        foreach ($crawler as $domElement) {
            $onclick = $domElement->getAttribute('onclick');
            $link = str_replace('doSet(','',$onclick);
            $link = str_replace(')','',$link);
            $data = explode(',',$link);
            $link = str_replace("'",'',$data[0]);
            $arrayOfPodcasts[] = array(
                'link' => $link,
                'date' => str_replace("'",'',$data[1])
            );

        }

        $pages = array();

        /** @var \DOMElement $domElement */
        foreach ($pagination as $domElement){
            if(is_numeric($domElement->textContent)){
                $pages[] = $domElement->textContent;
            }
        }

        $lastPage = $pagination->filter('li.last a')->first()->attr('href');
        $lastPage = explode('Podcast_page=',$lastPage);
        $lastPage = $lastPage[1];

        return $this->render('@App/Default/index.html.twig', array(
            'podcasts' => $arrayOfPodcasts,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage
        ));
    }
}
