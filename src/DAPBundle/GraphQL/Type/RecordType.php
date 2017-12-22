<?php
/**
 * File containing the RecordType class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPBundle\GraphQL\Type;

use Doctrine\DBAL\Types\IntegerType;
use GuzzleHttp\Tests\Psr7\Str;
use Symfony\Component\ExpressionLanguage\Tests\Node\Obj;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\IdType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\Scalar\DateTimeType;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\ListType\ListType;

class RecordType extends AbstractObjectType
{
    /**
     * @param ObjectTypeConfig $config
     *
     * @return mixed
     */
    public function build($config)
    {
        $config
            ->setDescription('Records query which returns all or specifics atrtibutes of record element.')
            ->addFields([
                'dapID' => [
                    'description' => 'Universal identifier for the DAP record across services. Used to request records 
                    by ID in API. Uses UUIDv4 standard.',
                    'type' => new StringType()
                ],
                'recordType' => [
                    'description' => 'The record type allows well-indexed querying to differentiate by some top-level 
                    categorization of records. It is also a lookup identifier for record-specific 
                    configuration or logic.',
                    'type' => new StringType()
                ],
                'about' => [
                    'description' => 'Contains a list of tuples defining subjects of the work. Each tuple is 
                    comprised of uri (which may point to an authority reference) and description (which contains a 
                    textual representation of the subject).<br>For example, a record may contain 
                    {"uri": "http://id.loc.gov/authorities/names/sh85015739", 
                    "description": "Books, Conservation and restoration"}, ',
                    'type' => new ObjectType([
                        'name' => 'about',
                        'fields' => [
                            'uri' => new StringType(),
                            'description' => new StringType(),
                        ]
                    ])
                ],
                'additionalType' => [
                    'description' => '<span style="color:#f00">DEPRECATED</span>',
                    'type' => new StringType()
                ],
                'alternateName' => [
                    'description' => 'Provides an alternate name for the item. This is particularly useful for 
                    disambiguation and for the handling of transliterated works\' titles.',
                    'type' => new StringType()
                ],
                /*'alternateName' => [
                    'description' => '',
                    'type' => new ObjectType([
                        'name' => 'alternateName',
                        'fields' => [
                            'uri' => new StringType(),
                            'description' => new StringType(),
                        ]
                    ])
                ],*/
                'creator' => [
                    'description' => 'Contains a tuple with the information on the creator of the item. 
                    This tuple is comprised of authority (containing a link to an authority reference), 
                    familyName (containing the family name of hte creator), and givenName 
                    (containing the creator\'s given name)<br>For example, a record may contain
                    {"authority": "http://id.loc.gov/authorities/names/n2008083110", "familyName": "Osherow", 
                    "givenName": "Michelle"}',
                    'type' => new ObjectType([
                        'name' => 'creator',
                        'fields' => [
                            'authority' => new StringType(),
                            'familyName' => new StringType(),
                            'givenName' => new StringType(),
                        ]
                    ])
                ],
                'dateCreated' => [
                    'description' => 'A string containing a human-readable representation of the date. Examples 
                    include: "1589 October 12" and "MCCCCCIII, die tertio mensis Augusti [3 Aug. 1503]"',
                    'type' => new StringType()
                ],
                /*'datePublished' => [
                    'description' => '',
                    'type' => new StringType()
                ],
                /*/
                'datePublished' => [
                    'description' => 'Contains a tuple with two integer values: startDate and endDate. This is used
                    for searching. Either value of the tuple may be left blank.',
                    'type' => new ObjectType([
                        'name' => 'datePublished',
                        'fields' => [
                            'startDate' => new StringType(),
                            'endDate' => new StringType(),
                        ]
                    ])
                ],
                'description' => [
                    'description' => 'A textual description of the item written for human consumption. Frequently used
                    to hold notes on the item as well.',
                    'type' => new StringType()
                ],
                'extent' => [
                    'description' => 'A textual description measuring the size of the item as appropriate for its
                     genre. Examples include: "8 items", "1 videodisc (64 min.)", "XXVI, [2] p. (the last p. blank), 
                     6 folded leaves of plates".',
                    'type' => new StringType()
                ],
                'file_location' => [
                    'description' => 'If this record has its own binary file attached, this is where to find it. 
                    This file will typically be returned as one of the related items as well.',
                    'type' => new StringType()
                ],
                'folgerCallNumber' => [
                    'description' => 'The Folger Shakespeare Library\'s call number for this item, if appropriate.',
                    'type' => new StringType()
                ],
                'folgerDimensions' => [
                    'description' => '<span style="color:#f00">DEPRECATED</span>',
                    'type' => new StringType()
                ],
                'folgerProvenance' => [
                    'description' => 'A textual description of the origin and acquisition of the item.',
                    'type' => new StringType()
                ],
/*
 * 				'folgerRelatedItems' => [
                    'description' => '',
                    'type' => new ListType(new ObjectType([
                        'name' => 'genre',
                        'fields' => [
                            'id' => new StringType(),
                            'rootfile' => new StringType(),
                            'label' => new StringType(),
                            'mpso' => new StringType(),
                            'about' => new StringType(),
                            'description' => new StringType(),
                        ]
                    ]))
                ],
*/
                'folgerRelatedItems' => [
                    'description' => 'A list of tuples describing a curated set of related items for display on the 
                    page. Typically used to show various pages of a book, or recordings of a performance. Each tuple 
                    contains:
                    <ul>
                    <li>id: DAP ID of related item, if possible </li>
                    <li>folgeRemoteIdentification:
                    <ul>
                        <li>folgerRemoteUniqueID: key to item in remote system</li>
                        <li>folgerRemoteSystemID: key to the remote system holding the item</li>
                    </ul></li>
                    <li>folgeRelationshipType: key to the relationship between the items (e.g. owner, contained in)</li>
                    <li>folgerObjectType: is this video, audio, a web page for embedding, or something 
                    even more exciting?</li>
                    <li>label: short description to display</li>
                    <li>mpso: Multi Page Sort Order, used internally to order items in this set of tuples</li>
                    <li>description: longer description of related item for display in context</li>
                    </ul>',
                    'type' => new ListType(new ObjectType([
                        'name' => 'folgerRelatedItems',
                        'fields' => [
                            'id' => new StringType(),
                            'folgerRemoteIdentification' => new ObjectType([
                                'name' => 'folgerRemoteIdentification',
                                'description' => '',
                                'fields' => [
                                    'folgerRemoteUniqueID' => new StringType(),
                                    'folgerRemoteSystemID' => new StringType()
                                ]
                            ]),
                            'folgerRelationshipType' => new StringType(),
                            'folgerObjectType' => new StringType(),
                            'label' => new StringType(),
                            'mpso' => new StringType(),
                            'description' => new StringType(),
                        ]
                    ]))
                ],
                'format' => [
                    'description' => 'Currently, one of image, 3d object, printed text, video, sound, manuscript text,
                    notated music, text.
                    <br>Future plans have it as a URI that identifies the type of content contained within this record. 
                    This URI will be used to retrieve/identify the validating schema used for importing this content.',
                    'type' => new StringType()
                ],
                'from' => [
                    'description' => '<span style="color:#f00">DEPRECATED</span>',
                    'type' => new StringType()
                ],
                'genre' => [
                    'description' => 'Contains a list of tuples, each identifying a genre to which this item belongs.
                    Each tuple contains: <ul>
                    <li>search: text used for display and search</li>
                    <li>terms: a priority-sorted list of terms for the genre</li>
                    <li>uri: a reference to an authority file on the genre</li>
                    </ul>
                    An example might be:<br>
                    [{"uri": "http://vocab.getty.edu/aat/300027389", 
                    "terms": ["Translations (documents)", "India", "Delhi", "20th century"], 
                    "search": "Translations (documents) --India --Delhi --20th century."}]
                    ',
                    'type' => new ListType(new ObjectType([
                        'name' => 'genre',
                        'fields' => [
                            'search' => new StringType(),
                            'terms'  => new ListType(new StringType()),
                            'uri' => new StringType(),
                        ]
                    ]))
                ],
                'inLanguage' => [
                    'description' => 'The language code for the language of the work. Currently using 3-letter codes. 
                    May standardize on ISO codes in the future.',
                    'type' => new StringType()
                ],
                'isBasedOn' => [
                    'description' => '<span style="color:#f00">DEPRECATED</span>',
                    'type' => new StringType()
                ],
                'license' => [
                    'description' => 'NOT CURRENTLY IN USE. Intented to show license covering material (eg CC-BY)',
                    'type' => new StringType()
                ],
                'locationCreated' => [
                    'description' => 'Shows the location of origin of the item. Consists of a tuple containing 
                    addressLocality and addressCountry. For example: 
                    {"addressCountry": "Ireland", "addressLocality": "Dublin"}',
                    'type' => new ObjectType([
                        'name' => 'locationCreated',
                        'fields' => [
                            'addressLocality' => new StringType(),
                            'addressCountry' => new StringType(),
                        ]
                    ])
                ],
                'MPSO' => [
                    'description' => '<span style="color:#f00">DEPRECATED</span>. (Multi Page Sort Order)',
                    'type' => new StringType()
                ],
                'name' => [
                    'description' => 'The name of the item, fit for human consumption. 
                    For example "Letter from John Younge, Charnes, to Richard Bagot"',
                    'type' => new StringType()
                ],
                'position' => [
                    'description' => '<span style="color:#f00">DEPRECATED</span>',
                    'type' => new StringType()
                ],
                'publisher' => [
                    'description' => '<span style="color:#f00">DEPRECATED</span>',
                    'type' => new StringType()
                ],
                'size' => [
                    'description' => 'Human-readable description of the physical item\'s size. For example:
                    "37 x 28 cm (46 x 35 cm as housed)", "14 cm (8vo)"',
                    'type' => new StringType()
                ],
                'internalRelations' => [
                  'description' => 'Future: Calculated related records. Currently a combination of the record\'s own binary
                  assets and the manually-curated folgerRelatedItems manifest',
                  'type' => new ListType(new ObjectType([
                      'name' => 'internalRelations',
                      'fields' => [
                          /*'id' => new StringType(),
                          'folgerRemoteIdentification' => new ObjectType([
                              'description' => '',
                              'name' => 'folgerRemoteIdentification',
                              'fields' => [
                                  'folgerRemoteUniqueID' => new StringType(),
                                  'folgerRemoteSystemID' => new StringType()
                              ]
                          ]),
                          'folgerRelationshipType' => new StringType(),
                          'folgerObjectType' => new StringType(),
                          'label' => new StringType(),
                          'mpso' => new StringType(),
                          'description' => new StringType()
                          /**/
                          'remoteUniqueID' => new StringType(),
                          'dapID' => new StringType(),
                          'name' => new StringType(),
                          'description' => new StringType(),
                          'date' => new StringType(),
                          'mediaFormat' => new StringType(),
                          'location' => new StringType(),
                          'thumbnail' => new StringType()
                          ]
                  ]))
                ],
                'images' => [
                    'description' => 'Calculated related images based on LUNA-style-import data.',
                    'type' => new ListType(new ObjectType([
                        'name' => 'images',
                        'fields' => [
                            'rootfile' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'callNumber' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'pageNumber' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'multiPageSortOrder' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'title' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'author' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'imprintOrigin' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'bibId' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'holdingsId' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'lunaObjectId' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'lunaImageId' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'lunaURL' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'size4jpgURL' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'size5jpgURL' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'size6jpgURL' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'size7jpgURL' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'dateCreated' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'dateUpdated' => [
                                'description' => '',
                                'type' => new StringType()
                            ],
                            'mainImage' => [
                                'description' => '',
                                'type' => new ObjectType([
                                    'name' => 'mainImage',
                                    'fields' => [
                                        'small' => new StringType(),
                                        'medium' => new StringType(),
                                        'large' => new StringType(),
                                    ]
                                ])
                            ]
                        ]
                    ]))
                ],
                'rootfile' => [
                    'description' => 'LUNA-style remote ID',
                    'type' => new StringType()
                ],
                'callNumber' => [
                    'description' => 'Folger Call Number for LUNA-style imports',
                    'type' => new StringType()
                ],
                'pageNumber' => [
                    'description' => '',
                    'type' => new StringType()
                ],
                'multiPageSortOrder' => [
                    'description' => 'order of image in its collections',
                    'type' => new StringType()
                ],
                'title' => [
                    'description' => 'LUNA-style import equivalent of name.',
                    'type' => new StringType()
                ],
                'author' => [
                    'description' => 'LUNA-style import author name as string',
                    'type' => new StringType()
                ],
                'imprintOrigin' => [
                    'description' => 'String representation of the date of the item for LUNA-style imports',
                    'type' => new StringType()
                ],
                'bibId' => [
                    'description' => 'LUNA-style import Bib ID (pair with holdingsId for full precision)',
                    'type' => new StringType()
                ],
                'holdingsId' => [
                    'description' => 'LUNA-style import Bib ID (pair with bibId for full precision)',
                    'type' => new StringType()
                ],
                'lunaObjectId' => [
                    'description' => 'LUNA-style import legacy information',
                    'type' => new StringType()
                ],
                'lunaImageId' => [
                    'description' => 'LUNA-style import legacy information',
                    'type' => new StringType()
                ],
                'lunaURL' => [
                    'description' => 'LUNA-style iport equivalent of folgerRemoteUniqueID URL',
                    'type' => new StringType()
                ],
                'size4jpgURL' => [
                    'description' => 'Addressing information for LUNA-style imported images files',
                    'type' => new StringType()
                ],
                'size5jpgURL' => [
                    'description' => 'Addressing information for LUNA-style imported images files',
                    'type' => new StringType()
                ],
                'size6jpgURL' => [
                    'description' => 'Addressing information for LUNA-style imported images files',
                    'type' => new StringType()
                ],
                'size7jpgURL' => [
                    'description' => 'Addressing information for LUNA-style imported images files',
                    'type' => new StringType()
                ],
                'dateUpdated' => [
                    'description' => 'Update time of item in origin system for LUNA-style imports',
                    'type' => new StringType()
                ],
                'mainImage' => [
                    'description' => 'Information on how to find small, medium, and large versions of 
                    LUNA-style image imports',
                    'type' => new ObjectType([
                        'name' => 'mainImage',
                        'fields' => [
                            'small' => new StringType(),
                            'medium' => new StringType(),
                            'large' => new StringType(),
                        ]
                    ])
                ]
            ]
        );
    }
}