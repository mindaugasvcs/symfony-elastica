<?php

declare(strict_types=1);

// src/Command/PopulateSuggestCommand.php (used by templates/blog/posts/_51.html.twig)

namespace App\Command;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\NoopWordInflector;
use Elastica\Document;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Symfony\Component\String\u;

/**
 * Populate the suggest elasticsearch index.
 */
final class PopulateSuggestCommand extends BaseCommand
{
    public const CMD = 'populate';
    public const DESC = 'Populate the "suggest" Elasticsearch index';

    protected static $defaultName = self::NAMESPACE.':'.self::CMD;

    private TransformedFinder $articlesFinder;
    private Index $suggestIndex;
    private Inflector $inflector;

    public function __construct(TransformedFinder $articlesFinder, Index $suggestIndex)
    {
        parent::__construct();
        $this->articlesFinder = $articlesFinder;
        $this->suggestIndex = $suggestIndex;
        $this->inflector = new Inflector(new NoopWordInflector(), new NoopWordInflector());
    }

    protected function configure(): void
    {
        [$desc, $class] = [self::DESC, self::class];
        $this->setDescription(self::DESC)
            ->setHelp(
                <<<EOT
$desc

COMMAND:
<comment>$class</comment>

<info>%command.full_name%</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(self::DESC);
        $pagination = $this->findHybridPaginated($this->articlesFinder, '');
        $nbPages = $pagination->getNbPages();
        $keywords = [];

        foreach (range(1, $nbPages) as $page) {
            $pagination->setCurrentPage($page);
            foreach ($pagination->getCurrentPageResults() as $result) {
                if ($result instanceof HybridResult) {
                    foreach ($result->getResult()->getSource() as $property => $text) {
                        if ($property === 'type') {
                            continue;
                        }
                        $locale = explode('_', $this->inflector->tableize($property))[1] ?? 'en';
                        $text = strip_tags($text ?? '');
                        $words = str_word_count($text, 2, 'çéâêîïôûàèùœÇÉÂÊÎÏÔÛÀÈÙŒ'); // FGS dot not remove french accents! 🙃
                        $textArray = array_filter($words);
                        $keywords[$locale] = array_merge($keywords[$locale] ?? [], $textArray);
                    }
                }
            }
        }

        // Index by locale
        foreach ($keywords as $locale => $localeKeywords) {
            // Remove small words and remaining craps (emojis) 😖
            $localeKeywords = array_unique(array_map('mb_strtolower', $localeKeywords));
            $localeKeywords = array_filter($localeKeywords, static function ($v): bool {
                return u($v)->trim()->length() > 2;
            });
            $documents = [];
            foreach ($localeKeywords as $idx => $keyword) {
                $documents[] = (new Document())
                    ->setType('keyword')
                    ->set('locale', $locale)
                    ->set('suggest', $keyword);
            }
            $responseSet = $this->suggestIndex->addDocuments($documents);

            $output->writeln(sprintf(' -> TODO: %d -> DONE: <info>%d</info>, "%s" keywords indexed.', count($documents), $responseSet->count(), $locale));
        }

        return 0;
    }

    /**
     * @return Pagerfanta<mixed>
     */
    private function findHybridPaginated(TransformedFinder $articlesFinder, string $query): Pagerfanta
    {
        $paginatorAdapter = $articlesFinder->createHybridPaginatorAdapter($query);

        return new Pagerfanta(new FantaPaginatorAdapter($paginatorAdapter));
    }
}