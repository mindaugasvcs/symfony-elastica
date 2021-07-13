<?php

declare(strict_types=1);

// src/Controller/SearchPart2Controller.php

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
 * @Route("/{_locale}", name="search_part2_", requirements={"_locale"="%locales_requirements%"})
 */
final class SearchPart2Controller extends AbstractController
{
    /**
     * I made a PR to have the findHybridPaginated() function in the bundle.
     *
     * @Route({"en": "/part2/search", "fr": "/partie2/recherche"}, name="main")
     *
     * @see https://github.com/FriendsOfSymfony/FOSElasticaBundle/pull/1567/files
     */
    public function search(Request $request, SessionInterface $session, TransformedFinder $articlesFinder): Response
    {
        $q = u((string) $request->query->get('q', ''))->trim()->toString();
        $pagination = $articlesFinder->findHybridPaginated(Util::escapeTerm($q));
        $pagination->setCurrentPage($request->query->getInt('page', 1));
        $session->set('q', $q);

        return $this->render('search/search.html.twig', compact('pagination', 'q'));
    }
}