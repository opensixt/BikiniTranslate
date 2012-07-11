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
    const FIELD_RESOURCE  = 't.resource_id';
    const FIELD_LOCALE    = 't.localeId';
    const FIELD_USER      = 't.user_id';
    const FIELD_EXP       = 't.exp';
    const FIELD_REL       = 't.rel';
    const FIELD_HTS       = 't.hts';
    const FIELD_BLOCK     = 't.block';

    const MISSING_TRANS_BY_LANG = 0;

    /**
     *
     * @var string
     */
    private $_task;


    /**
     *
     * @param string $task
     */
    public function setTask ($task)
    {
        $this->_task = $task;
    }

    /**
     * Gets list of texts without translations
     *
     * @param array $resources Resource Ids
     * @param int $locale Locale Id
     * @param int $limit Pagination limit
     * @param int $offset Pagination offset
     * @param int $hts
     */
    public function getMissingTranslations($resource, $locale, $limit, $offset, $hts = false)
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

        # additional filters
        if (!empty($this->available_modules)) {
            $sql .= ' AND ' . self::FIELD_MODULE . " IN ('$this->available_modules')";
        }

        if (isset($params['domain']) && $params['domain']) {
            $sql .= ' AND ' . self::FIELD_MODULE . "= '$params[domain]'";
        }

        // just get the unflagged translations
        // 0 = open state
        // 1 = already sent to hts
        if ($params['hts'] === true) {
            $sql .= ' AND ' . self::FIELD_HTS . ' IS NULL';
        }
*/

    }

    /**
     * Get count of records in text table
     *
     * @return int
     */
    public function getTextCount($task)
    {
        $this->setTask($task);

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
            $query//->where(self::FIELD_TARGET . ' != \'DONT_TRANSLATE\'')
                ->where(self::FIELD_EXP . ' IS NULL')
                ->andWhere(self::FIELD_REL . ' IS NOT NULL');
            break;

        }

    }



}
