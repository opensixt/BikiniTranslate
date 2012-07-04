<?php

namespace opensixt\UserAdminBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Language Admin Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class LanguageRepository extends EntityRepository
{

    /**
     * Get list of locales from the DB
     *
     * @param int $page pagination current page
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLangListWithPagination($page = 1, $limit, $offset)
    {
        $list = $this->findBy(
            array(),               // search criteria
            array('locale' => 'asc'),  // order by
            $limit,                // limit
            $offset);              // offset
        return $list;
    }

    /**
     * Get count of records in User table
     *
     * @return int
     */
    public function getLangCount()
    {
        $count = $this->createQueryBuilder('l')
            ->select('COUNT(l)')
            ->getQuery()
            ->getSingleScalarResult();
        return $count;
    }
}
