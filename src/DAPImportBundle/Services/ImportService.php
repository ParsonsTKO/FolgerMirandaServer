<?php
/**
 * File containing the ImportService class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPImportBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use JsonSchema\Validator;
use Seld\JsonLint\JsonParser;
use Imagick;
use AppBundle\Entity\Record;
use Doctrine\ORM\Query\ResultSetMapping;

class ImportService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

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
    public $importSettings;

    public function __construct(EntityManagerInterface $em, Container $container, LoggerInterface $dapImportLogger = null)
    {
        $this->em = $em;
        $this->container = $container;
        $this->dapImportLogger = $dapImportLogger;
        $this->rootDir = $this->container->get('kernel')->getRootDir();
    }

    /**
     * Sets import settings.
     *
     * @param array $importSettings the children settings list.
     *
     * set importSettings property
     */
    public function setImportSettings(array $importSettings = null)
    {
        $this->importSettings = $importSettings;
    }

    /**
     * Extract data.
     *
     * @param
     *
     * Reads data from a specified file and extracts a desired subset of data
     */
    public function extract($contentId, $fileData, $schemaData, $includeImages = false, $transform = false)
    {
        try {
            $result = array();
            $contentSettings = $this->importSettings['content'];
            $validationJson = $this->validateJson($fileData, $schemaData);
            $result['import_result'] = $validationJson;
            
            if ($validationJson['validation']['success']) {
                if (array_key_exists($contentId, $contentSettings)) {
                    $data = json_decode($fileData, true);
                    $jsonIdField = $contentSettings[$contentId]['json_id_field'];
    
                    if ($includeImages) {
                        $imagesJsonFields = $contentSettings[$contentId]['json_fields'];
                        $result['import_result_images'] = array();
                        
                        if ($data) {
                            foreach ($imagesJsonFields as $field) {
                                foreach ($data as $index => $value) {
                                    if (array_key_exists($field, $value)) {
                                        $result['import_result_images'][$index][$value[$jsonIdField]][$field] = $value[$field];
                                    }
                                }
                            }
                        }
                    } else {
                        //this is not a luna import, but checks if it's in a binary format and has a possible download
                        foreach ($data as $index => $value) {
                            switch ($value['format']) {
                                case 'video':
                                case 'sound':
                                case 'binary':
                                case 'csv':
                                    $includeImages = true;
                                    $result['import_result_images'][$index][$value[$jsonIdField]]['filename'] = $value['RemoteUniqueID'];
                                    break;
                                default:
                                    //no download
                                    break;
                            }
                        }
                    }
                }
            }

            if ($transform) {
                if ($validationJson['validation']['success']) {
                    $result['import_result_content'] = $this->transform($contentId, $fileData, $includeImages);
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /**
     * Transform json data.
     *
     * @param $data array
     *
     * Works with the acquired data - using rules to convert it to the desired state
     */
    public function transform($contentId, $fileData, $includeImages)
    {
        try {
            return $this->load($contentId, $fileData, $includeImages);
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /**
     * Load json data.
     *
     * @param
     *
     * Write the resulting data to a target database
     */
    public function load($contentId, $fileData, $includeImages)
    {
        try {
            $contentSettings = $this->importSettings['content'];
            $result = array();
            $itemValue = array();

            if (array_key_exists($contentId, $contentSettings)) {
                $data = json_decode($fileData, true);
                $jsonType = $contentSettings[$contentId]['json_type'];
                $jsonIdField = $contentSettings[$contentId]['json_id_field'];
                $jsonToContentType = $contentSettings[$contentId]['json_to_content_type'];
                $jsonToContentTypeId = $contentSettings[$contentId]['json_to_content_type_id'];
                $jsonToField = $contentSettings[$contentId]['json_to_field'];

                if ($includeImages) {
                    //to enable downloads of files for voyager-style records, make sure these fields are set
                    //this is done in the DAPImportBundle/Resources/config/parameters.yml file
                    $imagesJsonFields = $contentSettings[$contentId]['json_fields'];
                    $imagesPath = $contentSettings[$contentId]['images']['path'];
                    $imagesType = $contentSettings[$contentId]['images']['type'];
                    if ($imagesType != 'binary_files') {
                        $imagesNames = $contentSettings[$contentId]['images']['names'];
                        $imagesVariationsSource = $contentSettings[$contentId]['images']['variations_source'];
                        $imagesVariations = $contentSettings[$contentId]['images']['variations'];
                    }

                    foreach ($data as $index => $value) {
                        //also check to see if this is a type which we'd want to download
                        //this array should be moved to configuration
                        if (in_array($value['format'], array("sound", "video", "csv", "binary"))) {
                            if (is_dir($imagesPath)) {
                                if ($contentId == "voyager_record") {
                                    $filename = sha1($value[$jsonIdField]);
                                } else {
                                    $filename = $value[$jsonIdField];
                                }
                                if (!is_dir($imagesPath.'/'.$filename)) {
                                    mkdir($imagesPath.'/'.$filename);
                                }

                                foreach ($imagesJsonFields as $field) {
                                    if (array_key_exists($field, $value)) {
                                        if ($value[$field] != '') {
                                            try {
                                                $imageBackLogFile = $imagesPath.'/imageToDownload.log.txt';
                                                if ($contentId == 'voyager_record') {
                                                    $path_parts = pathinfo($value[$field]);
                                                    //should do storagelocation/sha1_of_url/actual_filename.ext
                                                    $image = $imagesPath.'/'.$filename.'/'.$path_parts['filename'].'.'.$path_parts['extension'];
                                                } else {
                                                    $image = $imagesPath.'/'.$filename.'/'.$filename.'_'.$imagesNames[$field].$imagesType;
                                                }
                                                $imageURL = $value[$field];
                                                $logline = $value[$jsonIdField]. "\t" . $image . "\t" . $imageURL . "\t" . "original" . "\n";
                                                file_put_contents($imageBackLogFile, $logline, FILE_APPEND);
                                                //this is now optimistic that the follow-on importer has completed its task
                                                if ($contentId == 'voyager_record') {
                                                    $data[$index]['file_location'] = $image;
                                                } else {
                                                    $data[$index][$field] = $image;
                                                }
                                                if ($imagesType != 'binary_files') { //images, not arbitrary binary files
                                                    // Generate image variations
                                                    if ($imagesVariationsSource == $field) {
                                                        if (!empty($imagesVariations)) {
                                                            foreach ($imagesVariations as $variation => $properties) {
                                                                $imageVariation = $imagesPath.'/'.$value[$jsonIdField].'/'.$value[$jsonIdField].'_'.$variation.$imagesType;
                                                                $variationLogline = $value[$jsonIdField]. "\t" . $imageVariation . "\t" . $image . "\t" . $variation . "\t" . $properties['witdh'] . "\t" . $properties['height'] . "\n";
                                                                file_put_contents($imageBackLogFile, $variationLogline, FILE_APPEND);
                                                                $data[$index]['mainImage'][$variation] = $imageVariation;
                                                                //$this->generateVariation($image, $imageVariation, $properties['witdh'], $properties['height']);
                                                            }
                                                        }
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                $data[$index][$field] = '';
                                                continue;
                                            }
                                        } else {
                                            $data[$index][$field] = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($jsonType == 'simple') { //this means it only accepts a single item per json file
                    $itemValue[$jsonToContentType] = $jsonToContentTypeId;
                    $itemValue[$jsonToField] = $data;
                    if (array_key_exists($jsonIdField, $data)) {
                        $record = $this->existsRecord($jsonIdField, $data[$jsonIdField]);

                        if (!empty($record)) {
                            $result[] = $this->updateRecord($record, $itemValue);
                        } else {
                            $result[] = $this->createRecord($itemValue);
                        }
                    }

                } elseif ($jsonType == 'multiple') {
                    foreach ($data as $index => $itemData) {
                        $itemValue[$jsonToContentType] = $jsonToContentTypeId;
                        $itemValue[$jsonToField] = $itemData;

                        if (array_key_exists($jsonIdField, $itemData)) {
                            $record = $this->existsRecord($jsonIdField, $itemData[$jsonIdField]);

                            if (!empty($record)) {
                                $result[] = $this->updateRecord($record, $itemValue);
                            } else {
                                $result[] = $this->createRecord($itemValue);
                            }
                        }
                    }
                }

                return $result;
            }
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /*
    /**
     * Get schema define from file
     *
     * @param $fileData
     *
     * General import proccess
     */
    public function getJsonSchemaFromFile($fileData)
    {
        try {
            $schemasSettings = $this->importSettings['schemas'];
            $schemasService = $this->container->get('dap_import.service.schemas');
            $data = json_decode($fileData);

            if ($data) {
                $contentTypeField = $schemasSettings['contentTypeField'];

                if (array_key_exists($contentTypeField, $data)) {
                    $contentType = $data->$contentTypeField;

                    if (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $contentType)) {
                        $path = parse_url($contentType)['path'];
                        $explotedPath = explode('/', $path);

                        return $schemasService->get($explotedPath[2]);
                    } else {
                        return $schemasService->get();
                    }
                } else {
                    return $schemasService->get();
                }
            } else {
                return $schemasService->get();
            }
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /*
     /**
     * Get schema by identifier
     *
     * @param $identifier
     *
     * General import proccess
     */
    public function getJsonSchemaById($identifier)
    {
        try {
            $schemasService = $this->container->get('dap_import.service.schemas');

            if ($identifier) {
                return $schemasService->get($identifier);
            }
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /*
     /**
     * Get schema by text
     *
     * @param $identifier
     *
     * General import proccess
     */
    public function getJsonSchemaByText($text)
    {
        try {
            if ($text != '') {
                return $text;
            }
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /**
     * Validate json data.
     *
     * @param $sourceData
     *
     * Validate source data
     */
    public function validateJson($fileData, $schemaData)
    {
        try {
            $data = json_decode($fileData);
            $schema = json_decode($schemaData);
            $message = array();
            $errors = array();
            
            $parser = new JsonParser();
            $jsonFileLintValidator = $parser->lint($fileData);
            $jsonSchemaLintValidator = $parser->lint($schemaData);
            
            if ($jsonFileLintValidator != null) {
                return array(
                    'validation' => array(
                        'success' => false,
                        'message' => 'Json file content does not validate. Violations:',
                        'errors' => [
                            $jsonFileLintValidator->getMessage(),
                        ],
                    ),
                );
            }
            
            if ($jsonSchemaLintValidator != null) {
                return array(
                    'validation' => array(
                        'success' => false,
                        'message' => 'Json schema content does not validate. Violations:',
                        'errors' => [
                            $jsonSchemaLintValidator->getMessage(),
                        ],
                    ),
                );
            }

            $validator = new Validator();
            $validator->validate($data, $schema);

            if ($validator->isValid()) {
                $message = array(
                    'validation' => array(
                        'success' => true,
                        'message' => 'The supplied json file content validates against the json schema content.',
                    ),
                );
            } else {
                foreach ($validator->getErrors() as $error) {
                    $errors[] = '['.$error['property'].'] '.$error['message'];
                }

                $message = array(
                    'validation' => array(
                        'success' => false,
                        'message' => 'Json does not validate. Violations:',
                        'errors' => $errors,
                    ),
                );
            }

            return $message;
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /**
     * Create record.
     *
     * @param
     *
     * Persisting record
     */
    public function createRecord($value)
    {
        try {
            $record = new Record();
            $currentDate = new \DateTime('now');

            $record->setCreatedDate($currentDate);
            $record->setDapID(Uuid::uuid4()->toString());
            $record->setCreatedDate($currentDate);
            $record->setUpdatedDate($currentDate);
            $record->setRemoteSystem(Uuid::uuid4()->toString());
            $record->setRemoteID('1');
            $record->setRecordType($value['recordType']);
            $record->setMetadata($value['metadata']);

            $this->em->persist($record);
            $this->em->flush();
            $this->em->clear();

            $record = $this->em->find(Record::class, $record->getId());

            return $record;
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /**
     * Update record.
     *
     * @param
     *
     * Updating persisting record
     */
    public function updateRecord($arguments, $value)
    {
        try {
            $record = $this->em->getRepository('AppBundle:Record')->findOneBy($arguments);
            $record->setRecordType($value['recordType']);
            $record->setMetadata($value['metadata']);
            $this->em->persist($record);
            $this->em->flush();

            $updatedRecord = $this->em->find(Record::class, $record->getId());
            
            return $updatedRecord;
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /**
     * Exists record.
     *
     * @param
     *
     * Validate if exists record
     */
    public function existsRecord($field, $value)
    {
        try {
            $record = array();
            $selectSQL = 'SELECT id, dapid';
            $fromSQL = 'FROM record';
            $whereSQL = "WHERE metadata->>'".$field."' = '".$value."'";
            $limitSQL = "LIMIT 1";
            $sql = "$selectSQL $fromSQL $whereSQL $limitSQL;";
            $rsm = new ResultSetMapping();
            $rsm->addEntityResult('AppBundle:Record', 'record');
            $rsm->addFieldResult('record', 'id', 'id');
            $rsm->addFieldResult('record', 'dapid', 'dapID');
            $query = $this->em->createNativeQuery($sql, $rsm);
            $result = $query->getResult();

            if ($result) {
                foreach (reset($result) as $item => $value) {
                    if ($item == "dapID") {
                        $record['dapID'] = $value;
                    }
                }
            }

            return $record;
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }

            throw new \Exception('Error: '.$e->getMessage());
        }
    }
    
    /**
     *
     * Generate Thumbnail using Imagick class
     *
     * @param string $img
     * @param string $width
     * @param string $height
     * @param int $quality
     * @return boolean on true
     */
    public function generateVariation($image, $imageVariation, $width, $height, $quality = 90, $justAthumbnail = false)
    {
        try {
            if (is_file($image)) {
                $imagick = new Imagick(realpath($image));
                $imagick->setImageFormat('jpeg');
                $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
                $imagick->setImageCompressionQuality($quality);
                if($justAthumbnail){ //so we want bestfit = true to avoid stretching
                    $imagick->thumbnailImage($width, $height, true, false);
                } else {
                    $imagick->thumbnailImage($width, $height, false, false);
                }
                
                if (file_put_contents($imageVariation, $imagick) === false) {
                    throw new \Exception("Could not put contents.");
                }
                
                return true;
            }
        } catch (\Exception $e) {
            if (isset($this->dapImportLogger)) {
                $this->dapImportLogger->error($e->getMessage());
            }
            
            throw new \Exception('Error: '.$e->getMessage());
        }
    }

    /**
     * Generate info logger.
     *
     * @param $message string
     *
     * Persisting record
     */
    public function generateInfoLogger($result)
    {
        $generatedMessage = array();

        if (array_key_exists('fileName', $result)) {
            $generatedMessage[] = '['.$result['fileName'].']';
        }

        if (array_key_exists('import_result', $result)) {
            if (array_key_exists('validation', $result['import_result'])) {
                $generatedMessage[] = $result['import_result']['validation']['message'];

                if (array_key_exists('errors', $result['import_result']['validation'])) {
                    if (is_array($result['import_result']['validation']['errors'])) {
                        $generatedMessage[] = implode(',', $result['import_result']['validation']['errors']);
                    }
                }
            }
        }

        if (!empty($generatedMessage)) {
            $this->dapImportLogger->info(implode(' ', $generatedMessage));
        }
    }
}
