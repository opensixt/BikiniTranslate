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
     * @var string
     */
    protected $_commonLanguage;

    /**
     * @var int
     */
    protected $_commonLanguageId;

    /**
     * @var string
     */
    public $revisionControlMode;

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
     * @param int $page
     */
    public function setPaginationPage($page)
    {
        $this->_paginationPage = $page;
    }

    /**
     *
     * @param int $page
     */
    public function setPaginationLimit($lim)
    {
        if ($lim >= 0) {
            $this->_paginationLimit = $lim;
        }
    }

}
