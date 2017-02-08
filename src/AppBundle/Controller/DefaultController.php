<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $baseUrl = 'http://www.rinoceronte.fm/podcast/9';
        if($request->query->has('page')){
            $url = $baseUrl.'?Podcast_page='.$request->query->get('page');
        }else{
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

        return $this->render('@App/Default/index.html.twig', array(
            'podcasts' => $arrayOfPodcasts,
            'pages' => $pages
        ));
    }

    /**
     * @Route("/stream", name="stream_cocavi")
     */
    public function streamAction(){
        return new StreamedResponse(function(){
            header('Content-Type: application/octet-stream');
            header('Content-disposition: attachment; filename="hola.mp4"');
            $command = 'megaget --username=***** --password=***** /Root/VID_20150329_144648263.mp4 --path=-';
            $fp = popen($command,'r');
            $bufsize = 8192;
            $buff = '';
            while( !feof($fp) ) {
                $buff = fread($fp, $bufsize);
                echo $buff;
            }
            pclose($fp);
        });
    }
}
