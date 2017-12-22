<?php
/**
 * File containing the FolgerQueryType class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPBundle\GraphQL\Query;

use DAPBundle\GraphQL\Query\Record\RecordsField;
use DAPBundle\GraphQL\Query\Record\AllRecordsField;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\Scalar\DateTimeType;
use Youshido\GraphQL\Type\Object\ObjectType;

class FolgerQueryType extends AbstractObjectType
{
	/**
	 * @param ObjectTypeConfig $config
	 *
	 * @return mixed
	 */
	public function build($config)
	{
		$config
			->setDescription('Folger queries')
			->addFields([
				'hello' => [
					'description' => 'Simple GraphQL query which provides custom "Hello world" message.',
					'type' => new StringType(),
					'args' => [
						'name' => [
							'type' => new StringType(),
							'default' => 'Stranger'
						]
					],
					'resolve' => function ($context, $args) {
						if (!empty($args)) {
							return 'Hi there ' . $args['name'] . ", how are you? (Variable successfully received)";
						} else {
							return 'World! (GraphQL endpoint successfully reached)';
						}
					}
				],
				'currentTime' => [
					'description' => 'Simple GraphQL query which provides requested current time on remote server.',
					'type' => new StringType(),
					'resolve' => function () {
						return date('Y-m-d H:ia') . " (Function successfully requested on remote server)";
					}
				],
				'basicRecord' => [
					'description' => 'Simple GraphQL query which provides a basic record. (Hardcode record)',
					'type' => new ObjectType([
						'name' => 'basicRecord',
						'fields' => [
							'id' => new IntType(),
							'dapID' => new StringType(),
							'createdDate' => new DateTimeType(),
							'updatedDate' => new DateTimeType(),
							'remoteSystem' => new StringType(),
							'remoteID' => new StringType(),
							'recordType' => new StringType(),
							'metadata' => new StringType(),
						],
					]),
					'resolve' => function () {
						return [
							"id" => 15,
							"dapID" => 'e039757e-2630-4259-8190-e73f78d77278',
							"createdDate" => "2003-04-12 04:05:06",
							"updatedDate" => "2003-04-12 04:05:06",
							"remoteSystem" => "e5e696a8-e209-4ed1-a562-7ebb0c26d589",
							"remoteID" => "128835",
							"recordType" => "1",
							"metadata" => '"{\"id\":null,\"about\":null,\"author\":\"Middleton, Thomas,\",\"citation\":null,\"datePublished\":{\"timezone\":{\"name\":\"UTC\",\"location\":{\"country_code\":\"??\",\"latitude\":0,\"longitude\":0,\"comments\":\"\"}},\"offset\":0,\"timestamp\":0},\"publisher\":\"compos\'de by Tho. Middleton.\",\"genre\":\"Manuscripts (documents)\\nPlays (performed works)\\nPoems\\nPrologues and epilogues\\n\",\"mentions\":null,\"material\":\"77 p. ;\\n187 x 143 mm\",\"name\":\"A game at chess\",\"identifier\":\"V.a.342\",\"description\":\"Contains the longest and most complete version of banned play by Middleton. Scribal copy, in two unknown hands, overseen by Middleton. Title page in Middleton\'s autograph.\"}"',
						];
					}
				],
				new allRecordsField(),
				new recordsField()
			]);
	}
}