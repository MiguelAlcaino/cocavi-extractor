<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Podcast;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            $currentPage = $request->query->get('page');
            $url = $baseUrl.'?Podcast_page='.$currentPage;

        }else{
            $currentPage = 1;
            $url = $baseUrl;
        }

        $html = file_get_contents($url);

        $crawler = new Crawler($html);
        $pagination = $crawler->filter('ul.pagination li');

        $arrayOfPodcasts = $this->get('app.services.cocavi_rinoceronte_service')->getPodcastsFromCrawler($crawler);

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

    /**
     * @param Request $request
     * @param int $id
     * @return StreamedResponse
     * @Route("/stream/{id}", name="stream_cocavi")
     */
    public function streamAction(Request $request, $id){
        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();
        return new StreamedResponse(function() use($request, $manager, $id){
            $podcast = $manager->getRepository('AppBundle:Podcast')->find($id);
            header('Content-Type: application/octet-stream');
            header('Content-disposition: attachment; filename="'.$podcast->getMegaName().'"');
            $megaUser = $this->getParameter('mega_user');
            $megaPassword = $this->getParameter('mega_password');
            $megaFolder = $this->getParameter('mega_folder');
            $command = sprintf('megaget --username=%s --password=%s %s/%s --path=-',$megaUser, $megaPassword,$megaFolder,$podcast->getMegaName());

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

    /**
     * @param Request $request
     * @return JsonResponse
     * @Method({"POST"})
     * @Route("/save-forever", name="save_cocavi_forever")
     */
    public function saveForeverAction(Request $request){
        $link = $request->request->get('link');
        $name = $request->request->get('name');

        $command = sprintf("(".$this->get('service_container')->getParameter('kernel.root_dir').'/../bin/console podcast:save %s "%s") > /dev/null 2>/dev/null &',$link,$name);
        shell_exec($command);
        return new JsonResponse(array(
            'status' => 'Todo bien!',
            //'command' => $command
        ));
    }
}
