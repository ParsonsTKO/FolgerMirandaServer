<?php
namespace DAPBundle\ElasticDocs;

use ONGR\ElasticsearchBundle\Annotation as ES;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class DAPGenre
 * @package DAPBundle\ElasticDocs
 * @ES\Object
 */
class DAPGenre
{
    /**
     * @ES\Property(type="keyword")
     */
    public $search;


    /**
     * @ES\Property(type="keyword")
     */
    public $terms;

    /**
     * @ES\Property(type="text")
     */
    public $uri;

    public function __construct($inSearch = null, $inTerms = null, $inUri = null)
    {
        $this->terms = new ArrayCollection();

        if (isset($inSearch)) {
            $this->search = $inSearch;
        }

        if (isset($inTerms) && is_array($inTerms)) {
            $this->terms = array_slice($inTerms, 0, 1);
        }

        if (isset($inUri)) {
            $this->uri = $inUri;
        }
    }

}
