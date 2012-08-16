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
     * @param int $locale
     * @param array $resources
     * @param int $page
     * @return array
     * @throws \Exception
     */
    public function getData($locale, $resources, $page)
    {
        // Exceptions
        if (!$this->_searchString) {
            throw new \Exception(__METHOD__ . ': _searchString is not set. Please set it with ' . __CLASS__ . '::setSearchParameters() !');
        }

        $data = array();

        $this->_textRepository->setSearchString($this->_searchString);

        // count of all results for the search parameters
        $textCount = $this->_textRepository->getTextCount(
            TextRepository::TASK_SEARCH_PHRASE_BY_LANG,
            $locale,
            $resources);

        // get pagination bar
        $pagination = new Pagination(
            $textCount,
            $this->_paginationLimit,
            $page);
        $data['paginationBar'] = $pagination->getPaginationBar();

        // get search results
        $data['searchResults'] = $this->_textRepository->getSearchResults(
            $this->_paginationLimit,
            $pagination->getOffset());

        return $data;
    }

}