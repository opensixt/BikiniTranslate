<?php

namespace opensixt\UserAdminBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * User Administration Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class UserRepository extends EntityRepository
{

    /**
     * Get list of users from the DB
     *
     * @param string $search
     * @param int $page pagination offset
     * @return array
     */
    public function getUserListWithPagination($limit, $offset)
    {
        $list = $this->findBy(
            array(),               // search criteria
            array('id' => 'asc'),  // order by
            $limit,                // limit
            $offset);              // offset
        return $list;
    }

    /**
     * Get count of records in User table
     *
     * @return int
     */
    public function getUserCount()
    {
        $count = $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->getQuery()
            ->getSingleScalarResult();
        return $count;
    }

}
