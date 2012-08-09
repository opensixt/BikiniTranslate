<?php
namespace opensixt\BikiniTranslateBundle\Services;

use opensixt\BikiniTranslateBundle\Services\HandleText;

/**
 * CopyDomain
 * Intermediate layer between Controller and Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class CopyDomain extends HandleText {

    public function __construct($doctrine, $locale)
    {
        $this->_commonLanguage = $locale;

        parent::__construct($doctrine);

        $this->_commonLanguageId = $this->_textRepository->getIdByLocale($locale);
    }

    /**
     * Copy Language
     *
     * @param int $from
     * @param int $to
     * @param array $resources
     * @return int count of changes
     */
    public function copyLanguage($from, $to, $resources)
    {
        // Exception
        if (!isset($this->revisionControlMode)) {
            throw new \Exception(__METHOD__ . ': revisionControlMode is not set. Please set text_revision_control in parameters.yml !');
        }
        if (!isset($this->_commonLanguage)) {
            throw new \Exception(__METHOD__ . ': _commonLanguage is not set. Please set common_language in parameters.yml !');
        }

        $this->_textRepository->setTextRevisionControl($this->revisionControlMode);
        $this->_textRepository->setCommonLanguage($this->_commonLanguage);
        $this->_textRepository->setCommonLanguageId($this->_commonLanguageId);

        $translationsCount = $this->_textRepository->copyLanguageContent(
            $from,
            $to,
            $resources);

        return $translationsCount;
    }

    /**
     * Copy Resource
     *
     * @param int $from
     * @param int $to
     * @param array $resources
     * @return int count of changes
     */
    public function copyResource($from, $to, $resources)
    {
        // Exception
        if (!isset($this->revisionControlMode)) {
            throw new \Exception(__METHOD__ . ': revisionControlMode is not set. Please set text_revision_control in parameters.yml !');
        }
        if (!isset($this->_commonLanguage)) {
            throw new \Exception(__METHOD__ . ': _commonLanguage is not set. Please set common_language in parameters.yml !');
        }
        if (empty($this->_locales)) {
            throw new \Exception(__METHOD__ . ': _locales is not set. Please set it with ' . __CLASS__ . '::setLocales() !');
        }

        $this->_textRepository->setTextRevisionControl($this->revisionControlMode);
        $this->_textRepository->setCommonLanguage($this->_commonLanguage);
        $this->_textRepository->setCommonLanguageId($this->_commonLanguageId);
        $this->_textRepository->setResources($resources);

        $translationsCount = $this->_textRepository->copyResourceContent(
            $from,
            $to,
            $this->_locales);

        return $translationsCount;
    }

}
