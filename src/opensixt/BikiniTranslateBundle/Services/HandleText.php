<?php

namespace opensixt\BikiniTranslateBundle\Services;

use opensixt\BikiniTranslateBundle\Repository\TextRepository;

/**
 * HandleText
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */

class HandleText {

    const ENTITY_TEXT_NAME  = 'opensixt\BikiniTranslateBundle\Entity\Text';

    /**
     * @var repository
     */
    protected $_textRepository;

    /**
     * @var EntityManager
     */
    protected $_em;

    /**
     * @var int
     */
    protected $_locale;

    /**
     * @var array
     */
    protected $_locales;

    /**
     * @var array
     */
    protected $_resources;

    /**
     * @var string
     */
    private $_commonLanguage;

    /**
     * @var int
     */
    private $_commonLanguageId;

    private $_revisionControlMode;

    /**
     * @var int
     */
    protected $_paginationLimit;

    /**
     * @var int
     */
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
        $this->_textRepository = $this->_em->getRepository(self::ENTITY_TEXT_NAME);
    }

    /**
     * Sets resources
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param array $resources
     */
    public function setResources(array $resources)
    {
        $this->_resources = $resources;
    }

    /**
     * Sets locale
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param int $locale
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    /**
     * Sets locales
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param array $locales
     */
    public function setLocales($locales)
    {
        $this->_locales = $locales;
    }

    /**
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param int $page
     */
    public function setPaginationPage($page)
    {
        $this->_paginationPage = $page;
    }

}
