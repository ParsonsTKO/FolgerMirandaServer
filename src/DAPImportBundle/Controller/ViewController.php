<?php
/**
 * File containing the ViewController class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;

class ViewController extends Controller
{
    /**
     * Renders DAP Import dashboard.
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dashboardAction()
    {
        try {
            return $this->render(
                'DAPImportBundle::dashboard.html.twig',
                array(
                )
            );
        } catch (\Exception $e) {
            $this->get('dap_import.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Page could not be found. Error: '.$e->getMessage());
        }
    }

    /**
     * Downloads Backlogged LUNA images from import process
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloaderAction()
    {
        try {
            $importService = $this->get('dap_import.service.import');
            $fileDirectory = 'var/folger/storage/images/';
            $inputFileName = 'imageToDownload.log.txt';
            $outputFileName = 'unfinished-import-' . date('Ymd-his').'.txt';
            $inputQueueFile = $fileDirectory . $inputFileName;
            $placeHolderFile = $fileDirectory . $outputFileName;
            $numberPerBatch = 1;
            $toDoList = file($inputQueueFile);
            
            if (count($toDoList) == 0) {
                die('YAY! We are done!');
            }
            
            //get first X items into their own list
            $ourPartOfTheList = array_slice($toDoList, 0, $numberPerBatch);
            //save our work list
            file_put_contents($placeHolderFile, $ourPartOfTheList);
            
            //remove first X items from the original list
            $toDoList = array_slice($toDoList, $numberPerBatch);
            //resave the original list
            file_put_contents($inputQueueFile, $toDoList);
            
            //do our todolist from 1 to X
            try {
                for ($i = 0; $i < count($ourPartOfTheList); $i++) {
                    //expecting [imageid]\t[save_location]\t[remote URL]
                    $thisRecord = explode("\t", str_replace("\n", "", $ourPartOfTheList[$i]));

                    if (!is_dir($fileDirectory . $thisRecord[0])) {
                        mkdir($fileDirectory . $thisRecord[0]);
                    }

                    $newFilePath = $thisRecord[1];
                    $filePath = $thisRecord[2];
                    $fileBinary = file_get_contents($filePath);

                    if ($thisRecord[3] == "original") {
                        file_put_contents($newFilePath, $fileBinary);
                        $gohere = $fileDirectory . $thisRecord[0] . '/' . $thisRecord[0] . '_thumb.jpg';
                        if (!file_exists($gohere)) {
                            $importService->generateVariation($newFilePath, $gohere, 80, 80, 90, true);
                        }
                    } else {
                        $witdh = $thisRecord[4];
                        $height = $thisRecord[5];
                        $importService->generateVariation($filePath, $newFilePath, $witdh, $height);
                    }
                }

                //delete our placeholder file to show we're done
                unlink($placeHolderFile);
            } catch (\Exception $exception) {
                //mark our file as errored-out
                rename($placeHolderFile, str_replace('unfinished-', 'error-', $placeHolderFile));
            }
            $response = new Response();
            $response->setContent("<html><head><script>window.setTimeout(function(){location.href='/dapimport/downloader?".date('Ymd-his')."';}, 30)</script></head><body><a href='/dapimport/downloader?".date('Ymd-his')."'</body></html>");
            $response->setStatusCode(Response::HTTP_OK);
            $response->headers->set('Content-Type', 'text/html');
            return $response;
        } catch (\Exception $e) {
            $this->get('dap_import.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Luna Image Download Could not Complete. : Error: '.$e->getMessage()) ;
        }
    }

    /**
     * Downloads Backlogged LUNA images from import process
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function binaryDownloaderAction()
    {
        try {
            $importService = $this->get('dap_import.service.import');
            $fileDirectory = 'var/folger/storage/binary/';
            $inputFileName = 'imageToDownload.log.txt';
            $outputFileName = 'unfinished-import-' . date('Ymd-his').'.txt';
            $inputQueueFile = $fileDirectory . $inputFileName;
            $placeHolderFile = $fileDirectory . $outputFileName;
            $numberPerBatch = 1;
            $toDoList = file($inputQueueFile);

            if (count($toDoList) == 0) {
                die('YAY! We are done!');
            }

            //get first X items into their own list
            $ourPartOfTheList = array_slice($toDoList, 0, $numberPerBatch);
            //save our work list
            file_put_contents($placeHolderFile, $ourPartOfTheList);

            //remove first X items from the original list
            $toDoList = array_slice($toDoList, $numberPerBatch);
            //resave the original list
            file_put_contents($inputQueueFile, $toDoList);

            //do our todolist from 1 to X
            try {
                for ($i = 0; $i < count($ourPartOfTheList); $i++) {
                    //expecting [imageid]\t[save_location]\t[remote URL]
                    $thisRecord = explode("\t", str_replace("\n", "", $ourPartOfTheList[$i]));

                    if (!is_dir($fileDirectory . sha1($thisRecord[0]))) {
                        mkdir($fileDirectory . sha1($thisRecord[0]));
                    }

                    $newFilePath = $thisRecord[1];
                    $filePath = $thisRecord[2];
                    //this couldn't handle large files
                    //$fileBinary = file_get_contents($filePath);

                    if ($thisRecord[3] == "original") {
                        //THIS COULDN'T HANDLE LARGE FILES: file_put_contents($newFilePath, $fileBinary);

                        //trying w./ CURL
                        $curlOptions = array (
                            CURLOPT_FILE => is_resource($newFilePath) ? $newFilePath : fopen($newFilePath, 'w'),
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_URL => $filePath,
                            CURLOPT_FAILONERROR => true,
                        );
                        $curlResource = curl_init();
                        curl_setopt_array($curlResource, $curlOptions);
                        $diditwork = curl_exec($curlResource);
                        if ($diditwork === false) {
                            throw new Exception("Couldn't download the file");
                        }

                        /*
                         $gohere = $fileDirectory . $thisRecord[0] . '/' . $thisRecord[0] . '_thumb.jpg';
                        if (!file_exists($gohere)) {
                            $importService->generateVariation($newFilePath, $gohere, 80, 80, 90, true);
                        }
                        */
                    } else {
                        $width = $thisRecord[4];
                        $height = $thisRecord[5];
                        $importService->generateVariation($filePath, $newFilePath, $width, $height);
                    }
                }

                //delete our placeholder file to show we're done
                unlink($placeHolderFile);
            } catch (\Exception $exception) {
                //mark our file as errored-out
                rename($placeHolderFile, str_replace('unfinished-', 'error-', $placeHolderFile));
            }
            $response = new Response();
            $response->setContent("<html><head><script>window.setTimeout(function(){location.href='/dapimport/binary_downloader?".date('Ymd-his')."';}, 30)</script></head><body><a href='/dapimport/downloader?".date('Ymd-his')."'</body></html>");
            $response->setStatusCode(Response::HTTP_OK);
            $response->headers->set('Content-Type', 'text/html');
            return $response;
        } catch (\Exception $e) {
            $this->get('dap_import.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Luna Image Download Could not Complete. : Error: '.$e->getMessage()) ;
        }
    }
    /**
     * Renders DAP Import from voyager json.
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function voyagerAction(Request $request)
    {
        try {
            $contentId = 'voyager_record';
            $importService = $this->get('dap_import.service.import');
            $schemasService = $this->get('dap_import.service.schemas');
            $schemasSettings = $this->getParameter('dap_import.schemas');
            $data = array();
            $result = array();
            $schemaList = array_merge(
                array('Select' => ''),
                $schemasService->getSchemaList()
            );

            $form = $this->createFormBuilder($data)
                ->add('file_text', TextareaType::class, array(
                        'attr' => array('rows' => '30', 'cols' => '60', 'class' => 'lined file-text'),
                        'required' => true,
                    )
                )
                ->add('schema_text', TextareaType::class, array(
                        'attr' => array('rows' => '30', 'cols' => '60', 'class' => 'lined schema-text'),
                        'required' => true,
                    )
                )
                ->add('schema_list', ChoiceType::class, array(
                        'label' => 'Select schema',
                        'attr' => array('class' => 'schema-list'),
                        'choices' => $schemaList,
                        'required' => false,
                    )
                )
                ->add('validate', SubmitType::class, array(
                        'label' => 'Validate Only',
                        'attr' => array('class' => 'btn btn-primary btn-lg'),
                        'validation_groups' => false,
                    )
                )
                ->add('validateAndImport', SubmitType::class, array(
                        'label' => 'Validate and Import',
                        'attr' => array('class' => 'btn btn-primary btn-lg'),
                    )
                )
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                
                if (array_key_exists('file_text', $data)) {
                    $file = $data['file_text'];
                }

                if (array_key_exists('schema_text', $data)) {
                    $schema = $data['schema_text'];
                }

                if ($form->get('validate')->isClicked()) {
                    $result = $importService->extract($contentId, $file, $schema);
                } elseif ($form->get('validateAndImport')->isClicked()) {
                    $result = $importService->extract($contentId, $file, $schema, false, true);
                }

                $importService->generateInfoLogger($result);
            }

            return $this->render(
                'DAPImportBundle::voyager.html.twig',
                array(
                    'schemas' => $schemasSettings['schemas'],
                    'form' => $form->createView(),
                    'result' => $result,
                )
            );
        } catch (\Exception $e) {
            $this->get('dap_import.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Page could not be found. Error: '.$e->getMessage());
        }
    }

    /**
     * Renders DAP Import from luna json.
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function lunaAction(Request $request)
    {
        try {
            $contentId = 'luna_record';
            $importService = $this->get('dap_import.service.import');
            $schemasService = $this->get('dap_import.service.schemas');
            $schemasSettings = $this->getParameter('dap_import.schemas');
            $data = array();
            $result = array();
            $schemaList = array_merge(
                array('Select' => ''),
                $schemasService->getSchemaList()
            );

            $form = $this->createFormBuilder($data)
                ->add('file_text', TextareaType::class, array(
                        'attr' => array('rows' => '30', 'cols' => '60', 'class' => 'lined file-text'),
                        'required' => true,
                    )
                )
                ->add('schema_text', TextareaType::class, array(
                        'attr' => array('rows' => '30', 'cols' => '60', 'class' => 'lined schema-text'),
                        'required' => true,
                    )
                )
                ->add('schema_list', ChoiceType::class, array(
                        'label' => 'Select schema',
                        'attr' => array('class' => 'schema-list'),
                        'choices' => $schemaList,
                        'required' => true,
                    )
                )
                ->add('validate', SubmitType::class, array(
                        'label' => 'Validate Only',
                        'attr' => array('class' => 'btn btn-primary btn-lg'),
                        'validation_groups' => false,
                    )
                )
                ->add('validateAndImport', SubmitType::class, array(
                        'label' => 'Validate and Import',
                        'attr' => array('class' => 'btn btn-primary btn-lg'),
                    )
                )
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                if (array_key_exists('file_text', $data)) {
                    $file = $data['file_text'];
                }

                if (array_key_exists('schema_text', $data)) {
                    $schema = $data['schema_text'];
                }

                if ($form->get('validate')->isClicked()) {
                    $result = $importService->extract($contentId, $file, $schema, true);
                } elseif ($form->get('validateAndImport')->isClicked()) {
                    $result = $importService->extract($contentId, $file, $schema, true, true);
                }

                $importService->generateInfoLogger($result);
            }

            return $this->render(
                'DAPImportBundle::luna.html.twig',
                array(
                    'schemas' => $schemasSettings['schemas'],
                    'form' => $form->createView(),
                    'result' => $result,
                )
            );
        } catch (\Exception $e) {
            $this->get('dap_import.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Page could not be found. Error: '.$e->getMessage());
        }
    }
}
