<?php
/**
 * Created by PhpStorm.
 * User: malcaino
 * Date: 20/02/17
 * Time: 16:33
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class StreamedPodcast
 * @package AppBundle\Entity
 * @ORM\Table(name="streamed_podcast")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StreamedPodcastRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class StreamedPodcast
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="file_name", type="string", length=100, nullable=false)
     */
    private $fileName;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     *
     * @return StreamedPodcast
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return StreamedPodcast
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function persistDates(){
        $this->created = new \DateTime('now');
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }
}
