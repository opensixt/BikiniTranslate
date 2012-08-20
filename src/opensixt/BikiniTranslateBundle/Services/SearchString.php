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
class SearchString extends HandleText {

    const SEARCH_EXACT = 1;
    const SEARCH_LIKE  = 2;

    /** @var string */
    protected $_searchString;

    /** @var string */
    protected $searchPhrase;

    /** @var int */
    protected $searchMode;

    public function __construct($doctrine)
    {
        $this->_paginationLimit = 15;
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
        $searchPhrase = urldecode($searchPhrase);
        $this->searchPhrase = $searchPhrase;
        $this->searchMode = $searchMode;
        if ($searchMode == self::SEARCH_LIKE) {
            $searchPhrase = preg_replace('/\s+/', ' ', $searchPhrase);
            $searchPhrase = str_replace(' ', '%', $searchPhrase);
        }
        $searchPhrase = '%' . $searchPhrase . '%';
        //TODO: sanitize input, fulltext search (MATCH...AGAINST....)
        $this->_searchString = $searchPhrase;
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
        if (!$this->_searchString) {
            throw new \Exception(__METHOD__ . ': _searchString is not set. Please set it with ' . __CLASS__ . '::setSearchParameters() !');
        }

        $this->_textRepository->setSearchString($this->_searchString);

        $this->_textRepository->init(
            TextRepository::TASK_SEARCH_PHRASE_BY_LANG,
            $locale,
            $resources);

        $query = $this->_textRepository->getSearchResults();

        if (empty($this->_paginationLimit)) {
            $this->_paginationLimit = PHP_INT_MAX;
        }

        $data = $this->paginator->paginate($query, $page, $this->_paginationLimit);

        // set GET parameter for paginator links
        if (count($resources) == 1) {
            $data->setParam('resource', $resources[0]);
        }
        $data->setParam('search', $this->searchPhrase);
        $data->setParam('mode', $this->searchMode);
        $data->setParam('locale', $locale);

        return $data;
    }

}