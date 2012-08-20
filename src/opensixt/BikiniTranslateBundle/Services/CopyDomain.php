<?php
namespace opensixt\BikiniTranslateBundle\Services;

use opensixt\BikiniTranslateBundle\Services\HandleText;

/**
 * CopyDomain
 * Intermediate layer between Controller and Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class CopyDomain extends HandleText
{
    public function __construct($doctrine, $locale)
    {
        $this->commonLanguage = $locale;

        parent::__construct($doctrine);

        $this->commonLanguageId = $this->textRepository->getIdByLocale($locale);
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
            throw new \Exception(
                __METHOD__ . ': revisionControlMode is not set. Please set text_revision_control in parameters.yml !'
            );
        }
        if (!isset($this->commonLanguage)) {
            throw new \Exception(
                __METHOD__ . ': _commonLanguage is not set. Please set common_language in parameters.yml !'
            );
        }

        $this->textRepository->setTextRevisionControl($this->revisionControlMode);
        $this->textRepository->setCommonLanguage($this->commonLanguage);
        $this->textRepository->setCommonLanguageId($this->commonLanguageId);

        $translationsCount = $this->textRepository->copyLanguageContent(
            $from,
            $to,
            $resources
        );

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
            throw new \Exception(
                __METHOD__ . ': revisionControlMode is not set. Please set text_revision_control in parameters.yml !'
            );
        }
        if (!isset($this->commonLanguage)) {
            throw new \Exception(
                __METHOD__ . ': _commonLanguage is not set. Please set common_language in parameters.yml !'
            );
        }
        if (empty($this->locales)) {
            throw new \Exception(
                __METHOD__ . ': _locales is not set. Please set it with ' . __CLASS__ . '::setLocales() !'
            );
        }

        $this->textRepository->setTextRevisionControl($this->revisionControlMode);
        $this->textRepository->setCommonLanguage($this->commonLanguage);
        $this->textRepository->setCommonLanguageId($this->commonLanguageId);
        $this->textRepository->setResources($resources);

        $translationsCount = $this->textRepository->copyResourceContent(
            $from,
            $to,
            $this->locales
        );

        return $translationsCount;
    }
}

