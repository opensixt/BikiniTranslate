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

    /**
     *
     * @var array
     */
    private $_queryParameters;

    /**
     * Gets list of texts without translations
     *
     * @param int $domain Resource Id
     * @param int $locale Locale Id
     * @param int $limit Pagination limit
     * @param int $offset Pagination offset
     * @param int $hts
     */
    public function getMissingTranslations($domain, $locale, $limit, $offset, $hts = false)
    {

        $query = $this->createQueryBuilder('t')
                ->select('t, l, r, u')
                ->leftJoin('t.locale', 'l')
                ->leftJoin('t.resource', 'r')
                ->leftJoin('t.user', 'u')
                //->leftJoin('t.target4english', 't', '', 't.localeId=')
                //->where(self::FIELD_LOCALE . ' = ?1')
                //->andWhere(self::FIELD_TARGET . ' = \'TRANSLATE_ME\'')
                ->where(self::FIELD_TARGET . ' != \'DONT_TRANSLATE\'')
                ->andWhere(self::FIELD_EXP . ' IS NULL')
                ->andWhere(self::FIELD_REL . ' IS NOT NULL')

                //->setParameter(1, $locale)

                ->setMaxResults($limit)
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

        if (isset($params['page']) && $params['page'] && $params['limit']) {
            if ($params['page'] <= 1) {
                $start = 0;
            } else {
                $start = ($params['page'] - 1)* $params['limit'];
            }

            $sql .= " LIMIT $start, limit";
        }

        return $sql;
*/


    }

    /**
     * Get count of records in User table
     *
     * @return int
     */
    public function getTextCount()
    {
        $criteria = array();//$this->checkLogedUser();

        /*if (isset($criteria['id'])) {
            // user without ROLE_ADMIN can view only himself
            $count = 1;
        } else {*/
            $count = $this->createQueryBuilder('u')
                ->select('COUNT(u)')
                ->getQuery()
                ->getSingleScalarResult();
        //}
        return $count;
    }

    /**
     *
     * @param array $parameters
     */
    public function setQueryParameters($parameters)
    {
        $this->_queryParameters = $parameters;
    }

}
