<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Podcast;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
        $currentPage = $request->query->get('page',1);
        $html = $this->get('app.services.cocavi_rinoceronte_service')->getHTML($currentPage);

        $crawler = new Crawler($html);

        $arrayOfPodcasts = $this->get('app.services.cocavi_rinoceronte_service')->getPodcastsFromCrawler($crawler);

        $pagination = $crawler->filter('ul.pagination li');

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
     * @Route("/streamed-podcasts", name="streamed_podcasts")
     * @Template("@App/Default/streamedPodcastsList.html.twig")
     */
    public function streamedPodcastsAction(Request $request){
        $currentPage = $request->query->get('page',1);
        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();

        $streamedPodcasts = $manager->getRepository('AppBundle:StreamedPodcast')->findBy(
            array(),
            array(
                'created' => 'DESC'
            )
        );

        //TODO: Calculate pages
        $pages = array(1) ;
        return array(
            'currentPage' => $currentPage,
            'streamedPodcasts' => $streamedPodcasts,
            'pages' => $pages
        );
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
     * @param int $id
     * @return StreamedResponse
     * @Route("/stream-streamed/{id}", name="stream_streamed_cocavi")
     */
    public function streamStreamedAction(Request $request, $id){
        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();
        return new StreamedResponse(function() use($request, $manager, $id){
            $streamedPodcast = $manager->getRepository('AppBundle:StreamedPodcast')->find($id);
            header('Content-Type: application/octet-stream');
            header('Content-disposition: attachment; filename="'.$streamedPodcast->getFileName().'"');
            $megaUser = $this->getParameter('mega_user');
            $megaPassword = $this->getParameter('mega_password');
            $megaFolder = $this->getParameter('mega_streamed_podcasts_folder');
            $command = sprintf('megaget --username=%s --password=%s %s/%s --path=-',$megaUser, $megaPassword,$megaFolder,$streamedPodcast->getFileName());

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
