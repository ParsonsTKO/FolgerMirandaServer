<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Record
 * @package AppBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="record")
 * //https://github.com/dunglas/doctrine-json-odm for jsonb
 */
class Record
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
    public $dapID;
    
    /**
     * @ORM\Column(type="datetime")
     */
    public $createdDate;
    
    /**
     * @ORM\Column(type="datetime")
     */
    public $updatedDate;
    
    /**
     * @ORM\Column(type="guid")
     * 
     */
    public $remoteSystem;
    
    /**
     * @ORM\Column(type="string")
     * 
     */
    public $remoteID;

    /**
     * @ORM\Column(type="string")
     * is a key to the recordType table
     */
    public $recordType;
    
    /**
     * @ORM\Column(type="json_document", options={"jsonb": true})
     * JSON field storing the metadata record in a schema-compliant structure.
     */
    public $metadata;

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
     * Set dapID
     *
     * @param guid $uuid
     *
     * @return Record
     */
    public function setDapID($dapID)
    {
        $this->dapID = $dapID;

        return $this;
    }

    /**
     * Get dapID
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
     * @return Record
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
     * @return Record
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
     * Set remoteSystem
     *
     * @param guid $remoteSystem
     *
     * @return Record
     */
    public function setRemoteSystem($remoteSystem)
    {
        $this->remoteSystem = $remoteSystem;

        return $this;
    }

    /**
     * Get remoteSystem
     *
     * @return guid
     */
    public function getRemoteSystem()
    {
        return $this->remoteSystem;
    }

    /**
     * Set remoteID
     *
     * @param guid $remoteID
     *
     * @return Record
     */
    public function setRemoteID($remoteID)
    {
        $this->remoteID = $remoteID;

        return $this;
    }

    /**
     * Get remoteID
     *
     * @return guid
     */
    public function getRemoteID()
    {
        return $this->remoteID;
    }

    /**
     * Set recordType
     *
     * @param integer $recordType
     *
     * @return Record
     */
    public function setRecordType($recordType)
    {
        $this->recordType = $recordType;

        return $this;
    }

    /**
     * Get recordType
     *
     * @return integer
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    /**
     * Set metadata
     *
     * @param json_document $metadata
     *
     * @return Record
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get metadata
     *
     * @return json_document
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
