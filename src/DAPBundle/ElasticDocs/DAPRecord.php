<?php
/**
 * Created by PhpStorm.
 * User: johnc
 * Date: 5/11/17
 * Time: 1:50 PM
 */

namespace DAPBundle\ElasticDocs;


use Doctrine\Common\Collections\ArrayCollection;
use ONGR\ElasticsearchBundle\Collection\Collection;
use ONGR\ElasticsearchBundle\Annotation as ES;
/**
 * @ES\Document(type="daprecord")
 */
class DAPRecord
{

    /**
     * @var string
     *
     * @ES\Id()
     */
    public $dapid;

    /**
     * @var string
     *
     * @ES\Property(type="keyword")
     */
    public $dapidagain;

    /**
     * @var string
     *
     * @ES\Property(type="keyword")
     */
    public $isBasedOn;


    /**
     * @var DAPCreator
     *
     * @ES\Embedded(class="DAPBundle:DAPCreator")
     *
     */
    public $creator;

    /**
     * @var string
     *
     * @ES\Property(type="text")
     */
    public $name;

    /**
     * @var DAPAlternateName
     *
     * @ES\Embedded(class="DAPBundle:DAPAlternateName")
     */
    public $alternateName;

    /**
     * @var string
     *
     * @ES\Property(type="integer")
     */
    public $dateCreated;

    /**
     * @var DAPDatePublished
     *
     * @ES\Embedded(class="DAPBundle:DAPDatePublished")
     */
    public $datePublished;

    /**
     * @var string
     *
     * @ES\Property(type="text")
     */
    public $publisher;

    /**
     * @var DAPLocation
     *
     * @ES\Embedded(class="DAPBundle:DAPLocation")
     *
     */
    public $locationCreated;

    /**
     * @var string
     *
     * @ES\Property(type="text")
     */
    public $extent;

    /**
     * @var string
     *
     * @ES\Property(type="text")
     */
    public $size;

    /**
     * @var string
     *
     * @ES\Embedded(class="DAPBundle:DAPDescription", multiple=true)
     */
    public $description; //WE WOULD PREFER THIS TO JUST BE AN ARRAY/COLLECTION OF STRINGS, BUT... APPARENTLY THIS REQUIRES AN OBJECT/CLASS

    /**
     * @var string
     *
     * @ES\Property(type="text")
     */
    public $disambiguatingDescription;

    /**
     * @var string
     *
     * @ES\Property(type="keyword")
     */
    public $genre;

    /**
     * @var DAPGenre
     *
     * @ES\Embedded(class="DAPBundle:DAPGenre", multiple=true)
     *
     */
    public $folgerGenre;

    /**
     * @var string
     *
     * @ES\Property(type="keyword")
     */
    public $folgerCallNumber;

    /**
     * @var string
     *
     * @ES\Property(type="keyword")
     */
    public $format;

    /**
     * @var DAPAgent
     *
     * @ES\Embedded(class="DAPBundle:DAPAgent", multiple=true)
     */
    public $agent;

    /**
     * @var string
     *
     * @ES\Property(type="keyword")
     */
    public $inLanguage;

    /**
     * @var string
     *
     * @ES\Property(type="text")
     */
    public $folgerProvenance;

    /**
     * @var DAPAbout
     *
     * @ES\Embedded(class="DAPBundle:DAPAbout", multiple=true)
     */
    public $about;

    /**
     * @var DAPRelatedItems
     *
     * @ES\Embedded(class="DAPBundle:DAPRelatedItems", multiple=true)
     */
    public $folgerRelatedItems;

    /**
     * @var string
     *
     * @ES\Property(type="text")
     */
    public $searchText;


    public function __construct() {
        $this->description = new Collection();
        $this->folgerGenre = new Collection();
        $this->agent = new Collection();
        $this->about = new Collection();
        $this->folgerRelatedItems = new Collection();
    }

    public function setMy($setMe, $withVal)
    {
        if(!isset($withVal) || is_null($withVal)) { return false; }

        $this->$setMe = $withVal;

        if($setMe == 'dapid') {
            $this->dapidagain = $withVal;
        }

        return true;
    }

