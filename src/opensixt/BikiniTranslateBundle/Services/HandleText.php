<?php

namespace opensixt\BikiniTranslateBundle\Services;

use opensixt\BikiniTranslateBundle\Repository\TextRepository;

/**
 * HandleText
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */

abstract class HandleText
{
    const ENTITY_TEXT_NAME  = 'opensixt\BikiniTranslateBundle\Entity\Text';

    /** @var repository */
    protected $textRepository;

    /** @var EntityManager */
    protected $em;

    /** @var int */
    protected $locale;

    /** @var array */
    protected $locales;

    /** @var string */
    protected $commonLanguage;

    /** @var int */
    protected $commonLanguageId;

    /** @var int */
    protected $paginationLimit;

    /** @var string */
    public $revisionControlMode;

    /** @var \Knp\Component\Pager\Paginator */
    public $paginator;

    /**
     * Constructor
     *
     * @param type $doctrine
     */
    public function __construct($doctrine)
    {
        $this->em = $doctrine->getEntityManager();
        $this->textRepository = $this->em->getRepository(self::ENTITY_TEXT_NAME);
    }

    /**
     * Sets locale
     *
     * @param int $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Sets locales
     *
     * @param array $locales
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * Set pagination limit
     *
     * @param int $page
     */
    public function setPaginationLimit($lim)
    {
        if ($lim >= 0) {
            $this->paginationLimit = $lim;
        }
    }

    /**
     *
     * @param array $texts
     */
    public function updateTexts(array $texts)
    {
        // Exception
        if (!isset($this->revisionControlMode)) {
            throw new \Exception(
                __METHOD__ . ': revisionControlMode is not set. Please set text_revision_control in parameters.yml !'
            );
        }

        $this->textRepository->setTextRevisionControl($this->revisionControlMode);

        if (!empty($texts)) {
            foreach ($texts as $key => $value) {
                if ($key > 0 && strlen($value)) {
                    $this->textRepository->updateText($key, $value);
                }
            }
        }
    }
}

