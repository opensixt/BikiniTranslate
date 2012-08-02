<?php

namespace opensixt\BikiniTranslateBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

use opensixt\BikiniTranslateBundle\Entity\TextRevision;

/**
 * Text Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class TextRepository extends EntityRepository
{
    const FIELD_ID          = 't.id';
    const FIELD_HASH        = 't.hash';
    const FIELD_SOURCE      = 't.source';
    const FIELD_TARGET      = 't.target';
    const FIELD_REVISION_ID = 't.textRevisionId';
    const FIELD_RESOURCE    = 't.resourceId';
    const FIELD_LOCALE      = 't.localeId';
    const FIELD_USER        = 't.userId';
    const FIELD_EXP         = 't.exp';
    const FIELD_REL         = 't.rel';
    const FIELD_HTS         = 't.hts';
    const FIELD_BLOCK       = 't.block';

    const TEXT_EMPTY_VALUE  = 'TRANSLATE_ME';

    const TASK_MISSING_TRANS_BY_LANG = 0;
    const TASK_SEARCH_PHRASE_BY_LANG = 1;
    const TASK_ALL_CONTENT_BY_LANG   = 2;
    const TASK_ALL_CONTENT_BY_RES    = 3;

    const DOMAIN_TYPE_LANGUAGE = 1;
    const DOMAIN_TYPE_RESOURCE = 2;

    /**
     * @var string
     */
    private $_task;

    /**
     * @var array
     */
    private $_resources;

    /**
     * @var int
     */
    private $_hts;

    /**
     * @var int
     */
    private $_locale;

    /**
     * @var array
     */
    private $_locales;

    /**
     * @var string
     */
    private $_commonLanguage;

    /**
     * @var int
     */
    private $_commonLanguageId;

    /**
     * @var string
     */
    private $_textRevisionControl;


    /**
     *
     * @param string $task
     */
    public function setTask($task)
    {
        $this->_task = $task;
    }

    /**
     *
     * @param array $resources
     */
    public function setResources($resources)
    {
        $this->_resources = $resources;
    }

    /**
     *
     * @param int $hts
     */
    public function setHts($hts)
    {
        $this->_hts = $hts;
    }

    /**
     * Sets locale
     *
     * @param int $locale
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    /**
     * Sets locales
     *
     * @param array $locales
     */
    public function setLocales($locales)
    {
        $this->_locales = $locales;
    }

    /**
     *
     * @param string $locale
     */
    public function setCommonLanguage($locale)
    {
        $this->_commonLanguage = $locale;
    }

    /**
     *
     * @param int $id
     */
    public function setCommonLanguageId($id)
    {
        $this->_commonLanguageId = $id;
    }

    /**
     *
     * @param string $textRevisionControl
     */
    public function setTextRevisionControl($textRevisionControl)
    {
        $this->_textRevisionControl = $textRevisionControl;
    }

    /**
     *
     * @param string $searchString
     */
    public function setSearchString($searchString)
    {
        $this->_searchString = $searchString;
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
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param int $limit Pagination limit
     * @param int $offset Pagination offset
     */
    public function getMissingTranslations($limit, $offset)
    {
        $query = $this->createQueryBuilder('t')
            ->select('t, l, r, u, tr')
            ->leftJoin('t.locale', 'l')
            ->leftJoin('t.resource', 'r')
            ->leftJoin('t.user', 'u');

        $this->setQueryParameters($query);

        // pagination limit and offset
        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        $translations = $query->getQuery() //->getResult();//
            ->getArrayResult();

        // set messages in common language for any text in $translations
        $this->setMessagesInCommonLanguage($translations);

        return $translations;
    }

    /**
     * Get search results
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param int $limit Pagination limit
     * @param int $offset Pagination offset
     * @return type
     */
    public function getSearchResults($limit, $offset)
    {
        $query = $this->createQueryBuilder('t')
            ->select('t, r, l, tr')
            ->leftJoin('t.resource', 'r')
            ->leftJoin('t.locale', 'l');

        $this->setQueryParameters($query);

        // pagination limit and offset
        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        $results = $query->getQuery() //->getResult();//
            ->getArrayResult();

        return $results;
    }

    /**
     * Copy language ($langFrom) contents to another language ($langTo)
     * for available resources.
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
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
            $textsDestLang = $this->getMessagesByLanguage($sourceData, array($langTo));
            $translationsCount = $this->updateEmptyTranslations($sourceData, $textsDestLang, self::DOMAIN_TYPE_LANGUAGE);
        }
        return $translationsCount;
    }

    /**
     * Copy resource ($resFrom) contents to another resource ($resTo)
     * for any locales from $languages
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
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
            $textsDestLang = $this->getMessagesByLanguage($sourceData, $languages, array($resTo));
            $translationsCount = $this->updateEmptyTranslations($sourceData, $textsDestLang, self::DOMAIN_TYPE_RESOURCE);
        }
        return $translationsCount;
    }

    /**
     * Get all contents from Text table by resource
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
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
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
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
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
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
            if ($txt['target']['target'] == self::TEXT_EMPTY_VALUE) {
                $txtId = $txt['id'];

                // if the list of availabte resources is not set
                if (empty($this->_resources)) {
                    throw new \Exception(__METHOD__ . ': _resources is not set. Please set it with ' . __CLASS__ . '::setResources() !');
                }

                // if destination text belongs to available resource
                if (in_array($txt['resourceId'], $this->_resources)) {
                    // get text from $sourceData by hash and resource
                    $translation = '';
                    foreach ($sourceData as $src) {
                        if ($src['hash'] == $txt['hash']){
                            if ($src['resourceId'] == $txt['resourceId'] && $domainType == self::DOMAIN_TYPE_LANGUAGE) {
                                $translation = $src['target']['target'];
                                break;
                            }
                            if ($src['localeId'] == $txt['localeId'] && $domainType == self::DOMAIN_TYPE_RESOURCE) {
                                $translation = $src['target']['target'];
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
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param array $texts
     * @param array $locales
     * @param array $resources
     * @return array
     */
    protected function getMessagesByLanguage(array $texts, array $locales, array $resources = array())
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
            $messages = $query->getQuery()->getArrayResult();
        }

        return $messages;
    }

    /**
     * Get array with unique values if key 'hash' from $texts
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param type $texts
     * @return array
     */
    protected function getHashes($texts)
    {
        $hashes = array();
        if (count($texts)) {
            foreach ($texts as $text) {
                $hashes[$text['hash']] = $text['hash'];
            }
        }
        return array_values($hashes);
    }


    /**
     * Set messages in $locale language for any hash from $texts
     * if current locale not equal common language
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param array $texts
     */
    protected function setMessagesInCommonLanguage(&$texts)
    {
        if ($this->_locale != $this->_commonLanguageId) {
            $textsLang = $this->getMessagesByLanguage($texts, array($this->_commonLanguageId));
            foreach ($texts as &$text) {
                $mess = '';
                foreach ($textsLang as $textLang) {
                    if ($text['hash'] == $textLang['hash']) {
                        $mess = $textLang['target']['target'];
                        break;
                    }
                }
                $text['commonLang'] = $mess;
            }
        }
    }

    /**
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param QueryBuilder $query
     */
    protected function setQueryParameters($query)
    {
        // Exceptions
        if (!isset($this->_task)) {
            throw new \Exception(__METHOD__ . ': _task is not set. Please set it with ' . __CLASS__ . '::init() !');
        }


        if ($this->_task == self::TASK_ALL_CONTENT_BY_LANG || $this->_task == self::TASK_ALL_CONTENT_BY_RES) {
            if (!$this->_commonLanguageId) {
                throw new \Exception(__METHOD__ . ': _commonLanguageId is not set. Please set it with ' . __CLASS__ . '::setCommonLanguage() !');
            }
            if (empty($this->_locales)) {
                throw new \Exception(__METHOD__ . ': _locales is not set. Please set it with ' . __CLASS__ . '::setLocales() !');
            }
        }

        switch ($this->_task) {

        case self::TASK_SEARCH_PHRASE_BY_LANG:
            $query->join('t.target', 'tr', Join::WITH , "tr.target LIKE ?1")
                ->andWhere(self::FIELD_RESOURCE . ' IN (?2)')
                ->andWhere(self::FIELD_LOCALE . ' = ?3')
                ->andWhere(self::FIELD_EXP . ' IS NULL')
                ->setParameter(1, $this->_searchString)
                ->setParameter(2, $this->_resources)
                ->setParameter(3, $this->_locale);

            break;

        case self::TASK_ALL_CONTENT_BY_LANG:
        case self::TASK_ALL_CONTENT_BY_RES:
            $query->join('t.target', 'tr', Join::WITH , "tr.target != ?1")
                ->where(self::FIELD_LOCALE . ' IN (?2)')
                ->andWhere(self::FIELD_EXP . ' IS NULL')
                ->setParameter(1, self::TEXT_EMPTY_VALUE)
                ->setParameter(2, $this->_locales);

            if (!empty($this->_resources)) {
                $query->andWhere(self::FIELD_RESOURCE . ' IN (?3)')
                ->setParameter(3, $this->_resources);
            }

            if ($this->_locale == $this->_commonLanguageId) {
                $query->andWhere(self::FIELD_REL . ' IS NOT NULL');
            }

            break;

        case self::TASK_MISSING_TRANS_BY_LANG:
        default:
            $query->join('t.target', 'tr', Join::WITH , "tr.target = ?1")
                ->where(self::FIELD_RESOURCE . ' IN (?2)')
                ->andWhere(self::FIELD_LOCALE . ' = ?3')
                //->where(self::FIELD_TARGET . ' != \'DONT_TRANSLATE\'')
                ->andWhere(self::FIELD_EXP . ' IS NULL')
                ->andWhere(self::FIELD_REL . ' IS NOT NULL')
                ->setParameter(1, self::TEXT_EMPTY_VALUE)
                ->setParameter(2, $this->_resources)
                ->setParameter(3, $this->_locale);
            // just get the unflagged translations
            // 0 = open state
            // 1 = already sent to hts
            if ($this->_hts === true) {
                $query->addWhere(self::FIELD_HTS . ' IS NULL');
            }

            break;
        }
    }

    /**
     * Updates text table: set target = $text for $id
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param int $id
     * @param string $text
     */
    public function updateText($id, $text)
    {
        if ($id) {
            $textRevisionRep = $this->_em->getRepository('opensixtBikiniTranslateBundle:TextRevision');

            if ($this->_textRevisionControl == 'off') {
                // if text_revision_control is 'off', than update table with new data
                $objTextRevision = $textRevisionRep->find($id);
                if (!empty($objTextRevision)) {
                    $objTextRevision->setTarget($text);
                    $this->_em->persist($objTextRevision);
                    $this->_em->flush();
                }

            } else {
                /* if text_revision_control is 'on', than insert a new record into
                   TextRevision table, and update a pointer field in Text */
                $objTextRevision = new TextRevision();
                $objTextRevision->setTextId($id);
                $objTextRevision->setTarget($text);

                $this->_em->persist($objTextRevision);
                $this->_em->flush();
                $newTextId = $objTextRevision->getId();

                if ($newTextId) {
                    // TODO: use ORM
                    $this->createQueryBuilder('t')
                        ->update()
                        ->set(self::FIELD_REVISION_ID, '?1')
                        ->where(self::FIELD_ID . ' = ?2')
                        ->setParameter(1, $newTextId)
                        ->setParameter(2, $id)
                        ->getQuery()
                        ->execute();
                }
            }
        }
    }

    /**
     * Get language id from table Languages by locale
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param string $langId
     */
    public function getIdByLocale($locale)
    {
        if ($locale) {
            $repository = $this->getEntityManager()
                ->getRepository('opensixtBikiniTranslateBundle:Language');

            $langData = $repository->findBy(array('locale' => $locale));
            return $langData[0]->getId();
        }
    }

}
