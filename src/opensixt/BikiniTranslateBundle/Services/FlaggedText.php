<?php
namespace opensixt\BikiniTranslateBundle\Services;

use opensixt\BikiniTranslateBundle\Services\HandleText;
use opensixt\BikiniTranslateBundle\Repository\TextRepository;

use opensixt\BikiniTranslateBundle\Helpers\Pagination;

/**
 * FlaggedText
 * Intermediate layer between Controller and Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class FlaggedText extends HandleText {

    public function __construct($doctrine)
    {
        $this->_paginationLimit = 15;
        parent::__construct($doctrine);
    }

    /**
     * Returns search results and pagination data
     * if $this->_date is set - get expired texts
     * else returns non released texts
     *
     * @param int $locale
     * @param array $resources
     * @param int $page
     * @param date $expiredDate
     * @return array
     */
    public function getData($locale, $resources, $page, $expiredDate = null)
    {
        if (!empty($expiredDate)) {
            $this->_textRepository->setDate($expiredDate);
        }
        if (count($this->_locales)) {
            $this->_textRepository->setLocales($this->_locales);
        }

        $data = array();

        // count of all results for the search parameters
        $textCount = $this->_textRepository->getTextCount(
            TextRepository::TASK_SEARCH_FLAGGED_TEXTS,
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