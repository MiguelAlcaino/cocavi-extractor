<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 20/02/17
 * Time: 16:59
 */

namespace AppBundle\Repository;


use AppBundle\Entity\StreamedPodcast;
use Doctrine\ORM\EntityRepository;

class StreamedPodcastRepository extends EntityRepository
{
    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return StreamedPodcast|null
     */
    public function getOneBetweenDates(\DateTime $start, \DateTime $end){
        $query = $this->createQueryBuilder('s')
            ->where('s.created >= :start_date AND s.created < :end_date')
            ->setParameter('start_date',$start)
            ->setParameter('end_date',$end)
            ->getQuery();
        return $query->getOneOrNullResult();
    }
}