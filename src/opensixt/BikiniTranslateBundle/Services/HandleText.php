<?php

namespace opensixt\BikiniTranslateBundle\Services;

use opensixt\BikiniTranslateBundle\Repository\TextRepository;

/**
 * HandleText
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */

class HandleText {

    const ENTITY_TEXT  = 'opensixt\BikiniTranslateBundle\Entity\Text';

    const SEARCH_EXACT = 1;
    const SEARCH_LIKE = 2;

    protected $_textRepository;

    protected $_em;

    protected $_locale;

    protected $_resources;

    protected $_paginationLimit;

    protected $_paginationPage;

    /**
     * Constructor
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param type $doctrine
     */
    public function __construct($doctrine)
    {
        $this->_em = $doctrine->getEntityManager();
        $this->_textRepository = $this->_em->getRepository(self::ENTITY_TEXT);
    }

    /**
     * Sets resources
     *
     * @param array $resources
     */
    public function setResources(array $resources)
    {
        $this->_resources = $resources;
    }

    /**
     * Sets locale
     *
     * @param int $locale
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    /*public function setPaginationLimit(int $limit)
    {
        $this->_paginationLimit = $limit;
    }*/

    public function setPaginationPage($page)
    {
        $this->_paginationPage = $page;
    }

}
