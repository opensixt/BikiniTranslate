<?php

namespace opensixt\UserAdminBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Groups Admin Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class GroupsRepository extends EntityRepository
{
    /**
     * Get list of groups from the DB
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getGroupListWithPagination($limit, $offset)
    {
        $list = $this->findBy(
            array(),                // search criteria
            array('name' => 'asc'), // order by
            $limit,                 // limit
            $offset);               // offset
        return $list;
    }

    /**
     * Get count of records in Groups table
     *
     * @return int
     */
    public function getGroupCount()
    {
        $count = $this->createQueryBuilder('g')
            ->select('COUNT(g)')
            ->getQuery()
            ->getSingleScalarResult();
        return $count;
    }
}