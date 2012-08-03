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

    protected $_expiredDate;


    public function __construct($doctrine)
    {
        $this->_paginationLimit = 15;
        parent::__construct($doctrine);
    }

    /**
     * Set expired date
     *
     * @param date $date
     */
    public function setExpiredDate($date)
    {
        $this->_expiredDate = $date;
    }

    /**
     * Returns search results and pagination data
     * if $this->_date is set - get expired texts
     * else returns non released texts
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @return array
     */
    public function getData()
    {
        if (!empty($this->_expiredDate)) {
            $this->_textRepository->setDate($this->_expiredDate);
        }
        if (count($this->_locales)) {
            $this->_textRepository->setLocales($this->_locales);
        }

        $data = array();

        // count of all results for the search parameters
        $textCount = $this->_textRepository->getTextCount(
            TextRepository::TASK_SEARCH_FLAGGED_TEXTS,
            $this->_locale,
            $this->_resources);

        // get pagination bar
        $pagination = new Pagination(
            $textCount,
            $this->_paginationLimit,
            $this->_paginationPage);
        $data['paginationBar'] = $pagination->getPaginationBar();

        // get search results
        $data['searchResults'] = $this->_textRepository->getSearchResults(
            $this->_paginationLimit,
            $pagination->getOffset());

        return $data;
    }

}