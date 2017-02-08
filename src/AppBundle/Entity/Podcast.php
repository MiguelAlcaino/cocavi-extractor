<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Podcast
 *
 * @ORM\Table(name="podcast")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PodcastRepository")
 */
class Podcast
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
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="originalUrl", type="text", unique=true)
     */
    private $originalUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="megaName", type="string", length=255, nullable=true)
     */
    private $megaName;

    /**
     * @var bool
     * @ORM\Column(name="is_uploading", type="boolean", options={"default"=false})
     */
    private $uploading = false;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set megaName
     *
     * @param string $megaName
     *
     * @return Podcast
     */
    public function setMegaName($megaName)
    {
        $this->megaName = $megaName;

        return $this;
    }

    /**
     * Get megaName
     *
     * @return string
     */
    public function getMegaName()
    {
        return $this->megaName;
    }

    /**
     * Set originalUrl
     *
     * @param string $originalUrl
     *
     * @return Podcast
     */
    public function setOriginalUrl($originalUrl)
    {
        $this->originalUrl = $originalUrl;

        return $this;
    }

    /**
     * Get originalUrl
     *
     * @return string
     */
    public function getOriginalUrl()
    {
        return $this->originalUrl;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Podcast
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isUploading()
    {
        return $this->uploading;
    }

    /**
     * @param bool $uploading
     * @return Podcast
     */
    public function setUploading($uploading)
    {
        $this->uploading = $uploading;
        return $this;
    }
}

