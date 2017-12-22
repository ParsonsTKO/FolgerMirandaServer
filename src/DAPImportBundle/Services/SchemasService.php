<?php
/**
 * File containing the SchemasService class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPImportBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Psr\Log\LoggerInterface;

class SchemasService
{
    /**
     * @var Kernel rootDir
     */
    private $rootDir;

    /**
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
    public $schemasSettings;

    public function __construct(Container $container, LoggerInterface $dapImportLogger = null)
    {
        $this->container = $container;
        $this->dapImportLogger = $dapImportLogger;
        $this->rootDir = $this->container->get('kernel')->getRootDir();
    }

    /**
     * Sets schemas settings.
     *
     * @param array $schemasSettings the children settings list.
     *
     * set schemasSettings property
     */
    public function setSchemasSettings(array $schemasSettings = null)
    {
        $this->schemasSettings = $schemasSettings;
    }

    /**
     * Get schema.
     *
     * @param string $identifier.
     *
     * set schemasSettings property
     */
    public function get($identifier = 'default')
    {
        try {
            $schemas = $this->schemasSettings['schemas'];
            $path = $this->rootDir.$this->schemasSettings['path'];
            $schemaDefault = $this->schemasSettings['default'];
            $schema = '';
            $extension = '.json';

            if ($identifier) {
                foreach ($schemas as $schemaIdentifier => $schemaValue) {
                    if ($schemaIdentifier == $identifier) {
                        if (array_key_exists('base', $schemaValue)) {
                            $schema = $schemaValue['base'];
                        } else {
                            $schema = $schemaDefault;
                        }
                    } elseif (is_array($schemaValue)) {
                        if (array_key_exists($identifier, $schemaValue)) {
                            if ($schemaValue[$identifier]) {
                                $schema = $schemaValue[$identifier];
                            } elseif (array_key_exists('base', $schemaValue)) {
                                $schema = $schemaValue['base'];
                            } else {
                                $schema = $schemaDefault;
                            }
                        }
                    } else {
                        $schema = $schemaDefault;
                    }
                }

                $file = $path.$schema.$extension;

                if (!file_exists($file)) {
                    $file = $path.$schemaDefault.$extension;
                }
            } else {
                $file = $path.$schemaDefault.$extension;
            }

            return file_get_contents($file);
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /**
     * Validate schema file exists.
     *
     * @param string $identifier.
     *
     * set schemasSettings property
     */
    public function schemaExists($file)
    {
        try {
            if (file_exists($file)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /**
     * Get schemas.
     *
     * @param string $identifier.
     *
     * set schemasSettings property
     */
    public function getSchemaList()
    {
        try {
            $schemas = $this->schemasSettings['schemas'];
            $schemaDefault = $this->schemasSettings['default'];
            $schemasList = array();

            foreach ($schemas as $schemaIdentifier => $schemaValue) {
                if (!empty($schemaValue)) {
                    foreach ($schemaValue as $schemaItemIdentifier => $schemaItemValue) {
                        if ($schemaItemIdentifier == 'base') {
                            $schemasList[$schemaItemValue] = $schemaIdentifier;
                        } else {
                            if ($schemaItemValue) {
                                $schemasList[$schemaItemValue] = $schemaItemIdentifier;
                            }
                        }
                    }
                }
            }

            return $schemasList;
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }
}
