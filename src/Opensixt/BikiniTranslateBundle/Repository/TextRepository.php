<?php

namespace Opensixt\BikiniTranslateBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

use Opensixt\BikiniTranslateBundle\Entity\TextRevision;
use Opensixt\BikiniTranslateBundle\Entity\Text;
use Opensixt\BikiniTranslateBundle\Entity\Language;

use Opensixt\BikiniTranslateBundle\Repository\ResourceRepository as ResourceRepo;
use Opensixt\BikiniTranslateBundle\Repository\LanguageRepository as LanguageRepo;

/**
 * Text Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class TextRepository extends EntityRepository
{
    const FIELD_ID               = 't.id';
    const FIELD_HASH             = 't.hash';
    const FIELD_SOURCE           = 't.source';
    const FIELD_TARGET           = 't.target';
    const FIELD_REVISION_ID      = 't.textRevisionId';
    const FIELD_RESOURCE         = 't.resourceId';
    const FIELD_LOCALE           = 't.localeId';
    const FIELD_USER             = 't.userId';
    const FIELD_EXPIRY_DATE      = 't.expiryDate';
    const FIELD_DELETED_DATE     = 't.deletedDate';
    const FIELD_RELEASED         = 't.released';
    const FIELD_TS               = 't.translationService';
    const FIELD_BLOCK            = 't.block';
    const FIELD_TRANSLATE_ME     = 't.translateMe';
    const FIELD_DONTTRANSLATE    = 't.dontTranslate';
    const FIELD_TRANSLATION_TYPE = 't.translationType';

    const TASK_MISSING_TRANS_BY_LANG       = 0;
    const TASK_SEARCH_PHRASE_BY_LANG       = 1;
    const TASK_ALL_CONTENT_BY_LANG         = 2;
    const TASK_ALL_CONTENT_BY_RES          = 3;
    const TASK_SEARCH_FLAGGED_TEXTS        = 4;
    const TASK_SEARCH_BY_TRANSLATED_STATUS = 5;

    const DOMAIN_TYPE_LANGUAGE = 1;
    const DOMAIN_TYPE_RESOURCE = 2;

    /** @var string */
    protected $task;

    /** @var array */
    protected $resources;

    /** @var int */
    protected $locale;

    /** @var array */
    protected $locales;

    /** @var string */
    protected $commonLanguage;

    /** @var int */
    protected $commonLanguageId;

    /** @var Datetime */
    protected $expiryDate;

    /** @var int */
    protected $translationType = Text::TRANSLATION_TYPE_TEXT;

    /** @var boolean */
    protected $translated;

    /** @var int */
    protected $userId;

    /**
     *
     * @param string $task
     */
    public function setTask($task)
    {
        $this->task = $task;
    }

    /**
     *
     * @param array $resources
     */
    public function setResources($resources)
    {
        $this->resources = $resources;
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
     *
     * @param string $locale
     */
    public function setCommonLanguage($locale)
    {
        $this->commonLanguage = $locale;
        $this->commonLanguageId = $this->getIdByLocale($locale);
    }

    /**
     *
     * @param int $id
     */
    public function setCommonLanguageId($id)
    {
        $this->commonLanguageId = $id;
    }

    /**
     *
     * @param string $searchString
     */
    public function setSearchString($searchString)
    {
        $this->searchString = $searchString;
    }

    /**
     * Set expiryDate attribute for search
     *
     * @param date $date
     */
    public function setExpiryDate($date)
    {
        $this->expiryDate = $date;
    }

    /**
     * Set translated attribute for search
     *
     * @param boolean $flag
     */
    public function setTranslated($flag)
    {
        $this->translated = $flag;
    }

    /**
     * Sets userId
     *
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Sets class attributes
     *
     * @param int $task
     * @param int $locale locale id
     * @param array $resources
     */
    public function init($task, $locale, $resources, $translationType = Text::TRANSLATION_TYPE_TEXT)
    {
        $this->setTask($task);
        $this->setLocale($locale);
        $this->setResources($resources);
        $this->translationType = $translationType;
    }

    /**
     * Get count of records in text table
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param int $task
     * @param int $locale locale id
     * @param array $resources
     * @param int $hts
     * @return int texts count
     */
    public function getTextCount($task, $locale, $resources, $hts = false)
    {
        $this->init($task, $locale, $resources, $hts);

        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t)');

        $this->setQueryParameters($query);

        $count = $query->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * Gets list of texts without translations
     *
     * @return QueryBuilder
     */
    public function getTranslations()
    {
        $query = $this->getBaseQuery();
        $this->setQueryParameters($query);

        return $query;
    }

    /**
     *
     * @param string $hash
     * @return ArrayCollection
     */
    public function getTextsByHash($hash)
    {
        $messages = new ArrayCollection;

        if (strlen(trim($hash))) {
            $query = $this->getBaseQuery();

            $query->andWhere(self::FIELD_HASH . ' =  ?1')
                ->setParameter(1, $hash)
                ->andWhere(self::FIELD_DELETED_DATE . ' IS NULL')
                ->andWhere(self::FIELD_TRANSLATE_ME . ' = 0')
                ->andWhere(self::FIELD_RELEASED . ' = 1')
                ->addOrderBy('l.locale', 'DESC');

            $messages = $query->getQuery()->getResult();
        }

        return $messages;
    }

    /**
     * Copy language ($langFrom) contents to another language ($langTo)
     * for available resources.
     *
     * @param int $langFrom
     * @param int $langTo
     * @param array $resources
     * @return int count of updated records
     */
    public function copyLanguageContent($langFrom, $langTo, $resources)
    {
        $translationsCount = 0;
        if (count($resources)) {
            $this->setResources($resources);

            // get source data
            $sourceData = $this->getAllByLanguage($langFrom);

            // set data for destination locale
            $textsDestLang = $this->getMessagesByLanguage(
                $sourceData,
                array($langTo)
            );

            $translationsCount = $this->updateEmptyTranslations(
                $sourceData,
                $textsDestLang,
                self::DOMAIN_TYPE_LANGUAGE
            );
        }
        return $translationsCount;
    }

    /**
     * Copy resource ($resFrom) contents to another resource ($resTo)
     * for any locales from $languages
     *
     * @param int $resFrom
     * @param int $resTo
     * @param array $languages
     * @return int count of updated records
     */
    public function copyResourceContent($resFrom, $resTo, $languages)
    {
        $translationsCount = 0;
        if (!empty($resFrom) && !empty($resTo) && count($languages)) {
            $this->setLocales($languages);

            // get source data
            $sourceData = $this->getAllByResource($resFrom);

            // set data for destination resource for any language from $languages
            $textsDestLang = $this->getMessagesByLanguage(
                $sourceData,
                $languages,
                array($resTo)
            );

            $translationsCount = $this->updateEmptyTranslations(
                $sourceData,
                $textsDestLang,
                self::DOMAIN_TYPE_RESOURCE
            );
        }
        return $translationsCount;
    }

    /**
     * Get all contents from Text table by resource
     *
     * @param int $resource
     * @return array
     */
    protected function getAllByResource($resource)
    {
        $contents = array();
        if (!empty($resource)) {
            $this->setTask(self::TASK_ALL_CONTENT_BY_RES);

            $query = $this->createQueryBuilder('t')
                ->select('t, r')
                ->leftJoin('t.resource', 'r');

            $this->setQueryParameters($query);

            $query->andWhere(self::FIELD_RESOURCE . ' = ?3')
                ->setParameter(3, $resource);

            $contents = $query->getQuery()->getResult();
        }
        return $contents;
    }

    /**
     * Get all contents from Text table by language
     *
     * @param int $locale language id
     * @return array
     */
    protected function getAllByLanguage($locale)
    {
        $contents = array();
        if (!empty($locale)) {
            $this->setTask(self::TASK_ALL_CONTENT_BY_LANG);
            $this->setLocales(array($locale));

            $query = $this->createQueryBuilder('t')
                ->select('t, r')
                ->leftJoin('t.resource', 'r');

            $this->setQueryParameters($query);

            $contents = $query->getQuery()->getResult();
        }
        return $contents;
    }

    /**
     * Copy translations from sourceData for destLocale language
     *
     * @param array $sourceData
     * @param array $textsDestLang array with new values
     * @param int $domainType
     * @return int count of updates
     */
    protected function updateEmptyTranslations($sourceData, $textsDestLang, $domainType)
    {
        $changesCount = 0;
        $translations = array();

        // merge all empty translations in destination language
        foreach ($textsDestLang as $txt) {
            if ($txt->getTranslateMe() === true) {
                $txtId = $txt->getId();

                // if the list of availabte resources is not set
                if (empty($this->resources)) {
                    throw new \Exception(
                        __METHOD__ . ': resources is not set. Please set it with ' . __CLASS__ . '::setResources() !'
                    );
                }

                // if destination text belongs to available resource
                if (in_array($txt->getResourceId(), $this->resources)) {
                    // get text from $sourceData by hash and resource

                    foreach ($sourceData as $src) {
                        if ($src->getHash() == $txt->getHash()) {
                            if ($src->getResourceId() == $txt->getResourceId()
                                    && $domainType == self::DOMAIN_TYPE_LANGUAGE) {
                                $translations[$txtId] = $src->getTarget();
                                break;
                            }
                            if ($src->getLocaleId() == $txt->getLocaleId()
                                    && $domainType == self::DOMAIN_TYPE_RESOURCE) {
                                $translations[$txtId] = $src->getTarget();
                                break;
                            }
                        }
                    }

                }
            }
        }

        // if $translation not empty, update texts
        if (!empty($translations)) {
            $changesCount = count($translations);
            $this->updateTexts($translations);
        }

        return $changesCount;
    }

    /**
     * Get messages in $locales language(s) for any hash from $texts
     * and return it like array.
     *
     * $resources - array of resources, - optional filter by it
     *
     * INDEX: IDX_getMessagesResourceByLanguage, IDX_getMessagesByLanguage
     *
     * @param SlidingPagination $texts
     * @param array $locales
     * @param array $resources
     * @return ArrayCollection
     */
    protected function getMessagesByLanguage(/*SlidingPagination*/ $texts, array $locales, array $resources = array())
    {
        $messages = new ArrayCollection;
        $hashes = $this->getHashes($texts);

        $query = $this->getBaseQuery();

        if (count($resources)) {
            $query->andWhere(self::FIELD_RESOURCE . ' IN  (?4)')
                ->setParameter(4, $resources);
        }

        if (count($hashes) && count($locales)) {
            $query
                ->andWhere(self::FIELD_LOCALE . ' IN (?2)')
                ->andWhere(self::FIELD_TRANSLATION_TYPE . ' = ?3')
                ->andWhere(self::FIELD_DELETED_DATE . ' IS NULL')
                ->andWhere(self::FIELD_HASH . ' IN (?1)')
                ->setParameter(1, $hashes)
                ->setParameter(2, $locales)
                ->setParameter(3, $this->translationType);

            $messages = $query->getQuery()->getResult();
        }

        return $messages;
    }

    /**
     * @return QueryBuilder
     */
    protected function getBaseQuery()
    {
        $query = $this->createQueryBuilder('t')
            ->select('t, r, l, u')
            ->leftJoin('t.resource', 'r')
            ->leftJoin('t.locale', 'l')
            ->leftJoin('t.user', 'u');

        return $query;
    }

    /**
     * Get suggegstions (translations with same hash from other resources)
     *
     * INDEX: IDX_getSuggestionByHashAndLanguage
     *
     * @param string $hash md5 string
     * @param int $locale source langiage
     * @param int $resource source resource
     * @param array $allResources available resources
     * @return array texts with same hash and language and with another resources
     */
    public function getSuggestionByHashAndLanguage($hash, $locale, $resource, $allResources)
    {
        $suggestions = array();

        if (strlen($hash) && $locale) {
            $query = $this->createQueryBuilder('t')
                ->select('t, r')
                ->leftJoin('t.resource', 'r')
                ->andWhere(self::FIELD_RESOURCE . ' != ?3')
                ->andWhere(self::FIELD_RESOURCE . ' in (?4)')
                ->andWhere(self::FIELD_LOCALE . ' = ?2')
                ->andWhere(self::FIELD_TRANSLATION_TYPE . ' = ?5')
                ->andWhere(self::FIELD_DELETED_DATE . ' IS NULL')
                ->andwhere(self::FIELD_HASH . ' = ?1')
                ->andWhere(self::FIELD_TRANSLATE_ME . ' = 0')
                ->setParameter(1, $hash)
                ->setParameter(2, $locale)
                ->setParameter(3, $resource)
                ->setParameter(4, $allResources)
                ->setParameter(5, $this->translationType);
            $suggestions = $query->getQuery()->getArrayResult();
        }

        return $suggestions;
    }

    /**
     * Get Text Id by hash, locale and resource
     * !!! ONLY for getFromTranslationService
     *
     * INDEX: IDX_getIdByHashAndLocaleAndResource
     *
     * @param string $hash md5 string
     * @param int $locale source language
     * @param int $resource source resource
     * @return array texts with same hash and language and with another resources
     */
    public function getIdByHashAndLocaleAndResource($hash, $locale, $resource)
    {
        // TODO: source getFromTranslationService Wheres
        $id = 0;
        if (strlen($hash) && $locale && $resource) {
            $query = $this->createQueryBuilder('t')
                ->select('t')
                ->andWhere(self::FIELD_RESOURCE . ' = ?3')
                ->andWhere(self::FIELD_LOCALE . ' = ?2')
                ->andWhere(self::FIELD_TRANSLATION_TYPE . ' = ?4')
                ->andWhere(self::FIELD_DELETED_DATE . ' IS NULL')
                ->andwhere(self::FIELD_HASH . ' = ?1')
                ->andWhere(self::FIELD_TRANSLATE_ME . ' = 1')
                ->andWhere(self::FIELD_TS . ' = 1')
                ->setParameter(1, $hash)
                ->setParameter(2, $locale)
                ->setParameter(3, $resource)
                ->setParameter(4, $this->translationType);
            $result = $query->getQuery()->getOneOrNullResult();
            if (is_object($result)) {
                $id = $result->getId();
            }
        }
        return $id;
    }

    /**
     * Get array with unique values if key 'hash' from $texts
     *
     * @param array of objects Entity/Text
     * @return array
     */
    protected function getHashes($texts)
    {
        $hashes = array();
        if (count($texts)) {
            foreach ($texts as $text) {
                $hashes[$text->getHash()] = $text->getHash();
            }
        }
        return array_values($hashes);
    }

    /**
     * Set messages in $locale language for any hash from $texts
     * if current locale not equal common language
     *
     * @param array $texts
     */
    public function setMessagesInLanguage(&$texts, $languageId)
    {
        $textsLang = $this->getMessagesByLanguage($texts, array($languageId));
        foreach ($texts as $text) {
            $message = '';
            foreach ($textsLang as $textLang) {
                if ($text->getHash() == $textLang->getHash()) {
                    $message = $textLang->getTarget();
                    break;
                }
            }
            $text->setTextInCommonLanguage($message);
        }
    }

    /**
     * Set messages in $locale language for any hash from $texts
     * if current locale not equal common language
     *
     * @param array $texts
     */
    public function setMessagesInCommonLanguage(&$texts)
    {
        if ($this->locale != $this->commonLanguageId) {
            $this->setMessagesInLanguage($texts, $this->commonLanguageId);
        }
    }

    /**
     * Set query parameters by $this->task
     *
     * @param QueryBuilder $query
     */
    protected function setQueryParameters($query)
    {
        // Exceptions
        if (!isset($this->task)) {
            throw new \Exception(__METHOD__ . ': task is not set. Please set it with ' . __CLASS__ . '::init() !');
        }

        switch ($this->task) {
            case self::TASK_SEARCH_PHRASE_BY_LANG:
                $query->andWhere(self::FIELD_RESOURCE . ' IN (?2)')
                    ->andWhere(self::FIELD_LOCALE . ' = ?3')
                    ->andWhere(self::FIELD_TRANSLATION_TYPE . ' = ?4')
                    ->andWhere(self::FIELD_DELETED_DATE . ' IS NULL')
                    ->andWhere(self::FIELD_EXPIRY_DATE . ' IS NULL')
                    ->andWhere(self::FIELD_TARGET . ' LIKE ?1')
                    ->setParameter(1, $this->searchString)
                    ->setParameter(2, $this->resources)
                    ->setParameter(3, $this->locale)
                    ->setParameter(4, $this->translationType)
                    ->addOrderBy(ResourceRepo::FIELD_NAME, 'ASC')
                    ->addOrderBy(self::FIELD_TARGET, 'ASC');

                break;
            case self::TASK_ALL_CONTENT_BY_LANG:
            case self::TASK_ALL_CONTENT_BY_RES:
                if (!empty($this->resources)) {
                    $query->andWhere(self::FIELD_RESOURCE . ' IN (?3)')
                        ->setParameter(3, $this->resources);
                }

                $query->andwhere(self::FIELD_LOCALE . ' IN (?1)')
                    ->andWhere(self::FIELD_TRANSLATION_TYPE . ' = ?2')
                    ->andWhere(self::FIELD_DELETED_DATE . ' IS NULL')
                    ->andWhere(self::FIELD_EXPIRY_DATE . ' IS NULL')
                    ->andWhere(self::FIELD_TRANSLATE_ME . ' = 0');

                if ($this->locale == $this->commonLanguageId) {
                    $query->andWhere(self::FIELD_RELEASED . ' = 1');
                }

                $query->setParameter(1, $this->locales)
                    ->setParameter(2, $this->translationType);

                break;
            case self::TASK_SEARCH_FLAGGED_TEXTS:
                $query->andWhere(self::FIELD_RESOURCE . ' IN (?1)')
                    ->setParameter(1, $this->resources);

                if (!empty($this->locale)) {
                    $query->andWhere(self::FIELD_LOCALE . ' = ?3')
                        ->setParameter(3, $this->locale);
                }
                if (!empty($this->locales)) {
                    $query->andWhere(self::FIELD_LOCALE . ' IN (?4)')
                        ->setParameter(4, $this->locales);
                }

                $query->andWhere(self::FIELD_TRANSLATION_TYPE . ' = ?2')
                    ->setParameter(2, $this->translationType)
                    ->andWhere(self::FIELD_DELETED_DATE . ' IS NULL');

                if (!empty($this->expiryDate)) {
                    // expired texts
                    $query->andWhere(self::FIELD_EXPIRY_DATE . ' <= ?5')
                        ->setParameter(5, $this->expiryDate);
                } else {
                    // non released texts
                    $query->andWhere(self::FIELD_EXPIRY_DATE . ' IS NULL')
                        ->andWhere(self::FIELD_RELEASED . ' IS NULL OR ' . self::FIELD_RELEASED . ' = 0');
                }

                $query->addOrderBy(ResourceRepo::FIELD_NAME, 'ASC')
                    ->addOrderBy(LanguageRepo::FIELD_LOCALE, 'ASC')
                    ->addOrderBy(self::FIELD_TARGET, 'ASC');

                break;
            case self::TASK_SEARCH_BY_TRANSLATED_STATUS:
                $query->andWhere(self::FIELD_RESOURCE . ' IN (?1)')
                    ->setParameter(1, $this->resources);

                if (!empty($this->locale)) {
                    $query->andWhere(self::FIELD_LOCALE . ' = ?3')
                        ->setParameter(3, $this->locale);
                }
                if (!empty($this->locales)) {
                    $query->andWhere(self::FIELD_LOCALE . ' IN (?4)')
                        ->setParameter(4, $this->locales);
                }

                $query->andWhere(self::FIELD_TRANSLATION_TYPE . ' = ?2')
                    ->setParameter(2, $this->translationType)
                    ->andWhere(self::FIELD_DELETED_DATE . ' IS NULL');

                $query->andWhere(self::FIELD_TRANSLATE_ME . ' = ?5')
                    ->setParameter(5, intval(!$this->translated));

                $query->addOrderBy(LanguageRepo::FIELD_LOCALE, 'ASC')
                    ->addOrderBy(self::FIELD_SOURCE, 'ASC');

                break;
            case self::TASK_MISSING_TRANS_BY_LANG:
            default:
                $query->where(self::FIELD_RESOURCE . ' IN (?1)')
                    ->andWhere(self::FIELD_LOCALE . ' = ?2')
                    ->andWhere(self::FIELD_TRANSLATION_TYPE . ' = ?3')
                    ->andWhere(self::FIELD_DELETED_DATE . ' IS NULL')
                    ->andWhere(self::FIELD_EXPIRY_DATE . ' IS NULL')
                    ->andWhere(self::FIELD_TRANSLATE_ME . ' = 1')
                    ->andWhere(self::FIELD_RELEASED . ' = 1')
                    ->andWhere(self::FIELD_DONTTRANSLATE . ' IS NULL OR ' . self::FIELD_DONTTRANSLATE . ' = 0')
                    // just get the unflagged translations
                    // 0 = open state, 1 = already sent to translation service
                    ->andWhere(self::FIELD_TS . ' IS NULL OR ' . self::FIELD_TS . ' = 0')
                    ->setParameter(1, $this->resources)
                    ->setParameter(2, $this->locale)
                    ->setParameter(3, $this->translationType);

                $query->addOrderBy(ResourceRepo::FIELD_NAME, 'ASC')
                    ->addOrderBy(self::FIELD_SOURCE, 'ASC');

                break;
        }
    }

    /**
     * Updates texts: set target = $text for $id
     *
     * @param array $texts
     * @param boolean $translationService if true set texts as not released and translated
     */
    public function updateTexts(array $texts, $translationService = false)
    {
        if (!count($texts)) {
            return;
        }

        $em = $this->getEntityManager();

        foreach ($texts as $id => $text) {
            if ($id > 0 && strlen($text)) {
                $objText = $this->find($id);
                if (is_null($objText)) {
                    throw new \Exception(__METHOD__ . ': no such text id: ' . $id);
                }

                $objText->setTarget($text);
                if (!empty($this->userId)) {
                    $objText->setUserId($this->userId);
                }

                if ($translationService === true) {
                    // set texts as not released and translated
                    $objText->setReleased(false);
                    $objText->setTranslateMe(false);
                    $objText->setTranslationService(false);
                }
                $em->persist($objText);
            }
        }
        $em->flush();
    }

    /**
     * Set texts with $ids as released
     *
     * @param array $ids
     */
    public function releaseTexts(array $ids)
    {
        if (!count($ids)) {
            return;
        }

        $em = $this->getEntityManager();

        foreach ($ids as $id) {
            $objText = $this->find($id);
            if (is_null($objText)) {
                throw new \Exception(__METHOD__ . ': no such text id: ' . $id);
            }

            $objText->setReleased(true);
            $em->persist($objText);
        }
        $em->flush();
    }

    /**
     * Mark texts with $ids as deleted
     *
     * @param array $ids
     */
    public function markTextsAsDeleted(array $ids)
    {
        if (!count($ids)) {
            return;
        }

        $em = $this->getEntityManager();
        $now = new \DateTime();

        foreach ($ids as $id) {
            $objText = $this->find($id);
            if (is_null($objText)) {
                throw new \Exception(__METHOD__ . ': no such text id: ' . $id);
            }

            $objText->setDeletedDate($now);
            $em->persist($objText);
        }
        $em->flush();
    }

    /**
     * Set translation_service=1 for each id from $ids
     *
     * @param array $ids
     */
    public function setTranslationServiceFlag($ids)
    {
        if (!count($ids)) {
            return;
        }

        $em = $this->getEntityManager();

        foreach ($ids as $id) {
            $objText = $this->find($id);
            if (is_null($objText)) {
                throw new \Exception(__METHOD__ . ': no such text id: ' . $id);
            }

            $objText->setTranslationService(true);
            $em->persist($objText);
        }
        $em->flush();
    }

    /**
     * Get language Id by locale (from table Languages)
     *
     * @param string $langId
     */
    public function getIdByLocale($locale)
    {
        if (!$locale) {
            return false;
        }

        $repository = $this->getEntityManager()
            ->getRepository(Language::ENTITY_LANGUAGE);

        $langData = $repository->findBy(array('locale' => $locale));
        return $langData[0]->getId();
    }

    /**
     * @param \Opensixt\BikiniTranslateBundle\Entity\TextRevision $target
     * @return \Opensixt\BikiniTranslateBundle\Entity\Text
     */
    public function findOneByTarget(TextRevision $target)
    {
        $qb = $this->createQueryBuilder('t');
        $qb ->select('t')
            ->join('t.targets', 'tr')
            ->where('tr.id = ?1');
        $result = $qb->getQuery()->execute(array('1' => $target->getId()));

        return current($result);
    }

    /**
     * Get count of not translated texts by locales
     *
     * @param array $locales
     * @return int
     */
    public function getCountNotTranslatedTexts($locales)
    {
        $contents = array();
        if (!empty($locales) && is_array($locales)) {
            $this->setTask(self::TASK_MISSING_TRANS_BY_LANG);

            $query = $this->createQueryBuilder('t')->select('t');

            $query->where(self::FIELD_TRANSLATION_TYPE . ' = ?1')
                    ->andWhere(self::FIELD_DELETED_DATE . ' IS NULL')
                    ->andWhere(self::FIELD_EXPIRY_DATE . ' IS NULL')
                    ->andWhere(self::FIELD_TRANSLATE_ME . ' = 1')
                    ->andWhere(self::FIELD_RELEASED . ' = 1')
                    ->andWhere(self::FIELD_DONTTRANSLATE . ' IS NULL OR ' . self::FIELD_DONTTRANSLATE . ' = 0')
                    // just get the unflagged translations
                    // 0 = open state, 1 = already sent to translation service
                    ->andWhere(self::FIELD_TS . ' IS NULL OR ' . self::FIELD_TS . ' = 0')
                    ->setParameter(1, $this->translationType);

            $query->andWhere(self::FIELD_LOCALE . ' in (?2)')
                ->setParameter(2, $locales);

            $contents = $query->getQuery()->getResult();
        }
        return count($contents);
    }
}
