<?php
/**
 * File containing the RecordType class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPBundle\GraphQL\Type;

use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\IdType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\Scalar\DateTimeType;
use Youshido\GraphQL\Type\Object\ObjectType;

class AllRecordType extends AbstractObjectType
{
    /**
     * @param ObjectTypeConfig $config
     *
     * @return mixed
     */
    public function build($config)
    {
        $config
            ->setDescription('Returns name.')
            ->addFields(
                [
                    'id' => [
                        'description' => 'Serial identifier in the database for relational indexes.
                         Not for use in API.',
                        'type' => new NonNullType(new IdType())
                    ],
                    'dapID' => [
                        'description' => 'Universal identifier for the DAP record across services. 
                        Used to request records by ID in API. Uses UUIDv4 standard.',
                        'type' => new StringType()
                    ],
                    'createdDate' => [
                        'description' => 'On first insertion. Do not change this on updates.',
                        'type' => new DateTimeType()
                    ],
                    'updatedDate' => [
                        'description' => 'On first insertion set to the same exact value as createdDate.',
                        'type' => new DateTimeType()
                    ],
                    'remoteSystem' => [
                        'description' => 'ID used to configure future automated import plugins for syncing content 
                        to the remote system, as well as to configure any rules needed for interpreting the 
                        RemoteUniqueID field.  It will also be used to manage collisions between RemoteUniqueIDs 
                        generated from different remote systems.',
                        'type' => new StringType()
                    ],
                    'remoteID' => [
                        'description' => '',
                        'type' => new StringType()
                    ],
                    'recordType' => [
                        'description' => 'The record type allows well-indexed querying to differentiate by some 
                        top-level categorization of records. It is also a lookup identifier for record-specific 
                        configuration or logic.',
                        'type' => new StringType()
                    ],
                    'metadata' => [
                        'description' => 'The full schema-compliant metadata record. It should include everything 
                        needed for surfacing this discrete item in the API or pushing to the search index.',
                        'type' => new ObjectType([
                            'name' => 'object',
                            'fields' => [
                                'name' => new StringType(),
                            ]
                        ])
                    ]
                ]
            );
    }
}