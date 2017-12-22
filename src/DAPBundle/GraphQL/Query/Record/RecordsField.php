<?php
/**
 * File containing the RecordsField class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPBundle\GraphQL\Query\Record;

use DAPBundle\GraphQL\Type\RecordType;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\AbstractType;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQLBundle\Field\AbstractContainerAwareField;
use Youshido\GraphQL\Config\Field\FieldConfig;

class RecordsField extends AbstractContainerAwareField
{	
    public function resolve($value, array $args, ResolveInfo $info)
    {
        $fields = array();
        $resolverValue = array();

        if (!empty($info->getFieldASTList())) {
            foreach ($info->getFieldASTList() as $index => $field) {
                $fields[$index] = $field->getName();
            }

            $result = $this->container->get('dap.resolver.record')->findByNativeQuery($args);

            if (count($result) > 0) {
                foreach ($result as $index => $resultItem) {
                    if ($resultItem->dapID) {
                        $resolverValue[$index]['dapID'] = $resultItem->dapID;
                    }

                    if ($resultItem->recordType) {
                        $resolverValue[$index]['recordType'] = $resultItem->recordType;
                    }

                    if (array_key_exists("metadata", $resultItem)) {
                        foreach ($resultItem->metadata as $identifier => $value) {
                            if (in_array($identifier, $fields)) {
                                if ($value != "") {
                                    $resolverValue[$index][$identifier] = $value;
                                }
                            }

                            if (in_array("images", $fields)) {
                                if ($identifier == "folgerRelatedItems") {
                                    if (!empty($value)) {
                                        $folgerRelatedItems = $value;

                                        foreach ($folgerRelatedItems as $folgerRelatedItemIdentifier => $folgerRelatedItemValue) {
                                            if (array_key_exists("folgerRemoteIdentification", $folgerRelatedItemValue)) {
                                                //make sure it's LUNA
                                                if (array_key_exists('folgerRemoteSystemID', $folgerRelatedItemValue['folgerRemoteIdentification']) &&
                                                    strtolower($folgerRelatedItemValue['folgerRemoteIdentification']['folgerRemoteSystemID']) == 'luna') {
                                                    if (array_key_exists("folgerRemoteUniqueID", $folgerRelatedItemValue['folgerRemoteIdentification'])) {
                                                        $resultFolgerRelatedItemArgs = array(
                                                            "rootfile" => $folgerRelatedItemValue['folgerRemoteIdentification']["folgerRemoteUniqueID"]
                                                        );
                                                        $resultFolgerRelatedItem = $this->container->get('dap.resolver.record')->findByNativeQuery($resultFolgerRelatedItemArgs);
                                                        //that is, if it found an image record for this based on looking up its rootfile
                                                        if (count($resultFolgerRelatedItem) > 0) {
                                                            foreach ($resultFolgerRelatedItem as $resultFolgerRelatedItemValue) {
                                                                if (array_key_exists("metadata", $resultFolgerRelatedItemValue)) {
                                                                    $resolverValue[$index]['images'][] = $resultFolgerRelatedItemValue->metadata;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if (in_array("internalRelations", $fields)) {
                                if ($identifier == 'folgerRelatedItems') {
                                    if (!empty($value)) {
                                        $folgerRelatedItems = $value;

                                        //build a list of foreign keys to query, so we can ask one big question instead of a jillion little ones
                                        $internalRelationsForeignKeys = array();
                                        foreach ($folgerRelatedItems as $folgerRelatedItemIdentifier => $folgerRelatedItemValue) {
                                            //make sure it's a part of collection
                                            if (array_key_exists('folgerRelationshipType', $folgerRelatedItemValue) && strtolower($folgerRelatedItemValue['folgerRelationshipType']) == 'partofcollection') {
                                                if (array_key_exists("folgerRemoteIdentification", $folgerRelatedItemValue)) {
                                                    //use the folgerRemoteSystemID to see how to look it up
                                                    if (array_key_exists('folgerRemoteSystemID', $folgerRelatedItemValue['folgerRemoteIdentification']) &&
                                                        strtolower($folgerRelatedItemValue['folgerRemoteIdentification']['folgerRemoteSystemID']) == 'dap-foreign-key') {
                                                        //if DAP-Foreign-Key, add to list to look up using RemoteUniqueID on records
                                                        array_push($internalRelationsForeignKeys, $folgerRelatedItemValue['folgerRemoteIdentification']["folgerRemoteUniqueID"]);
                                                    }
                                                }
                                            }
                                        }
                                        //turn this list into a query for our database
                                        if (count($internalRelationsForeignKeys) > 0) {
                                            $getCollection = array("foreign-collection" => ("'" . implode("', '", $internalRelationsForeignKeys) . "'"));
                                            $resultFolgerRelatedItems = $this->container->get('dap.resolver.record')->findByNativeQuery($getCollection);


                                            //cycle through the results to build our return data structure
                                            if (count($folgerRelatedItemValue) > 0) {
                                                foreach ($resultFolgerRelatedItems as $resultFolgerRelatedItemValue) {
                                                    if (array_key_exists("metadata", $resultFolgerRelatedItemValue)) {
                                                        $t = (object)$resultFolgerRelatedItemValue->metadata;
                                                        $tout = (object)array();
                                                        $tout->remoteUniqueID = $t->RemoteUniqueID;
                                                        $tout->dapID = $resultFolgerRelatedItemValue->dapID;
                                                        if (isset($t->name)) {
                                                            $tout->name = $t->name;
                                                        } else {
                                                            $tout->name = '';
                                                        }
                                                        if (isset($t->description)) {
                                                            $tout->description = $t->description;
                                                        } else {
                                                            $tout->description = '';
                                                        }
                                                        if (isset($t->dateCreated)) {
                                                            $tout->date = $t->dateCreated;
                                                        } elseif (isset($t->datePublished) && (isset($t->datePublished->startDate) || isset($t->datePublished->endDate))) {
                                                            if (isset($t->datePublished->startDate)) {
                                                                $tout->date = $t->datePublished->startDate;
                                                                if (isset($t->datePublished->endDate)) {
                                                                    $tout->date .= ' - ';
                                                                }
                                                            }
                                                            if (isset($t->datePublished->endDate)) {
                                                                $tout->date .= $t->datePublished->endDate;
                                                            }
                                                        }

                                                        if (isset($t->locationCreated) && (isset($t->locationCreated->addressLocality) || isset($t->locationCreated->addressCountry))) {
                                                            if (isset($t->locationCreated->addressLocality)) {
                                                                $tout->location = $t->locationCreated->addressLocality;
                                                                if (isset($t->locationCreated->addressCountry)) {
                                                                    $tout->location .= ', ';
                                                                }
                                                            }
                                                            if (isset($t->locationCreated->addressCountry)) {
                                                                $tout->location .= $t->locationCreated->addressCountry;
                                                            }
                                                        }
                                                        if (isset($t->mediaFormat)) {
                                                            $tout->mediaFormat = $t->folgerGenre;
                                                        } else {
                                                            $tout->mediaFormat = '';
                                                        }
                                                        //do image
                                                        //placeholder for now - will do a lookup of this record's folgerRelatedItems to find the first image, and use that, like the mainImage does?
                                                        $tout->thumbnail = 'https://placeholdit.imgix.net/~text?txtsize=15&txt=DAP&w=50&h=50';

                                                        //okay, we've build our object, now make sure we attach it to our record
                                                        $resolverValue[$index]['internalRelations'][] = $tout;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $resolverValue;
    }

    /**
     * @return AbstractObjectType|AbstractType
     */
    public function getType()
    {
        return new ListType(new RecordType());
    }

    public function build(FieldConfig $config)
    {
        $config
            ->setDescription('Complex GraphQL query which provides all storage records. It gets metadata field (Json type).')
            ->addArgument(
                'id', new IntType()
            )
            ->addArgument(
                'dapID', new StringType()
            )
            ->addArgument(
                'rootfile', new StringType()
            )
            ->addArgument(
                'searchText', new StringType()
            );
    }
}