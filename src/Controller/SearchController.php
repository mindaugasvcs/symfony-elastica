<?php


namespace App\Controller;


use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/{slug}", name="blog_post")
     */
    public function getPost(): Response
    {
        exit('blogpost');
    }

    /**
     * @Route("/search", name="blog_search")
     */
    public function search(Request $request, PostRepository $posts): Response
    {
        $query = $request->get('query');
        $limit = $request->get('limit');
        $foundPosts = $posts->findBySearchQuery($query, $limit);

        $results = [];
        foreach ($foundPosts as $post) {
            $results[] = [
                'title' => htmlspecialchars($post->getTitle()),
                'date' => $post->getPublishedAt()->format('M d, Y'),
                'author' => htmlspecialchars($post->getAuthor()->getFullName()),
                'summary' => htmlspecialchars($post->getSummary()),
                'url' => $this->generateUrl('blog_post', ['slug' => $post->getSlug()]),
            ];
        }

        return $this->json($results);
    }
}