<?php

declare(strict_types=1);

// src/Controller/SuggestController.php

namespace App\Controller;

use App\Elasticsearch\ElastiCoil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale}", requirements={"_locale"="%locales_requirements%"})
 */
final class SuggestController extends AbstractController
{
    /**
     * @Route({"en": "/suggest", "fr": "/suggerer"}, name="suggest")
     */
    public function suggest(Request $request, string $_locale, ElastiCoil $elastiCoil): JsonResponse
    {
        $q = (string) $request->query->get('q', '');

        return $this->json($elastiCoil->getSuggestions($q, $_locale));
    }
}