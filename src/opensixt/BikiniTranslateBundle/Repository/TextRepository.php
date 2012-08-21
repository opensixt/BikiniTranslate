<?php

namespace opensixt\BikiniTranslateBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

use opensixt\BikiniTranslateBundle\Entity\TextRevision;

/**
 * Text Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class TextRepository extends EntityRepository
{
    const FIELD_ID            = 't.id';
    const FIELD_HASH          = 't.hash';
    const FIELD_SOURCE        = 't.source';
    const FIELD_TARGET        = 'tr.target';
    const FIELD_REVISION_ID   = 't.textRevisionId';
    const FIELD_RESOURCE      = 't.resourceId';
    const FIELD_LOCALE        = 't.localeId';
    const FIELD_USER          = 't.userId';
    const FIELD_EXPIRY_DATE   = 't.expiryDate';
    const FIELD_RELEASED      = 't.released';
    const FIELD_TS            = 't.translationService';
    const FIELD_BLOCK         = 't.block';
    const FIELD_TRANSLATE_ME  = 't.translateMe';
    const FIELD_DONTTRANSLATE = 't.dontTranslate';

    const TASK_MISSING_TRANS_BY_LANG = 0;
    const TASK_SEARCH_PHRASE_BY_LANG = 1;
    const TASK_ALL_CONTENT_BY_LANG   = 2;
    const TASK_ALL_CONTENT_BY_RES    = 3;
    const TASK_SEARCH_FLAGGED_TEXTS  = 4;

    const DOMAIN_TYPE_LANGUAGE = 1;
    const DOMAIN_TYPE_RESOURCE = 2;

    /**
     * @var string
     */
    private $task;

    /**
     * @var array
     */
    private $resources;

    /**
     * @var int
     */
    private $hts;

    /**
     * @var int
     */
    private $locale;

    /**
     * @var array
     */
    private $locales;

    /**
     * @var string
     */
    private $commonLanguage;

    /**
     * @var int
     */
    private $commonLanguageId;

    /**
     * @var string
     */
    private $textRevisionControl;

    private $expiryDate;


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
     *
     * @param int $hts
     */
    public function setHts($hts)
    {
        $this->hts = $hts;
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
     * @param string $textRevisionControl
     */
    public function setTextRevisionControl($textRevisionControl)
    {
        $this->textRevisionControl = $textRevisionControl;
    }

    /**
     *
     * @param string $searchString
     */
    public function setSearchString($searchString)
    {
        $this->searchString = $searchString;
    }

    public function setExpiryDate($date)
    {
        $this->expiryDate = $date;
    }

    /**
     * Sets class attributes
     *
     * @param int $task
     * @param int $locale locale id
     * @param array $resources
     * @param bool $hts
     */
    public function init($task, $locale, $resources, $hts = false)
    {
        $this->setTask($task);
        $this->setLocale($locale);
        $this->setResources($resources);
        $this->setHts($hts);
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
    public function getMissingTranslations()
    {
        $query = $this->createQueryBuilder('t')
            ->select('t, l, r, u, tr')
            ->leftJoin('t.locale', 'l')
            ->leftJoin('t.resource', 'r')
            ->leftJoin('t.user', 'u');

        $this->setQueryParameters($query);

        return $query;
    }

    /**
     * Get search results
     *
     * @return QueryBuilder
     */
    public function getSearchResults()
    {
        $query = $this->createQueryBuilder('t')
            ->select('t, r, l, u, tr')
            ->leftJoin('t.resource', 'r')
            ->leftJoin('t.locale', 'l')
            ->leftJoin('t.user', 'u');

        $this->setQueryParameters($query);

        return $query;
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
                ->select('t, r, tr')
                ->leftJoin('t.resource', 'r');

            $this->setQueryParameters($query);

            $query->andWhere(self::FIELD_RESOURCE . ' = ?3')
                ->setParameter(3, $resource);

            $contents = $query->getQuery()->getArrayResult();
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
                ->select('t, r, tr')
                ->leftJoin('t.resource', 'r');

            $this->setQueryParameters($query);

            $contents = $query->getQuery()->getArrayResult();
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
        // merge all empty translations in destination language
        foreach ($textsDestLang as $txt) {
            if ($txt['translateMe'] == 1) {
                $txtId = $txt['id'];

                // if the list of availabte resources is not set
                if (empty($this->resources)) {
                    throw new \Exception(
                        __METHOD__ . ': resources is not set. Please set it with ' . __CLASS__ . '::setResources() !'
                    );
                }

                // if destination text belongs to available resource
                if (in_array($txt['resourceId'], $this->resources)) {
                    // get text from $sourceData by hash and resource
                    $translation = '';
                    foreach ($sourceData as $src) {
                        if ($src['hash'] == $txt['hash']) {
                            if ($src['resourceId'] == $txt['resourceId'] && $domainType == self::DOMAIN_TYPE_LANGUAGE) {
                                $translation = $src['target'][0]['target'];
                                break;
                            }
                            if ($src['localeId'] == $txt['localeId'] && $domainType == self::DOMAIN_TYPE_RESOURCE) {
                                $translation = $src['target'][0]['target'];
                                break;
                            }
                        }
                    }
                    // if text found, update destination
                    if (!empty($translation)) {
                        $changesCount++;
                        $this->updateText($txtId, $translation);
                    }
                }
            }
        }
        return $changesCount;
    }

    /**
     * Get messages in $locales language(s) for any hash from $texts
     * and return it like array.
     *
     * $resources - array of resources, - optional filter by it
     *
     * @param SlidingPagination $texts
     * @param array $locales
     * @param array $resources
     * @return array
     */
    protected function getMessagesByLanguage(/*SlidingPagination*/ $texts, array $locales, array $resources = array())
    {
        $messages = array();
        $hashes = $this->getHashes($texts);

        if (count($hashes) && count($locales)) {
            $query = $this->createQueryBuilder('t')
                ->select('t, tr')
                ->join('t.target', 'tr')
                ->where(self::FIELD_HASH . ' IN (?1)')
                ->andWhere(self::FIELD_LOCALE . ' IN (?2)')
                ->setParameter(1, $hashes)
                ->setParameter(2, $locales);

            if (count($resources)) {
                $query->andWhere(self::FIELD_RESOURCE . ' IN  (?3)')
                    ->setParameter(3, $resources);
            }

            // needed to access last text revision via target.0.target or $ele['target'][0]['target']
            $query->addOrderBy('tr.id', 'DESC');

            $messages = $query->getQuery()->getResult();
        }

        return $messages;
    }

    /**
     * Get suggegstions (translations with same hash from other resources)
     *
     * @param string $hash md5 string
     * @param int $locale source langiage
     * @param type $resource source resource
     * @return array texts with same hash and language and with another resources
     */
    public function getSuggestionByHashAndLanguage($hash, $locale, $resource, $allresources)
    {
        $suggestions = array();

        if (strlen($hash) && $locale) {
            $query = $this->createQueryBuilder('t')
                ->select('t, r, tr')
                ->leftJoin('t.resource', 'r')
                ->join('t.target', 'tr')
                ->where(self::FIELD_HASH . ' = ?1')
                ->andWhere(self::FIELD_TRANSLATE_ME . ' = 0')
                ->andWhere(self::FIELD_LOCALE . ' = ?2')
                ->andWhere(self::FIELD_RESOURCE . ' != ?3')
                ->andWhere(self::FIELD_RESOURCE . ' in (?4)')
                ->setParameter(1, $hash)
                ->setParameter(2, $locale)
                ->setParameter(3, $resource)
                ->setParameter(4, $allresources);
            $suggestions = $query->getQuery()->getArrayResult();
        }

        return $suggestions;
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
    public function setMessagesInCommonLanguage(&$texts)
    {
        if ($this->locale != $this->commonLanguageId) {
            $textsLang = $this->getMessagesByLanguage($texts, array($this->commonLanguageId));
            foreach ($texts as $text) {
                $message = '';
                foreach ($textsLang as $textLang) {
                    if ($text->getHash() == $textLang->getHash()) {
                        $message = $textLang->getCurrentTarget()->getTarget();
                        break;
                    }
                }
                $text->setTextInCommonLanguage($message);
            }
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
                $query->join('t.target', 'tr', Join::WITH, "tr.target LIKE ?1")
                    ->andWhere(self::FIELD_RESOURCE . ' IN (?2)')
                    ->andWhere(self::FIELD_LOCALE . ' = ?3')
                    ->andWhere(self::FIELD_EXPIRY_DATE . ' IS NULL')
                    ->setParameter(1, $this->searchString)
                    ->setParameter(2, $this->resources)
                    ->setParameter(3, $this->locale);

                break;
            case self::TASK_ALL_CONTENT_BY_LANG:
            case self::TASK_ALL_CONTENT_BY_RES:
                $query->join('t.target', 'tr')
                    ->where(self::FIELD_LOCALE . ' IN (?1)')
                    ->andWhere(self::FIELD_EXPIRY_DATE . ' IS NULL')
                    ->andWhere(self::FIELD_TRANSLATE_ME . ' = 0')
                    ->setParameter(1, $this->locales);

                if (!empty($this->resources)) {
                    $query->andWhere(self::FIELD_RESOURCE . ' IN (?2)')
                    ->setParameter(2, $this->resources);
                }
                if ($this->locale == $this->commonLanguageId) {
                    $query->andWhere(self::FIELD_RELEASED . ' = 1');
                }

                break;
            case self::TASK_SEARCH_FLAGGED_TEXTS:
                $query->join('t.target', 'tr')
                    ->andWhere(self::FIELD_RESOURCE . ' IN (?1)')
                    ->setParameter(1, $this->resources);

                if (!empty($this->locale)) {
                    $query->andWhere(self::FIELD_LOCALE . ' = ?2')
                    ->setParameter(2, $this->locale);
                }
                if (!empty($this->locales)) {
                    $query->andWhere(self::FIELD_LOCALE . ' IN (?3)')
                    ->setParameter(3, $this->locales);
                }

                if (!empty($this->expiryDate)) {
                    // expired texts
                    $query->andWhere(self::FIELD_EXPIRY_DATE . ' <= ?4')
                        ->setParameter(4, $this->expiryDate);
                } else {
                    // non released texts
                    $query->andWhere(self::FIELD_RELEASED . ' IS NULL OR ' . self::FIELD_RELEASED . ' = 0')
                        ->andWhere(self::FIELD_EXPIRY_DATE . ' IS NULL');
                }

                break;
            case self::TASK_MISSING_TRANS_BY_LANG:
            default:
                $query->leftJoin('t.target', 'tr')
                    ->where(self::FIELD_RESOURCE . ' IN (?1)')
                    ->andWhere(self::FIELD_LOCALE . ' = ?2')
                    ->andWhere(self::FIELD_DONTTRANSLATE . ' IS NULL OR ' . self::FIELD_DONTTRANSLATE . ' = 0')
                    ->andWhere(self::FIELD_EXPIRY_DATE . ' IS NULL')
                    ->andWhere(self::FIELD_RELEASED . ' = 1')
                    ->andWhere(self::FIELD_TRANSLATE_ME . ' = 1')
                    ->setParameter(1, $this->resources)
                    ->setParameter(2, $this->locale);
                // just get the unflagged translations
                // 0 = open state
                // 1 = already sent to hts
                //     if ($this->hts === true) {
                    $query->andWhere(self::FIELD_TS . ' IS NULL OR ' . self::FIELD_TS . ' = 0');
                //     }

                break;
        }

        // needed to access last text revision via target.0.target or $ele['target'][0]['target']
        $query->addOrderBy('tr.id', 'DESC');
    }

    /**
     * Updates text table: set target = $text for $id
     *
     * @param int $id
     * @param string $text
     */
    public function updateText($id, $text)
    {
        if ($id) {
            // TODO: $this->_textRevisionControl == 'off'

            $em = $this->getEntityManager();

            $textRep = $em->getRepository('opensixtBikiniTranslateBundle:Text');

            $objText = $textRep->find($id);
            if (is_null($objText)) {
                throw new \Exception(__METHOD__ . ': no such text id: ' . $id);
            }

            $objText->addTarget($text);
            $em->persist($objText);
            $em->flush();
        }
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

        $this->createQueryBuilder('t')
            ->update()
            ->set(self::FIELD_TS, '1')
            ->where(self::FIELD_ID . ' IN (?1)')
            ->setParameter(1, $ids)
            ->getQuery()
            ->execute();
        // TODO: ORM update
        /*foreach ($ids as $id) {
            $objText = $this->_em->find('opensixtBikiniTranslateBundle:Text', $id);
            $objText->setTranslationService(1);
        }
        $this->_em->persist($objText);
        $this->_em->flush();
         */
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
            ->getRepository('opensixtBikiniTranslateBundle:Language');

        $langData = $repository->findBy(array('locale' => $locale));
        return $langData[0]->getId();
    }

    /**
     * @param \opensixt\BikiniTranslateBundle\Entity\TextRevision $target
     * @return \opensixt\BikiniTranslateBundle\Entity\Text[]
     */
    public function findOneByTarget(TextRevision $target)
    {
        $qb = $this->createQueryBuilder('t');
        $qb ->select('t')
            ->join('t.target', 'ta')
            ->where('ta.id = ?1');
        $result = $qb->getQuery()->execute(array('1' => $target->getId()));

        return current($result);
    }
}

