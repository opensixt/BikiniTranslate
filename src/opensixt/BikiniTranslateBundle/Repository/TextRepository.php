<?php

namespace opensixt\BikiniTranslateBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Text Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class TextRepository extends EntityRepository
{
    //const TABLE_NAME    = 'text';

    const FIELD_HASH      = 't.hash';
    const FIELD_SOURCE    = 't.source';
    const FIELD_TARGET    = 't.target';
    const FIELD_RESOURCE  = 't.resourceId';
    const FIELD_LOCALE    = 't.localeId';
    const FIELD_USER      = 't.userId';
    const FIELD_EXP       = 't.exp';
    const FIELD_REL       = 't.rel';
    const FIELD_HTS       = 't.hts';
    const FIELD_BLOCK     = 't.block';

    const MISSING_TRANS_BY_LANG = 0;

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
     * Gets list of texts without translations
     *
     * @param int $limit Pagination limit
     * @param int $offset Pagination offset
     */
    public function getMissingTranslations($limit, $offset)
    {

        $query = $this->createQueryBuilder('t')
                ->select('t, l, r, u')
                ->leftJoin('t.locale', 'l')
                ->leftJoin('t.resource', 'r')
                ->leftJoin('t.user', 'u');

        $this->setQueryParameters($query);

        // pagination limit and offset
        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getQuery() //->getResult();//
            ->getArrayResult();
    }

    /**
     * Get count of records in text table
     *
     * @param int $task
     * @param int locale id
     * @param array $resources
     * @param int $hts
     * @return int texts count
     */
    public function getTextCount($task, $locale, $resources, $hts = false)
    {
        $this->setTask($task);
        $this->setLocale($locale);
        $this->setResources($resources);
        $this->setHts($hts);

        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t)');

        $this->setQueryParameters($query);

        $count = $query->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     *
     * @param array $parameters
     */
    private function setQueryParameters($query)
    {
        //$this->_queryParameters = $parameters;
        switch ($this->_task) {
        default:
        case self::MISSING_TRANS_BY_LANG:
            $query->where(self::FIELD_RESOURCE . ' IN (' . implode(',', $this->_resources) . ')')
                ->andWhere(self::FIELD_LOCALE . "=" . (int)$this->_locale)
                //->where(self::FIELD_TARGET . ' != \'DONT_TRANSLATE\'')
                //->andWhere(self::FIELD_TARGET . ' = \'TRANSLATE_ME\'')
                ->andWhere(self::FIELD_EXP . ' IS NULL')
                ->andWhere(self::FIELD_REL . ' IS NOT NULL');

            // just get the unflagged translations
            // 0 = open state
            // 1 = already sent to hts
            if ($this->_hts === true) {
                $query->addWhere(self::FIELD_HTS . ' IS NULL');
            }
            break;

        }
    }



}
