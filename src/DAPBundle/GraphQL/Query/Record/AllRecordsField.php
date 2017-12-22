<?php
/**
 * File containing the AllRecordsField class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPBundle\GraphQL\Query\Record;

use DAPBundle\GraphQL\Type\AllRecordType;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\AbstractType;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQLBundle\Field\AbstractContainerAwareField;
use Youshido\GraphQL\Config\Field\FieldConfig;

class AllRecordsField extends AbstractContainerAwareField
{	
	public function resolve($value, array $args, ResolveInfo $info)
	{
		if (!empty($args)) {
			return $this->container->get('dap.resolver.record')->findBy($args);
		} else {
			return $this->container->get('dap.resolver.record')->findAll();
		}
	}
	
	/**
	 * @return AbstractObjectType|AbstractType
	 */
	public function getType()
	{
		return new ListType(new AllRecordType());
	}
	
	public function build(FieldConfig $config)
	{
		$config
			->setDescription('Complex GraphQL query which provides all storage records. It gets metadata field (Json type).')
			->addArgument(
				'id', new IntType()
			);
	}
}