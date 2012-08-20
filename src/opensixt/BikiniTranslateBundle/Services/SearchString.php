<?php
namespace opensixt\BikiniTranslateBundle\Services;

use opensixt\BikiniTranslateBundle\Services\HandleText;
use opensixt\BikiniTranslateBundle\Repository\TextRepository;

use opensixt\BikiniTranslateBundle\Helpers\Pagination;

/**
 * SearchSearch
 * Intermediate layer between Controller and Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class SearchString extends HandleText
{
    const SEARCH_EXACT = 1;
    const SEARCH_LIKE  = 2;

    /** @var string */
    protected $searchString;

    public function __construct($doctrine)
    {
        $this->paginationLimit = 15;
        parent::__construct($doctrine);
    }

    /**
     * Set search phrase
     *
     * @param string $searchPhrase
     * @param int $mode
     */
    public function setSearchParameters($searchPhrase, $searchMode = self::SEARCH_EXACT)
    {
        if ($searchMode == self::SEARCH_LIKE) {
            $searchPhrase = preg_replace('/\s+/', ' ', $searchPhrase);
            $searchPhrase = str_replace(' ', '%', $searchPhrase);
        }
        $searchPhrase = '%' . $searchPhrase . '%';
        //TODO: sanitize input, fulltext search (MATCH...AGAINST....)
        $this->searchString = $searchPhrase;
    }

    /**
     * Returns search results and pagination data
     *
     * @param int $page
     * @param int $locale
     * @param array $resources
     * @return Knp\Component\Pager\Pagination\PaginationInterface
     * @throws \Exception
     */
    public function getData($page, $locale, $resources)
    {
        // Exceptions
        if (!$this->searchString) {
            throw new \Exception(
                __METHOD__ . ': searchString is not set. ' .
                'Please set it with ' . __CLASS__ . '::setSearchParameters() !'
            );
        }

        $this->textRepository->setSearchString($this->searchString);

        $this->textRepository->init(
            TextRepository::TASK_SEARCH_PHRASE_BY_LANG,
            $locale,
            $resources
        );

        $query = $this->textRepository->getSearchResults();

        if (empty($this->paginationLimit)) {
            $this->paginationLimit = PHP_INT_MAX;
        }

        return $this->paginator->paginate($query, $page, $this->paginationLimit);
    }
}

