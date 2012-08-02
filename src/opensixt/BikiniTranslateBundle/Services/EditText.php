<?php

namespace opensixt\BikiniTranslateBundle\Services;

use opensixt\BikiniTranslateBundle\Services\HandleText;
use opensixt\BikiniTranslateBundle\Repository\TextRepository;

use opensixt\BikiniTranslateBundle\Helpers\Pagination;

/**
 * SearchSearch
 * Intermediate layer between Controller and Model (part of controller)
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class EditText extends HandleText {

    /**
     * @var string
     */
    private $_commonLanguage;

    /**
     * @var int
     */
    private $_commonLanguageId;

    private $_revisionControlMode;


    public function __construct($doctrine, $locale, $text_revision_control)
    {
        $this->_paginationLimit = 15;
        $this->_revisionControlMode = $text_revision_control;
        $this->_commonLanguage = $locale;

        parent::__construct($doctrine);

        $this->_commonLanguageId = $this->_textRepository->getIdByLocale($locale);
    }

    /**
     * Compares common and current locales
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @return boolean true if locales equal
     */
    public function compareCommonAndCurrentLocales()
    {
        // Exceptions
        if (!$this->_locale) {
            throw new \Exception(__METHOD__ . ': _locale is not set. Please set it with ' . __CLASS__ . '::setLocale() !');
        }
        if (!$this->_commonLanguageId) {
            throw new \Exception(__METHOD__ . ': _commonLangauge is not set. Please set common_language in parameters.yml !');
        }

        $isEqual = false;
        if ($this->_commonLanguageId == $this->_locale) {
            $isEqual = true;
        }
        return $isEqual;
    }

    /**
     * Returns search results and pagination data
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @return array
     * @throws \Exception
     */
    public function getData()
    {
        // Exceptions
        if (!$this->_locale) {
            throw new \Exception(__METHOD__ . ': _locale is not set. Please set it with ' . __CLASS__ . '::setLocale() !');
        }
        if (empty($this->_resources)) {
            throw new \Exception(__METHOD__ . ': _resources is not set. Please set it with ' . __CLASS__ . '::setResources() !');
        }

        $data = array();

        $this->_textRepository->setCommonLanguage($this->_commonLanguage);
        $this->_textRepository->setCommonLanguageId($this->_commonLanguageId);

        // count of all results for the search parameters
        $textCount = $this->_textRepository->getTextCount(
            TextRepository::TASK_MISSING_TRANS_BY_LANG,
            $this->_locale,
            $this->_resources);

        // get pagination bar
        $pagination = new Pagination(
            $textCount,
            $this->_paginationLimit,
            $this->_paginationPage);
        $data['paginationBar'] = $pagination->getPaginationBar();

        // get search results
        $data['texts'] = $this->_textRepository->getMissingTranslations(
            $this->_paginationLimit,
            $pagination->getOffset());

        return $data;
    }

    /**
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param array $texts
     */
    public function updateTexts(array $texts)
    {
        // Exception
        if (!isset($this->_revisionControlMode)) {
            throw new \Exception(__METHOD__ . ': _revisionControlMode is not set. Please set text_revision_control in parameters.yml !');
        }

        $this->_textRepository->setTextRevisionControl($this->_revisionControlMode);

        if (!empty($texts)) {
            foreach ($texts as $key => $value) {
                if ($key > 0 && strlen($value)) {
                    $this->_textRepository->updateText($key, $value);
                }
            }
        }
    }

}
