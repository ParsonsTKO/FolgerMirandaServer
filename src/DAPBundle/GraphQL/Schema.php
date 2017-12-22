<?php
/**
 * File containing the Schema class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPBundle\GraphQL;

use DAPBundle\GraphQL\Query\FolgerQueryType;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Config\Schema\SchemaConfig;

class Schema extends AbstractSchema
{
    public function build(SchemaConfig $config)
    {	
    	$config
    		->setQuery(new FolgerQueryType());
    }

}
