<?php
namespace DAPBundle\ElasticDocs;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * Class DAPAbout
 * @package DAPBundle\ElasticDocs
 * @ES\Object
 */
class DAPAbout
{
    /**
     * @ES\Property(type="text")
     */
    public $uri;


    /**
     * @ES\Property(type="text")
     */
    public $description;

    public function __construct($inUri = null, $inDesc = null) {
        if(isset($inUri)) {
            $this->uri = ($inUri);
        }
        if(isset($inDesc)) {
            $this->description = ($inDesc);
        }
    }

}
