<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use AppBundle\Entity\Record;
use AppBundle\Entity\RemoteSystem;
use AppBundle\Entity\CreativeWork;
use AppBundle\Entity\Thing;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class ImportMarcController extends Controller
{
    /**
     * @Route("/getjson")
     */
    public function GetJsonAction()
    {
      $sJson = file_get_contents('./imports/goc.json');
      $oJson = json_decode($sJson, true); // decode the JSON into an associative array
      $sResponse =  '<pre>' . print_r($oJson[0], true) . '</pre>';
      return new Response('<html><body>' . $sResponse . '</body></html>');
    }
    /**
     * @Route("/importstuff")
     */
    public function ImportAction()
    {
        
      $journals = new \File_MARC('./imports/gameofchess2.mrc');

      $sOutput = "<h1>Import Specific Record</h1><h2>searching for: game at chess</h2>";
      while ($record = $journals->next()) {
          // Pretty print each record
          $oTitle = $record->getField('245');
          $aTitle = $oTitle->getSubfields('a');
          $sOutput .= "<p>Testing title: " . $aTitle[0]->getData() . " -- ";
          if(stripos($aTitle[0]->getData(),"game at chess") !== false){
            $oRecord = $record;
            $sOutput .= "</p><h1>Found Match!</h1>";
            break;
          } 
          //$sOutput .= $record . "<br />\n";
      }

      if(isset($oRecord)){
        $sOutput .= "<h1>MARC Record</H1><H2>Human Readable</h2>";
        //$oRecord = $journals->next();
        $sOutput .= $oRecord;
        $sOutput .= "<H2>PHP Programming Object</H2>";
        $sOutput .= "<h3>PHP Object: extract MARC 245 field (a,c)</h3>";
        $oTitle = $oRecord->getField('245');
        $aTitle = $oTitle->getSubfields('a');
        $aSubTitle = $oTitle->getSubfields('c');
        if (count($aSubTitle) == 1) {
          $sOutput .= "<h4>Title: " . $aTitle[0]->getData() . " " . $aSubTitle[0]->getData() . "</h4>";
        } else {
          $sOutput .= "<h4>Title: " . $aTitle[0]->getData() . "</h4>";
        }
        
        $sOutput .= print_r($oTitle, true);
        $sOutput .= "<h3>Complete Object</h3>";
        $sOutput .= print_r($oRecord,true);
      }
      
      
      $oGameAtChess = new CreativeWork;
      $oGameAtChess->setName($aTitle[0]->getData());
      $oGameAtChess->setIdentifier($oRecord->getField('852')->getSubfields('h')[0]->getData());
      $oGameAtChess->setDescription($oRecord->getField('520')->getSubfields('a')[0]->getData());
      /*
      $aAbout = $oRecord->getFields('500');
      $sAbout = "";
      foreach($aAbout as $aEntry) {
        $sAbout .= $aEntry->getSubFields('a')[0]->getData() . "\n";
      }
      $oAbout = new Thing;
      $oAbout->setName()
      $oGameAtChess->setAbout($sAbout);
      */
      $oGameAtChess->setAuthor($oRecord->getField('100')->getSubfields('a')[0]->getData());
      /*
      $aCitation = $oRecord->getFields('581');
      $sCitation = "";
      foreach($aCitation as $aEntry) {
        $sCitation .= $aEntry->getSubFields('a')[0]->getData() . "\n";
      }
      $oGameAtChess->setCitation($sCitation);
      */
      $date = new \DateTime();
      $date->setTimestamp(strtotime($oRecord->getField('245')->getSubfields('f')[0]->getData()));
      $oGameAtChess->setDatePublished($date);
      $oGameAtChess->setPublisher($oRecord->getField('245')->getSubfields('c')[0]->getData());
      $aGenre = $oRecord->getFields('655');
      $sGenre = "";
      foreach($aGenre as $aEntry) {
        $sGenre .= $aEntry->getSubFields('a')[0]->getData() . "\n";
      }
      $oGameAtChess->setGenre($sGenre);
      /*
      $aMentions = $oRecord->getFields('655');
      $sMentions = "";
      foreach($aMentions as $aEntry) {
        $sMentions .= $aEntry->getSubFields('a')[0]->getData() . "\n";
      }
      $oGameAtChess->SetMentions($sMentions);
      */
      $oGameAtChess->setMaterial($oRecord->getField('300')->getSubfields('a')[0]->getData() . "\n" . $oRecord->getField('300')->getSubfields('c')[0]->getData());

      $encoders = array(new JsonEncoder());
      $normalizers = array(new ObjectNormalizer());
      $serializer = new Serializer($normalizers, $encoders);

      $oJson = json_decode(nl2br($serializer->serialize($oGameAtChess, 'json')));
      $sPretty = json_encode($oJson, JSON_PRETTY_PRINT);
      $sOutput .= "<h1>Persisting schema based JSON record into system</h1><h2>JSON Record based off schema</h2><pre>" . $sPretty . "</pre>";

      $sMetaData = $serializer->serialize($oGameAtChess, 'json');

      $myRemoteSystem = new RemoteSystem();
      $tempnow = new \DateTime("now");
      $myRemoteSystem->setCreatedDate($tempnow);
      $myRemoteSystem->setUpdatedDate($tempnow);
      $myRemoteSystem->setLabel("Voyager Hamnet");
      $myRemoteSystem->setDescription("Folger System of Record");
      $myRemoteSystem->setUri("http://hamnet.folger.edu/cgi-bin/Pwebrecon.cgi?DB=local&PAGE=First");
      $myRemoteSystem->setDapID(Uuid::uuid4()->toString());
      

      $myRecord = new Record();
      $myRecord->setCreatedDate($tempnow);
      $myRecord->setDapID(Uuid::uuid4()->toString());
      $myRecord->setRecordType(1);
      $myRecord->setUpdatedDate($tempnow);
      $myRecord->setMetadata($sMetaData);
      $myRecord->setRemoteSystem($myRemoteSystem->getDapID());
      $myRecord->setRemoteID("http://hamnet.folger.edu/cgi-bin/Pwebrecon.cgi?v1=2&ti=1,2&Search_Arg=128835&Search_Code=FT%2A&CNT=50&PID=7t-1wrTOwfnU5GT4ArtV4cHro&SEQ=20170404012053&SID=");

      //$entityManager = $this->get('doctrine')->getManagerForClass(\AppBundle\Entity\Record::class);

      
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($myRecord);
      $entityManager->persist($myRemoteSystem);
      $entityManager->flush();
      
      $sOutput .= "<h1>Record Saved Successfully</h1>";

      // Iterate through the retrieved records
      /*
      while ($record = $journals->next()) {
          // Pretty print each record
          $sOutput .= $record . "<br />\n";
      }
      */

      return new Response('<html><body>Marc Import: <pre>'. nl2br($sOutput) .'</pre></body></html>');
      
    }

}
