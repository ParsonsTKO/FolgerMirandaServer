<?php

namespace AppBundle\Controller;

use AppBundle\Entity\fakeJSON;
use AppBundle\Entity\Record;
use AppBundle\Entity\RemoteSystem;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;

class DefaultController extends Controller
{
	/**
	 * @Route("/", name="homepage")
	 */
	public function indexAction(Request $request)
	{
		// replace this example code with whatever you need
		
		$tt = $this->createRecord($request); //hack to test writing to db
		return new Response($tt);
		
		
		return $this->render('default/index.html.twig', [
				'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
		]);
	}
	
	//hack to test writing to db
	public function createRecord(Request $request)
	{
		
		$entityManager = $this->getDoctrine()->getManager();
		
		$tempnow = new \DateTime("now");
		$myMetaData = new fakeJSON();
		$myMetaData->a = 'a1';
		$myMetaData->b = 'b1';
		
		$myRemoteSystem = new RemoteSystem();
		$myRemoteSystem->setCreatedDate($tempnow);
		$myRemoteSystem->setUpdatedDate($tempnow);
		$myRemoteSystem->setLabel("Voyager Hamnet");
		$myRemoteSystem->setDescription("Folger System of Record");
		$myRemoteSystem->setUri("http://hamnet.folger.edu/cgi-bin/Pwebrecon.cgi?DB=local&PAGE=First");
		$myRemoteSystem->setDapID(Uuid::uuid4()->toString());
		#$entityManager->persist($myRemoteSystem);
		#$entityManager->flush();
		
		$myRecord = new Record();
		$myRecord->setCreatedDate($tempnow);
		$myRecord->setDapID(Uuid::uuid4()->toString());
		$myRecord->setRecordType(1);
		$myRecord->setUpdatedDate($tempnow);
		$myRecord->setMetadata($myMetaData);
		$myRecord->setRemoteSystem($myRemoteSystem->getDapID());
		$myRecord->setRemoteID("03377cam a2200505 450");
		
		//$entityManager = $this->get('doctrine')->getManagerForClass(\AppBundle\Entity\Record::class);
		
		
		
		$entityManager->persist($myRecord);
		$entityManager->flush();
		
		return $this->render('DAPBundle::default.html.twig');
		#("Folger DAP Development Server");
	}
}
