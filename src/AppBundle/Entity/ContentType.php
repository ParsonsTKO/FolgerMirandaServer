<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ContentType
 * @package AppBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="contenttype")
 *
 */
class ContentType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * internal use - incremented integer
     */
    public $id;
    /**
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * The primary key of the type, a string identifier that may be used in API calls.
     */
    public $machineName;
    /**
     * @ORM\Column(type="string", length=100)
     */
    public $label;
    /**
     * @ORM\Column(type="string", length=255)
     * A reference to a particular schema the content type implements. Schema IDs are not unique.
     * (I.E. books & manuscripts might both leverage the same schema)
     */
    public $schemaID;
    /**
     * @ORM\Column(type="string", length=1000)
     */
    public $description;
    /**
     * @ORM\Column(type="text")
     * List of required fields on the schema beyond anything the schema declares as required.
     * If this can be represented as a Schema itself that would be great. We might also find a way
     * to have a schema that inherits another and changes some fields to required, if so this would
     * be merged with Schema ID.
     */
    public $requiredFields;


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
     * Set machineName
     *
     * @param guid $machineName
     *
     * @return ContentType
     */
    public function setMachineName($machineName)
    {
        $this->machineName = $machineName;

        return $this;
    }

    /**
     * Get machineName
     *
     * @return guid
     */
    public function getMachineName()
    {
        return $this->machineName;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return ContentType
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
     * Set schemaID
     *
     * @param string $schemaID
     *
     * @return ContentType
     */
    public function setSchemaID($schemaID)
    {
        $this->schemaID = $schemaID;

        return $this;
    }

    /**
     * Get schemaID
     *
     * @return string
     */
    public function getSchemaID()
    {
        return $this->schemaID;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ContentType
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
     * Set requiredFields
     *
     * @param string $requiredFields
     *
     * @return ContentType
     */
    public function setRequiredFields($requiredFields)
    {
        $this->requiredFields = $requiredFields;

        return $this;
    }

    /**
     * Get requiredFields
     *
     * @return string
     */
    public function getRequiredFields()
    {
        return $this->requiredFields;
    }
}
