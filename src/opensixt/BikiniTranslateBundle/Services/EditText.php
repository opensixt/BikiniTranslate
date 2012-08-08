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
class EditText extends HandleText {

    /**
     * @var int
     */
    public $_exportChunkLimit;

    public function __construct($doctrine, $locale)
    {
        $this->_paginationLimit = 15;
        $this->_commonLanguage = $locale;

        parent::__construct($doctrine);
        $this->_commonLanguageId = $this->_textRepository->getIdByLocale($locale);
    }

    /**
     * Compares common and current locales
     *
     * @param int $currentLocale
     * @return boolean true if locales equal
     */
    public function compareCommonAndCurrentLocales($currentLocale)
    {
        // Exceptions
        if (!$this->_commonLanguageId) {
            throw new \Exception(__METHOD__ . ': _commonLangauge is not set. Please set common_language in parameters.yml !');
        }

        $isEqual = false;
        if ($this->_commonLanguageId == $currentLocale) {
            $isEqual = true;
        }
        return $isEqual;
    }

    /**
     * Returns search results and pagination data
     *
     * @param int $locale
     * @param array $resources resource ids
     * @return array
     * @throws \Exception
     */
    public function getData($locale, $resources)
    {
        // Exceptions
        /*if (!$this->_locale) {
            throw new \Exception(__METHOD__ . ': _locale is not set. Please set it with ' . __CLASS__ . '::setLocale() !');
        }
        if (empty($this->_resources)) {
            throw new \Exception(__METHOD__ . ': _resources is not set. Please set it with ' . __CLASS__ . '::setResources() !');
        }*/

        $data = array();

        $this->_textRepository->setCommonLanguage($this->_commonLanguage);
        $this->_textRepository->setCommonLanguageId($this->_commonLanguageId);

        // count of all results for the search parameters
        $textCount = $this->_textRepository->getTextCount(
            TextRepository::TASK_MISSING_TRANS_BY_LANG,
            $locale,
            $resources);

        if (!empty($this->_paginationLimit)) {
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
        } else {
            $data['texts'] = $this->_textRepository->getMissingTranslations();
        }

        return $data;
    }

    /**
     *
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

    /**
     * Prepares data to export (xliff, xml, etc.)
     *
     * @param array $data
     * @return array
     */
    public function prepareExportData($data)
    {
        $chunks = array();
        if (count($data)) {
            foreach ($data as &$elem) {
                $elem['target']['target'] = $elem['source'];
            }
        }
        if (!empty($this->_exportChunkLimit)) {
            // chunks an array into $this->_exportChunkLimit large chunks
            $chunks = array_chunk($data, $this->_exportChunkLimit);
        } else {
            $chunks[0] = $data;
        }
        return $chunks;
    }

    /**
     *
     * @param type $exportXliff
     * @param type $chunk
     */
    public function sendToTS($exportXliff, $chunk)
    {
        // TODO: write send functionality
        // now save data to /tmp
        $fname = '/tmp/sendtots_' . date('Ymd-His') . '_'. mt_rand(0,10000) . '.xliff';
        file_put_contents ($fname, $exportXliff);

        // and set translation_service flag for fields from $chunk
        $ids = array_reduce(
            $chunk,
            function($ids, $elem) { $ids[] = $elem['id']; return $ids; },
            array());
        $this->_textRepository->setTranslationServiceFlag($ids);
        // TODO: return send result or throw exception
    }

}