    public function fill($invar) {

        if(!isset($invar) || is_null($invar)) { return false; }
        try {
            //don't put LUNA records into the search
            if($invar->recordType != 1) return -2;


            //postgres info
            $this->setMy('dapid', $invar->dapID);

            //reset to deal with just metadata
            $invar = (object)$invar->metadata;

            if (isset($invar->name)) {
                $this->setMy('name', $invar->name);
            } else if(isset($invar->title)) {
                $this->setMy('name', $invar->title);
            }

            if (isset($invar->alternateName)) {
                if(gettype($invar->alternateName) == 'string') {
                    $this->setMy('alternateName', new DAPAlternateName(null, $invar->alternateName));
                } else {
                    $invar->alternateName = (object)$invar->alternateName;
                    $this->setMy('alternateName', new DAPAlternateName($invar->alternateName->uri, $invar->alternateName->description));
                }
            }

            //looks to provide a numeric year for search
            //skips if cannot
            if (isset($invar->dateCreated)) {
                if(is_numeric($invar->dateCreated)) {
                    if(!is_integer($invar->dateCreated))
                    {
                        $invar->dateCreated = (int) $invar->dateCreated;
                    }
                    $this->setMy('dateCreated', $invar->dateCreated);
                }
            }

            if (isset($invar->datePublished)) {
                $invar->datePublished = (object)$invar->datePublished;
                $tStart = isset($invar->datePublished->startDate) ? $invar->datePublished->startDate : null;
                $tEnd = isset($invar->datePublished->endDate) ? $invar->datePublished->endDate : null;
                $this->setMy('datePublished', new DAPDatePublished($tStart, $tEnd));
                //Help search by filling createdDate when necessary
                if(!isset($invar->dateCreated)) {
                    if($tStart) {
                        $this->setMy('dateCreated', $tStart);
                    } elseif($tEnd) {
                        $this->setMy('dateCreated', $tEnd);
                    }
                }
            }

            if (isset($invar->publisher)) {
                $this->setMy('publisher', $invar->publisher);
            }

            if (isset($invar->locationCreated)) {
                $invar->locationCreated = (object)$invar->locationCreated;
                $ttype = isset($invar->locationCreated->type) ? $invar->locationCreated->type : null;
                $taddressLocality = isset($invar->locationCreated->addressLocality) ? $invar->locationCreated->addressLocality : null;
                $taddressCountry = isset($invar->locationCreated->addressCountry) ? $invar->locationCreated->addressCountry : null;
                $this->setMy('locationCreated', new DAPLocation($ttype, $taddressLocality, $taddressCountry));
            }

            if (isset($invar->extent)) {
                $this->setMy('extent', $invar->extent);
            }

            if (isset($invar->size)) {
                $this->setMy('size', $invar->size);
            }


            //description
            if (isset($invar->description)) {
                $tdesc = $invar->description;
                if (isset($tdesc)) {
                    $myDesc = array();
                    if (gettype($tdesc) == 'string') {
                        //turn single item into array
                        array_push($myDesc, new DAPDescription($tdesc));
                    } else if (gettype($tdesc) == 'array') {
                        //build array of DAPDescriptions
                        for ($i = 0; $i < count($tdesc); $i++) {
                            array_push($myDesc, new DAPDescription($tdesc[$i]));
                        }
                    } else {
                        // not array, not string, not workable
                    }
                    if (count($myDesc) > 0) { //if we've added some description(s)
                        $this->setMy('description', new Collection($myDesc));
                    }
                }
            }

            if (isset($invar->disambiguatingDescription)) {
                $this->setMy('disambiguatingDescription', $invar->disambiguatingDescription);
            }

            if (isset($invar->genre)) {
                if (gettype($invar->genre) == "string") { //old data uses array, so lets make sure we ignore that
                    $this->setMy('genre', $invar->genre);
                }
            }

            //folger genre
            if (isset($invar->genre)) {
                $tfolGenre = $invar->genre;
                if (isset($tfolGenre)) {
                    $myFolgerGenre = array();
                    if (gettype($tfolGenre) == 'array') {
                        //build array
                        for ($i = 0; $i < count($tfolGenre); $i++) {
                            if(isset($tfolGenre[$i]['search'])) {
                                $tsearch = $tfolGenre[$i]['search'];
                            } else {
                                $tsearch = "";
                            }
                            if(isset($tfolGenre[$i]['terms'])) {
                                $tterms = $tfolGenre[$i]['terms'];
                            } else {
                                $tterms = array();
                            }
                            if(isset($tfolGenre[$i]['uri'])) {
                                $turi = $tfolGenre[$i]['uri'];
                            } else {
                                $turi = '';
                            }
                            array_push($myFolgerGenre, new DAPGenre($tsearch, $tterms, $turi));
                        }

                    } else {
                        // not array, not workable
                    }
                    if (count($myFolgerGenre) > 0) { //if we've added some description(s)
                        $this->setMy('folgerGenre', new Collection($myFolgerGenre));
                    }
                }
            }
            //end folger genre

            if (isset($invar->folgerCallNumber)) {
                $this->setMy('folgerCallNumber', $invar->folgerCallNumber);
            }

            if (isset($invar->format)) {
                $this->setMy('format', $invar->format);
            }

            if (isset($invar->isBasedOn)) {
                $this->setMy('isBasedOn', $invar->isBasedOn);
            }

            //agent
            if (isset($invar->Agent)) {
                $tAgent = $invar->Agent;
                //die(var_dump($tAgent));
                if (isset($tAgent)) {
                    $myAgent = array();
                    if (gettype($tAgent) == 'array') {
                        //build array
                        for ($i = 0; $i < count($tAgent); $i++) {
                            $tname = isset($tAgent[$i]['name']) ? $tAgent[$i]['name'] : null;
                            $tdescription = isset($tAgent[$i]['description']) ? $tAgent[$i]['description'] : null;
                            $turi = isset($tAgent[$i]['uri']) ? $tAgent[$i]['uri'] : null;
                            $tDAPAgent = new DAPAgent($tname, $tdescription, $turi);
                            array_push($myAgent, $tDAPAgent);
                        }

                    } else {
                        // not array, not workable
                    }
                    if (count($myAgent) > 0) { //if we've added some description(s)
                        $this->setMy('agent', new Collection($myAgent));
                    }
                }
            }
            //end agent

            if (isset($invar->inLanguage)) {
                $this->setMy('inLanguage', $invar->inLanguage);
            }

            if (isset($invar->folgerProvenance)) {
                $this->setMy('folgerProvenance', $invar->folgerProvenance);
            }

            //about
            if (isset($invar->about)) {
                $tAbout = $invar->about;
                $myAbout = array();
                if (gettype($tAbout) == 'array') {
                    //build array
                    for ($i = 0; $i < count($tAbout); $i++) {
                        $turi = isset($tAbout[$i]['uri']) ? $tAbout[$i]['uri'] : null;
                        $tdescription = isset($tAbout[$i]['description']) ? $tAbout[$i]['description'] : null;
                        array_push($myAbout, new DAPAbout($turi, $tdescription));
                    }
                } else {
                    // not array, not workable
                }
                if (count($myAbout) > 0) { //if we've added some description(s)
                    $this->setMy('about', new Collection($myAbout));
                }
            }
            //end about

            //related items
            if (isset($invar->folgerRelatedItems)) {
                $myRelated = array();
                //if (gettype($tRelated) == 'array') {
                    //build array
                    for ($i = 0; $i < count($invar->folgerRelatedItems); $i++) {
                        $tRelated = (object)$invar->folgerRelatedItems[$i];
                        $tcallhack = '@id'; // b/c calling $thing->@id doesn't work
                        $tid = isset($tRelated->$tcallhack) ? $tRelated->$tcallhack : null;
                        $trootfile = isset($tRelated->rootfile) ? $tRelated->rootfile : null;
                        $tlabel = isset($tRelated->label) ? $tRelated->label : null;
                        $tmpso = isset($tRelated->mpso) ? $tRelated->mpso : null;
                        $tabout = isset($tRelated->about) ? $tRelated->about : null;
                        $tdescription = isset($tRelated->description) ? $tRelated->description : null;
                        array_push($myRelated, new DAPRelatedItems($tid, $trootfile, $tlabel, $tmpso, $tabout, $tdescription));
                    }
                //} else {
                    // not array, not workable
                //}
                if (count($myRelated) > 0) { //if we've added some description(s)
                    $this->setMy('folgerRelatedItems', new Collection($myRelated));
                }
            }
            //end related items

            if (isset($invar->searchText)) {
                $this->setMy('searchtext', $invar->searchText);
            }

            if (isset($invar->creator)) {
                $tc = new DAPCreator();
                if (isset($invar->creator['givenName'])) {
                    $tc->givenName = $invar->creator['givenName'];
                }
                if (isset($invar->creator['familyName'])) {
                    $tc->familyName = $invar->creator['familyName'];
                }
                if (isset($invar->creator['authority'])) {
                    $tc->authority = $invar->creator['authority'];
                }
                $this->setMy('creator', $tc);
            }

            return isset($this->dapid) ? $this->dapid : -1;
        } catch (\Exception $ex) {
            return -1;
        }

    }
}