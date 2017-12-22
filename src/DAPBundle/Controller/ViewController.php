<?php
/**
 * File containing the ViewController class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPBundle\Controller;

use DAPBundle\ElasticDocs\DAPDatePublished;
use DAPBundle\ElasticDocs\DAPRecord;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ViewController extends Controller
{
    /**
     * Renders graphiql client.
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function graphiqlAction()
    {
        return $this->render('DAPBundle::graphiql.html.twig');
    }

    /**
     * Renders DAP dashboard.
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dashboardAction()
    {
        return $this->render('DAPBundle::dashboard.html.twig');
    }
    /**
     * Puts items from Postgres into Elasticsearch.
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function buildElasticAction(Request $request)
    {
        $logger = $this->get('logger');

        $fakeSomeData = !is_null($request->query->get('fakedata'));


        //build elastic index
        //shell_exec("bin/console ongr:es:index:create");
        // does not seem to be the same as running it from the command line

        //talk to doctrine
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Record');
        $allrecords = $repo->findAll();

        $outvar = "<p>Found ".count($allrecords)." records</p>";
        $recordsProcessed = array();
        $recordsFailed = array();

        //At some point, will need to make this smart about how many records it tries to do at a time

        //get current place in work
        if (null !== ($request->query->get('start')) && is_numeric($request->query->get('start'))) {
            $startAt = (int) $request->query->get('start');
        } else {
            $startAt = 0;
        }
        //set cut-over place in work
        $stopAt = $startAt + 1000;

        //check to make sure we aren't running headless (e.g. try to do it all at once)
        if ((null !== ($request->query->get('headless'))) &&
            ($request->query->get('headless') == '1' || $request->query->get('headless') == 'true')) {
            $startAt = 0;
            $stopAt = count($allrecords);
        }


        for ($i=$startAt; $i< min($stopAt, count($allrecords)); $i++) {
            //get a record from doctrine
            $tempvar = $allrecords[$i];


            //build our elasticsearch object
            $elasticRecord = new DAPRecord();
            //get the (meta)data
            $tresult = $elasticRecord->fill($tempvar);
            if ($tresult == -1) {
                $tdapid = isset($tempvar->dapID) ? $tempvar->dapID : 'No DAP ID';
                $tname = isset($tempvar->metadata['name']) ? $tempvar->metadata['name'] : 'No Name';
                $logger->error('Failed to push dapid ' . $tdapid . ' ('.$tname.') to Elasticsearch.');
                array_push($recordsFailed, ($tdapid .'(' . $tname . ')'));
                continue;
            } elseif ($tresult == -2) {
                $logger->info('Skipped a Luna Record');
                array_push($recordsFailed, ('(Skipped a LUNA record.)'));
                continue;
            } else {
                $tdapid = isset($tempvar->dapID) ? $tempvar->dapID : 'No DAP ID';
                $tname = isset($tempvar->metadata['name']) ? $tempvar->metadata['name'] : 'No Name';
                $logger->info('Pushed ' . $tdapid . ' ('.$tname.') to Elasticsearch.');
                array_push($recordsProcessed, ($tdapid . ' ('.$tname.')'));
                //$outvar .= "<h3>Pushed to Elasticsearch: <br>".$tresult." (" . $tempvar->metadata['name'] . ")</h3>";
            }

            //save it to elasticsearch
            $esManager = $this->get('es.manager');
            $esRepo = $esManager->getRepository('DAPBundle:DAPRecord');
            $esManager->persist($elasticRecord);
            $esManager->commit();
        }
        //capture this b/c we reuse $i for counter
        $recordsSoFar = $i;

        if (count($recordsProcessed) > 0) {
            $outvar .= '<div><strong>We processed these items:</strong></div><ol>';
            for ($i = 0; $i < count($recordsProcessed); $i++) {
                $outvar .= '<li>' . $recordsProcessed[$i] . '</li>';
            }
            $outvar .= '</ol>';
        } else {
            $outvar .= '<p><strong>NO ITEMS PROCESSED</strong></p>';
        }

        if (count($recordsFailed) > 0) {
            $outvar .= '<div><strong>We could NOT process these items:</strong></div><ol>';
            for ($i = 0; $i < count($recordsFailed); $i++) {
                $outvar .= '<li>' . $recordsFailed[$i] . '</li>';
            }
            $outvar .= '</ol>';
        } else {
            $outvar .= '<p><strong>All records processed successfully</strong></p>';
        }
        $outvar .= "<p>Processed ".count($recordsProcessed)." records.</p>";

        if ($recordsSoFar < count($allrecords)) {
            $outvar = '<div class="alert alert-warning"><a href="/dap/buildelasticindex?start='.$recordsSoFar.'">'.(count($allrecords) - $recordsSoFar). ' Records left to process.</a></div>'.$outvar;
        } else {
            $outvar = '<div class="alert alert-success">Done! '.(count($allrecords) - $recordsSoFar). ' Records left to process.)</div>'.$outvar;

        }

        return $this->render('DAPBundle::buildElastic.html.twig', array("rawHTML" => $outvar));
    }

    /**
     * Builds long list of links to DAP resources for SEO.
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function buildSEOAction(Request $request)
    {
        $logger = $this->get('logger');


        //talk to doctrine
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Record');
        $allrecords = $repo->findAll();

        $debugOut = "<p>Found ".count($allrecords)." records</p>";
        $realOut = '';
        $processedCount = 0;

        if (getcwd() == '/home/vagrant/Code/dapdev/web') {
            $serverurl = 'http://dapclient.dev/';
        } else {
            $serverurl = 'http://search.dap.parsonstko.com/';
        }

        //At some point, will need to make this smart about how many records it tries to do at a time
        for ($i=0; $i<count($allrecords); $i++) {
            //get a record from doctrine
            $tempvar = $allrecords[$i];


            //build our elasticsearch object
            //we're using this to determine if the record should be linked
            $elasticRecord = new DAPRecord();
            //get the (meta)data
            $tresult = $elasticRecord->fill($tempvar);
            if ($tresult == -1) {
                $tdapid = isset($tempvar->dapID) ? $tempvar->dapID : 'No DAP ID';
                $tname = isset($tempvar->metadata['name']) ? $tempvar->metadata['name'] : 'No Name';
                $logger->error('Sitemap Creation: Failed to push dapid ' . $tdapid . ' ('.$tname.') to SEO.');
                continue;
            } elseif ($tresult == -2) {
                $logger->info('Sitemap Creation: Skipped a Luna Record');
                continue;
            } else {
                if (isset($tempvar->dapID) && isset($tempvar->metadata['name']) && isset($tempvar->updatedDate)) {
                    if (isset($tempvar->metadata['folgerGenre'])) {
                        if (is_array($tempvar->metadata['folgerGenre'])) {
                            try {
                                $firstParameter = urlencode($tempvar->metadata['folgerGenre'][0]['terms'[0]]);
                            } catch (\Exception $ex) {
                                $firstParameter = 'folger';
                            }
                        } else {
                            $firstParameter = urlencode($tempvar->metadata['folgerGenre']);
                        }
                    } else {
                        $firstParameter = 'folger';
                    }
                    $secondParameter = isset($tempvar->metadata['name']) ? urlencode($tempvar->metadata['name']) : 'folger';
                    $debugOut .= '<li>' . $tempvar->metadata['name'] . '('. $tempvar->dapID .')</li>';
                    $realOut .= '<url><loc>'.$serverurl.$firstParameter.'/'.$secondParameter.'/'. $tempvar->dapID .'</loc>';
                    $realOut .= '<lastmod>'. $tempvar->updatedDate->format('Y-m-d'). '</lastmod>';
                    $realOut .= '<changefreq>yearly</changefreq><priority>0.7</priority></url>';
                    $processedCount++;
                }
            }
        }

        $realOut = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.
            $realOut.'</urlset>';

        $myfile = file_put_contents('sitemap.xml', $realOut.PHP_EOL);

        if ($myfile  === false) {
            $userOut = 'Unable to write sitemap.xml';
        } else {
            $userOut = $processedCount . ' public of ' . count($allrecords) .
                ' total links written to <a href="/sitemap.xml">/sitemap.xml</a>.'.
                ' Make sure to run the script to move the file to the client interface.';
        }

        return $this->render('DAPBundle::buildElastic.html.twig', array("rawHTML" => $userOut));
    }

    /**
     * Lets us test elasticsearch
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchTestAction(Request $request)
    {
        $searchTerm = $request->query->get('searchterm');
        $filter = $request->query->get('filter');
        $filterValue = $request->query->get('filtervalue');
        $rangeField = $request->query->get('rangefield');
        $rangeMin = $request->query->get('rangemin');
        $rangeMax = $request->query->get('rangemax');
        $rangeDemote = $request->query->get('rangedemote'); //set to 1 to move the range filter into the collected query parts
        $facetName = $request->query->get('facetname');
        $facetField = $request->query->get('facetfield');
        $pageNumber = $request->query->get('pagenumber');
        $pageSize = $request->query->get('pagesize');

        $createdFrom = $request->query->get('createdfrom');
        $createdUntil = $request->query->get('createduntil');

        $outvar = '<p>Play with search features by using querystring variables matching the names below. 
            Any values will be displayed.';

        $outvar .= '<ul>';
        $outvar .= '<li><strong>Search</strong></li><ul>';
        $outvar .= '<li>searchterm: ' . $searchTerm .'</li>';
        $outvar .= '<li>filter: ' . $filter .'</li>';
        $outvar .= '<li>filtervalue: ' . $filterValue .'</li></ul>';

        $outvar .= '<li><strong>Search a Range</strong></li><ul>';

        $outvar .= '<li>rangefield: ' . $rangeField .'</li>';
        $outvar .= '<li>rangemin: ' . $rangeMin .'</li>';
        $outvar .= '<li>rangemax: ' . $rangeMax .'</li>';
        $outvar .= '<li>rangedemote: ' . $rangeDemote .'</li></ul>';

        $outvar .= '<li><strong>Range Seach - Created Date</strong></li><ul>';

        $outvar .= '<li>createdfrom: ' . $createdFrom .'</li>';
        $outvar .= '<li>createduntil: ' . $createdUntil .'</li></ul>';

        $outvar .= '<li><strong>Use a Facet</strong></li><ul>';

        $outvar .= '<li>facetname: ' . $facetName .'</li>';
        $outvar .= '<li>facetfield: ' . $facetField .'</li></ul>';



        $outvar .= '<li><strong>Paging</strong></li><ul>';
        $outvar .= '<li>pagenumber: ' . $pageNumber .'</li>';
        $outvar .= '<li>pagesize: ' . $pageSize .'</li></ul>';

        $outvar .= '</ul>';


        $elastic = $this->get('dap.resolver.elastic');


        if ($searchTerm) {
            $elastic->addFullTextSearch($searchTerm);
        }
        if ($filter && $filterValue) {
            $elastic->addFilter($filter, $filterValue);
        }
        if ($rangeField && ($rangeMin || $rangeMax)) {
            $elastic->addRangeFilter($rangeField, $rangeMin, $rangeMax, ($rangeDemote == 1));
        }

        if ($createdFrom || $createdUntil) {
            $elastic->addCreatedIn($createdFrom, $createdUntil);
        }

        //aggregations/facets
        if ($facetName) {
            $elastic->addAggregation($facetName, $facetField);
        }

        //let's add a range aggregation
        $rangeAggRanges = array();
        array_push($rangeAggRanges, array('key' => '<1700', 'to' => '1700' ));
        array_push($rangeAggRanges, array('key' => '1700-1800', 'from' => 1700, 'to' => 1800 ));
        array_push($rangeAggRanges, array('key' => '1800-1900', 'from' => 1800, 'to' => 1900 ));
        array_push($rangeAggRanges, array('key' => '1900-2000', 'from' => 1900, 'to' => 2000 ));
        array_push($rangeAggRanges, array('key' => '>2000', 'from' => 2000 ));
        $elastic->addRangeAggregation("Era", 'date_created', $rangeAggRanges);


        //page sizing
        if ($pageSize || $pageNumber) {
            if ($pageSize) {
                $elastic->setPageSize((int)$pageSize);
            }
            if ($pageNumber) {
                $elastic->setPage((int) $pageNumber);
            }
        } else {
            $outvar .= "<div> We're setting the page size to 2 for now so we can show 2 pages of results.</div>";
            $elastic->setPageSize(2);
        }

        $a = $elastic->getSearchJSON();
        $outvar .= "<h2>Search Query</h2> <pre>$a</pre>";

        $elastic->doSearch();


        //output facets
        if (count($elastic->facets) > 0) {
            $outvar .= "<h2>Facets</h2><ul>";
            foreach ($elastic->facets as $k => $v) {
                $outvar .= "<li><strong>$k</strong>";
                for ($j = 0; $j < count($v); $j++) {
                    $outvar .= "<ul>";
                    $outvar .= "<li>facet: " . $v[$j]->facet . "</li>";
                    $outvar .= "<li>key: " . $v[$j]->key . "</li>";
                    $outvar .= "<li>count: " . $v[$j]->count . "<hr></li>";
                    $outvar .= "</ul>";
                }
                $outvar .= "</li>";
            }
            $outvar .= "</ul>";
        }
        //end output facets


        $outvar .= "<h2>Search Results</h2><pre>".$this->debugOut($elastic->getDocuments())."</pre>";


        $elastic->getNextPage();

        $outvar .= "<h2>Search Results Next Page</h2><pre>".$this->debugOut($elastic->getDocuments())."</pre>";

        $outvar .= "<h2>Nitty Gritty</h2><pre>". $this->debugOut($elastic->getResults()) . "</pre>";


        return $this->render('DAPBundle::buildElastic.html.twig', array("rawHTML" => $outvar));
    }

    public function debugOut($invar)
    {
        ob_start();
        var_dump($invar);
        $tt = ob_get_clean();
        return $tt;
    }
}
