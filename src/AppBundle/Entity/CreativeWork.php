<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The most generic kind of creative work, including books, movies, photographs, software programs, etc.
 *
 * @see http://schema.org/CreativeWork Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/CreativeWork")
 */
class CreativeWork extends Thing
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Thing The subject matter of the content
     *
     * 
     * @ApiProperty(iri="http://schema.org/about")
     */
    private $about;

    /**
     * @var string The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably
     *
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     * @ApiProperty(iri="http://schema.org/author")
     */
    private $author;

    /**
     * @var CreativeWork A citation or reference to another creative work, such as another publication, web page, scholarly article, etc
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CreativeWork")
     * @ApiProperty(iri="http://schema.org/citation")
     */
    private $citation;

    /**
     * @var \DateTime Date of first broadcast/publication
     *
     * @Assert\Date
     * @ORM\Column(type="date", nullable=true)
     * @ApiProperty(iri="http://schema.org/datePublished")
     */
    private $datePublished;

    /**
     * @var string The publisher of the creative work
     *
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     * @ApiProperty(iri="http://schema.org/publisher")
     */
    private $publisher;

    /**
     * @var string Genre of the creative work, broadcast channel or group
     *
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     * @ApiProperty(iri="http://schema.org/genre")
     */
    private $genre;

    /**
     * @var Thing Indicates that the CreativeWork contains a reference to, but is not necessarily about a concept
     *
     * 
     * @ApiProperty(iri="http://schema.org/mentions")
     */
    private $mentions;

    /**
     * @var string A material that something is made from, e.g. leather, wool, cotton, paper
     *
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     * @ApiProperty(iri="http://schema.org/material")
     */
    private $material;

    /**
     * Sets id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets about.
     *
     * @param Thing $about
     *
     * @return $this
     */
    public function setAbout(Thing $about = null)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Gets about.
     *
     * @return Thing
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Sets author.
     *
     * @param string $author
     *
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Gets author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets citation.
     *
     * @param CreativeWork $citation
     *
     * @return $this
     */
    public function setCitation(CreativeWork $citation = null)
    {
        $this->citation = $citation;

        return $this;
    }

    /**
     * Gets citation.
     *
     * @return CreativeWork
     */
    public function getCitation()
    {
        return $this->citation;
    }

    /**
     * Sets datePublished.
     *
     * @param \DateTime $datePublished
     *
     * @return $this
     */
    public function setDatePublished(\DateTime $datePublished = null)
    {
        $this->datePublished = $datePublished;

        return $this;
    }

    /**
     * Gets datePublished.
     *
     * @return \DateTime
     */
    public function getDatePublished()
    {
        return $this->datePublished;
    }

    /**
     * Sets publisher.
     *
     * @param string $publisher
     *
     * @return $this
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Gets publisher.
     *
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Sets genre.
     *
     * @param string $genre
     *
     * @return $this
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Gets genre.
     *
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Sets mentions.
     *
     * @param Thing $mentions
     *
     * @return $this
     */
    public function setMentions(Thing $mentions = null)
    {
        $this->mentions = $mentions;

        return $this;
    }

    /**
     * Gets mentions.
     *
     * @return Thing
     */
    public function getMentions()
    {
        return $this->mentions;
    }

    /**
     * Sets material.
     *
     * @param string $material
     *
     * @return $this
     */
    public function setMaterial($material)
    {
        $this->material = $material;

        return $this;
    }

    /**
     * Gets material.
     *
     * @return string
     */
    public function getMaterial()
    {
        return $this->material;
    }
}
