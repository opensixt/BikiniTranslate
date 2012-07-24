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

    const MISSING_TRANS_BY_LANG = 0;
    const SEARCH_PHRASE_BY_LANG = 1;

    const SEARCH_EXACT = 1;
    const SEARCH_LIKE = 2;

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
     * @var string
     */
    private $_searchString;


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
     *
     * @param string $locale
     */
    public function setCommonLanguage($locale)
    {
        $this->_commonLanguage = $locale;
        $this->_commonLanguageId = $this->getIdByLocale($locale);
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
     * Get messages in $locale language for any hash from $texts
     * and return it like array
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param array $texts
     * @param string $locale
     * @return array
     */
    private function getMessagesByLanguage($texts, $locale)
    {
        $langId = $this->getIdByLocale($locale); // language id by locale
        $textsLang = array();

        if (count($texts) && $langId){
            $hashes = array();
            foreach ($texts as $text) {
                $hashes[] = $text['hash'];
            }
            array_unique($hashes);

            $query = $this->createQueryBuilder('t')
                ->select('t, tr')
                ->join('t.target', 'tr')
                ->where(self::FIELD_HASH . ' IN (?1)')
                ->andWhere(self::FIELD_LOCALE . '= ?2')
                ->setParameter(1, $hashes)
                ->setParameter(2, $langId);
            $textsLang = $query->getQuery()->getArrayResult();
        }

        return $textsLang;
    }

    /**
     * Set messages in $locale language for any hash from $texts
     * if current locale not equal common language
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param array $texts
     */
    private function setMessagesInCommonLanguage(&$texts)
    {
        if (!$this->_commonLanguageId) {
            throw new \Exception(__METHOD__ . ': _commonLangauge is not set. Please set it with ' . __CLASS__ . '::setCommonLanguage() !');
        }

        if ($this->_locale != $this->_commonLanguageId) {
            $textsLang = $this->getMessagesByLanguage($texts, $this->_commonLanguage);
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
     * @param array $parameters
     */
    private function setQueryParameters($query)
    {
        // Exceptions
        if (!isset($this->_task)) {
            throw new \Exception(__METHOD__ . ': _task is not set. Please set it with ' . __CLASS__ . '::init() !');
        }

        if ($this->_task == self::SEARCH_PHRASE_BY_LANG || $this->_task == self::MISSING_TRANS_BY_LANG) {
            if (!$this->_locale) {
                throw new \Exception(__METHOD__ . ': _locale is not set. Please set it with ' . __CLASS__ . '::init() !');
            }
            if (empty($this->_resources)) {
                throw new \Exception(__METHOD__ . ': _resources is not set. Please set it with ' . __CLASS__ . '::init() !');
            }
        }

        if ($this->_task == self::SEARCH_PHRASE_BY_LANG) {
            if (!$this->_searchString) {
                throw new \Exception(__METHOD__ . ': _searchString is not set. Please set it with ' . __CLASS__ . '::setSearchParameters() !');
            }
        }

        switch ($this->_task) {

        case self::SEARCH_PHRASE_BY_LANG:
            $query->join('t.target', 'tr', Join::WITH , "tr.target LIKE ?1")
                ->andWhere(self::FIELD_RESOURCE . ' IN (?2)')
                ->andWhere(self::FIELD_LOCALE . ' = ?3')
                ->andWhere(self::FIELD_EXP . ' IS NULL')
                ->setParameter(1, $this->_searchString)
                ->setParameter(2, $this->_resources)
                ->setParameter(3, $this->_locale);

            break;

        case self::MISSING_TRANS_BY_LANG:
        default:
            $query->join('t.target', 'tr', Join::WITH , "tr.target = 'TRANSLATE_ME'")
                ->where(self::FIELD_RESOURCE . ' IN (?1)')
                ->andWhere(self::FIELD_LOCALE . ' = ?2')
                //->where(self::FIELD_TARGET . ' != \'DONT_TRANSLATE\'')
                ->andWhere(self::FIELD_EXP . ' IS NULL')
                ->andWhere(self::FIELD_REL . ' IS NOT NULL')
                ->setParameter(1, $this->_resources)
                ->setParameter(2, $this->_locale);
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
     * Set search parameters: searchPhrase and search mode
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param string $searchPhrase
     * @param int $mode
     */
    public function setSearchParameters($searchPhrase, $searchMode = self::SEARCH_EXACT)
    {
        if ($searchMode == self::SEARCH_LIKE) {
            $searchPhrase = preg_replace('/\s+/', ' ', $searchPhrase);
            $searchPhrase = str_replace(' ', '%', $searchPhrase);
        }
        $searchPhrase = '%' . $searchPhrase . '%';
        //TODO: sanitize input, fulltext search (MATCH...AGAINST....)
        $this->setSearchString($searchPhrase);
    }

    /**
     * Updates text table: set target = $text for $id
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param int $id
     * @param string $text
     */
    public function updateText ($id, $text)
    {
        // Exception
        if (!isset($this->_textRevisionControl)) {
            throw new \Exception(__METHOD__ . ': _textRevisionControl is not set. Please set it with ' . __CLASS__ . '::setTextRevisionControl() !');
        }

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
                 * TextRevision table, and update a pointer field in Text */
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
