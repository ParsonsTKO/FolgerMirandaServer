<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImportMarcControllerTest extends WebTestCase
{
    public function testImport()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/import');
    }

}
