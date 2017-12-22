<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RemoteSystem
 *
 * @ORM\Table(name="remote_system")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RemoteSystemRepository")
 */
class RemoteSystem
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
     * @var guid
     *
     * @ORM\Column(name="dapID", type="guid", unique=true)
     * 
     */
    private $dapID;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedDate", type="datetime")
     */
    private $updatedDate;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="uri", type="string", length=255)
     */
    private $uri;

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
     * Set DapID
     *
     * @param guid $dapid
     *
     * @return RemoteSystem
     */
    public function setDapID($dapID)
    {
        $this->dapID = $dapID;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return guid
     */
    public function getDapID()
    {
        return $this->dapID;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return RemoteSystem
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set updatedDate
     *
     * @param \DateTime $updatedDate
     *
     * @return RemoteSystem
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    /**
     * Get updatedDate
     *
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return RemoteSystem
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return RemoteSystem
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set uri
     *
     * @param string $uri
     *
     * @return RemoteSystem
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
}

