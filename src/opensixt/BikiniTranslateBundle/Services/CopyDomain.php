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

    protected $_domainFrom;

    protected $_domainTo;


    public function __construct($doctrine, $locale, $text_revision_control)
    {
        $this->_revisionControlMode = $text_revision_control;
        $this->_commonLanguage = $locale;

        parent::__construct($doctrine);

        $this->_commonLanguageId = $this->_textRepository->getIdByLocale($locale);
    }

    /**
     * Set source domain
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param type $from
     */
    public function setDomainFrom($from)
    {
        $this->_domainFrom = $from;
    }

    /**
     * Set destination domain
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param type $to
     */
    public function setDomainTo($to)
    {
        $this->_domainTo = $to;
    }

    /**
     * Copy Language
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @return int count of changes
     */
    public function copyLanguage()
    {
        // Exception
        if (!isset($this->_revisionControlMode)) {
            throw new \Exception(__METHOD__ . ': _revisionControlMode is not set. Please set text_revision_control in parameters.yml !');
        }
        if (!isset($this->_commonLanguage)) {
            throw new \Exception(__METHOD__ . ': _commonLanguage is not set. Please set common_language in parameters.yml !');
        }
        if (empty($this->_resources)) {
            throw new \Exception(__METHOD__ . ': _resources is not set. Please set it with ' . __CLASS__ . '::setResources() !');
        }
        if (empty($this->_domainFrom)) {
            throw new \Exception(__METHOD__ . ': _domainFrom is not set. Please set it with ' . __CLASS__ . '::setDomainFrom() !');
        }
        if (empty($this->_domainTo)) {
            throw new \Exception(__METHOD__ . ': _domainTo is not set. Please set it with ' . __CLASS__ . '::setDomainTo() !');
        }

        $this->_textRepository->setTextRevisionControl($this->_revisionControlMode);
        $this->_textRepository->setCommonLanguage($this->_commonLanguage);
        $this->_textRepository->setCommonLanguageId($this->_commonLanguageId);

        $translationsCount = $this->_textRepository->copyLanguageContent(
            $this->_domainFrom,
            $this->_domainTo,
            $this->_resources);

        return $translationsCount;
    }

    /**
     * Copy Resource
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @return int count of changes
     */
    public function copyResource()
    {
        // Exception
        if (!isset($this->_revisionControlMode)) {
            throw new \Exception(__METHOD__ . ': _revisionControlMode is not set. Please set text_revision_control in parameters.yml !');
        }
        if (!isset($this->_commonLanguage)) {
            throw new \Exception(__METHOD__ . ': _commonLanguage is not set. Please set common_language in parameters.yml !');
        }
        if (empty($this->_resources)) {
            throw new \Exception(__METHOD__ . ': _resources is not set. Please set it with ' . __CLASS__ . '::setResources() !');
        }
        if (empty($this->_domainFrom)) {
            throw new \Exception(__METHOD__ . ': _domainFrom is not set. Please set it with ' . __CLASS__ . '::setDomainFrom() !');
        }
        if (empty($this->_domainTo)) {
            throw new \Exception(__METHOD__ . ': _domainTo is not set. Please set it with ' . __CLASS__ . '::setDomainTo() !');
        }
        if (empty($this->_locales)) {
            throw new \Exception(__METHOD__ . ': _locales is not set. Please set it with ' . __CLASS__ . '::setLocales() !');
        }

        $this->_textRepository->setTextRevisionControl($this->_revisionControlMode);
        $this->_textRepository->setCommonLanguage($this->_commonLanguage);
        $this->_textRepository->setCommonLanguageId($this->_commonLanguageId);
        $this->_textRepository->setResources($this->_resources);

        $translationsCount = $this->_textRepository->copyResourceContent(
            $this->_domainFrom,
            $this->_domainTo,
            $this->_locales);

        return $translationsCount;
    }

}
