<?php
/**
 * File containing the ContentTypeController class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ContentTypeController extends Controller
{
    /**
     * Get.
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($identifier)
    {
        try {
            $schemasService = $this->get('dap_import.service.schemas');
            $schema = $schemasService->get($identifier);

            $response = new JsonResponse();
            $response->headers->set('X-Location-Id', $identifier);
            $response->setSharedMaxAge(10800);
            $response->setMaxAge(3600);
            $response->setData(json_decode($schema));
            $response->setCharset('UTF-8');

            return $response;
        } catch (\Exception $e) {
            $this->get('dap_import.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Page could not be found. Error: '.$e->getMessage());
        }
    }
    /**
     * Show.
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction()
    {
        try {
            $schemasService = $this->get('dap_import.service.schemas');
            $schemasSettings = $this->getParameter('dap_import.schemas');
            $result = array();
            $schemaList = array_merge(
                array('Select' => ''),
                $schemasService->getSchemaList()
            );

            return $this->render(
                'DAPImportBundle::schemalist.html.twig',
                array(
                    'schemas' => $schemasSettings['schemas'],
                )
            );
        } catch (\Exception $e) {
            $this->get('dap_import.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Page could not be found. Error: '.$e->getMessage());
        }
    }
}
