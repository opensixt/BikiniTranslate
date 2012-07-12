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
     * Gets list of texts without translations
     *
     * @param int $locale Locale Id
     * @param int $limit Pagination limit
     * @param int $offset Pagination offset
     */
    public function getMissingTranslations($locale, $limit, $offset)
    {

        $query = $this->createQueryBuilder('t')
                ->select('t, l, r, u')
                ->leftJoin('t.locale', 'l')
                ->leftJoin('t.resource', 'r')
                ->leftJoin('t.user', 'u');

        $this->setQueryParameters($query);
                //->leftJoin('t.target4english', 't', '', 't.localeId=')
                //->where(self::FIELD_LOCALE . ' = ?1')
                //->andWhere(self::FIELD_TARGET . ' = \'TRANSLATE_ME\'')
      //          ->where(self::FIELD_TARGET . ' != \'DONT_TRANSLATE\'')
      //          ->andWhere(self::FIELD_EXP . ' IS NULL')
      //          ->andWhere(self::FIELD_REL . ' IS NOT NULL')

                //->setParameter(1, $locale)

                $query->setMaxResults($limit)
                ->setFirstResult($offset);

        return $query->getQuery()
            //->getResult();//
            ->getArrayResult();
                /*




*/

    }

    /**
     * Get count of records in text table
     *
     * @return int
     */
    public function getTextCount($task, $resources, $hts = false)
    {
        $this->setTask($task);
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
                //->where(self::FIELD_TARGET . ' != \'DONT_TRANSLATE\'')
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
