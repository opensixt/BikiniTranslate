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
class EditText extends HandleText
{
    /**
     * @var int
     */
    public $exportChunkLimit;

    public function __construct($doctrine, $locale)
    {
        $this->paginationLimit = 15;
        $this->commonLanguage = $locale;

        parent::__construct($doctrine);
        $this->commonLanguageId = $this->textRepository->getIdByLocale($locale);
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
        if (!$this->commonLanguageId) {
            throw new \Exception(
                __METHOD__ . ': commonLanguage is not set. Please set common_language in parameters.yml !'
            );
        }

        $isEqual = false;
        if ($this->commonLanguageId == $currentLocale) {
            $isEqual = true;
        }
        return $isEqual;
    }

    /**
     * Returns search results and pagination data
     *
     * @param int $page
     * @param int $locale
     * @param array $searchResources search resources
     * @param array $resources all available resources
     * @param boolean $suggestionsFlag if true get suggegstions for any text
     * @return array
     * @throws \Exception
     */
    public function getData($page, $locale, $searchResources, $resources, $suggestionsFlag = false)
    {
        $this->textRepository->setCommonLanguage($this->commonLanguage);
        $this->textRepository->setCommonLanguageId($this->commonLanguageId);

        // count of all results for the search parameters
        $this->textRepository->init(
            TextRepository::TASK_MISSING_TRANS_BY_LANG,
            $locale,
            $searchResources
        );

        $query = $this->textRepository->getMissingTranslations();

        if (empty($this->paginationLimit)) {
            $this->paginationLimit = PHP_INT_MAX;
        }
        $data = $this->paginator->paginate($query, $page, $this->paginationLimit);

        // set resource as GET parameter for paginator links,
        // if resource filter is set
        if (count($searchResources) == 1) {
            $data->setParam('resource', $searchResources[0]);
        }

        // set messages in common language for any text in $translations
        $this->textRepository->setMessagesInCommonLanguage($data);

        // get suggegstions (translations with same hash from other resources)
        if ($suggestionsFlag && count($data)) {
            foreach ($data as $txt) {
                $txt->setSuggestions(
                    $this->textRepository->getSuggestionByHashAndLanguage(
                        $txt->getHash(),
                        $txt->getLocaleId(),
                        $txt->getResourceId(),
                        $resources
                    )
                );
            }
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
                $elem['target'][0]['target'] = $elem['source'];
            }
        }
        if (!empty($this->exportChunkLimit)) {
            // chunks an array into $this->exportChunkLimit large chunks
            $chunks = array_chunk($data, $this->exportChunkLimit);
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
    public function sendToTranslationService($exportXliff, $chunk)
    {
        // TODO: write send functionality
        // now save data to /tmp
        $fname = '/tmp/sendtots_' . date('Ymd-His') . '_' . mt_rand(0, 10000) . '.xliff';
        file_put_contents($fname, $exportXliff);

        // and set translation_service flag for fields from $chunk
        $ids = array_reduce(
            $chunk,
            // @codingStandardsIgnoreStart
            function ($ids, $elem) {
            // @codingStandardsIgnoreEnd
                $ids[] = $elem['id'];
                return $ids;
            },
            array()
        );

        $this->textRepository->setTranslationServiceFlag($ids);
        // TODO: return send result or throw exception
    }
}

