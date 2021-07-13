<?php

declare(strict_types=1);

// src/Controller/SearchPart1Controller.php

namespace App\Controller;

use Elastica\Util;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

/**
 * You know, for search.
 *
 * @Route("/{_locale}", name="search_part1_", requirements={"_locale"="%locales_requirements%"})
 */
final class SearchPart1Controller extends AbstractController
{
    /**
     * @Route({"en": "/part1/search", "fr": "/partie1/recherche"}, name="main")
     */
    public function search(Request $request, SessionInterface $session, TransformedFinder $articlesFinder): Response
    {
        $q = u((string) $request->query->get('q', ''))->trim();
        $results = !$q->isEmpty() ? $articlesFinder->findHybrid(Util::escapeTerm($q)) : [];
        $session->set('q', $q);

        return $this->render('search/search_part1.html.twig', compact('results', 'q'));
    }
}