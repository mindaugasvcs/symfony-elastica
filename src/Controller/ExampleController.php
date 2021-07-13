<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Elastica\Util;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController extends AbstractController
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     *
     * @param TransformedFinder $articlesFinder
     * @return Response
     */
    public function listAction(TransformedFinder $articlesFinder) : Response
    {
        $search = Util::escapeTerm("Bleu de channel parfum");
        $result = $articlesFinder->findHybrid($search, 10);

        dd($result);

        return $this->render("views/empty.html.twig");
    }
}
