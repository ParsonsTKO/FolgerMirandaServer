<?php
/**
 * File containing the DefaultController class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:Default:index.html.twig',
            array(
            )
        );
    }
}
