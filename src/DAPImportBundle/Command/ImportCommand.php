<?php
/**
 * File containing the ImportCommand class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPImportBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Container;
use Psr\Log\LoggerInterface;

class ImportCommand extends Command {
	
	/**
	 *
	 * @var Container
	 */
	private $container;
	
	/**
	 * @var LoggerInterface
	 */
	protected $dapImportLogger;
	
	/**
	 * @var array
	 */
	public $importSettings;
	
	public function __construct(Container $container, LoggerInterface $dapImportLogger = null) {
		$this->container = $container;
		$this->dapImportLogger = $dapImportLogger;
	}
	
	/**
	 * Sets import settings.
	 *
	 * @param array $importSettings the settings settings list
	 *
	 * set importSettings property
	 */
	public function setCommandSettings(array $importSettings = null)
	{
		$this->importSettings = $importSettings;
	}
	
	/*
	
	protected function configure()
	{
		// ...
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// ...
	}
	*/
	
}