<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private Client $client;
    private EntityManagerInterface $em;

    /**
     * IndexController constructor.
     */
    public function __construct(KernelInterface $kernel, EntityManagerInterface $em)
    {
        $this->client = ClientBuilder::create()->build();
        $this->em = $em;
    }

    /**
     * @Route(name="index")
     */
    public function index()
    {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', '2000');
        //create an index
//        $this->createAnIndex();
        //index document
        $this->indexDocuments();

        return $this->render('index/index.html.twig', [
            'controller_name' => 'AutoCompleteController',
        ]);
    }

    /**
     * @Route("/search", name="auto_complete_search", methods="GET")
     */
    public function search( Request $request)
    {
        $query = $request->query->get('q');

        //sql test
//        $qb = $this->em->createQueryBuilder()
//            ->select('a.name')
//            ->from(Article::class, 'a')
//            ->where('a.description like :query')
//            ->setParameter('query', '%'.$query.'%')
//            ->getQuery()
//            ->getResult();
//
//        return $this->json($qb);
//        dd($qb);

        $res = [];
        $params = [
            'index' => 'symfony-elastica',
            'body' => [
                'query' => [
                    'match' => [
                        'name' => $query,
                    ],
                ],
            ]
        ];

        $result = $this->client->search($params);

        if (isset($result['hits']['hits'])) {
            $res['hits'] = $result['hits']['hits'];
        }

        return $this->json([
            'res' => $res,
        ]);
    }


    // create new index
    private function createAnIndex()
    {
        $params = [
            'index' => 'symfony-elastica',
            'body'  => [
                'settings' => [
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0
                ]
            ]
        ];

        $response = $this->client->indices()->create($params);
        dd($response);
    }

    //create document
    private function indexDocuments()
    {
        $articles = $this->em->getRepository(Article::class)->findAll();

        /** @var Article $article */
        foreach ($articles as $article) {
            // index a document
            $params = [
                'index' => 'symfony-elastica',
                'id'    => $article->getId(),
                'body'  => [
                    'type' => $article->getType(),
                    'name' => $article->getName(),
                    'slug' => $article->getSlug(),
                    'keyword' => $article->getKeyword(),
                ]
            ];

            $response = $this->client->index($params);
            dump($response);
        }
    }
}