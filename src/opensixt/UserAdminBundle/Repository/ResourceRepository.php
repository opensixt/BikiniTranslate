<?php

namespace opensixt\UserAdminBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Resources Admin Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class ResourceRepository extends EntityRepository
{
    /**
     * Get list of resources from the DB
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getResourceListWithPagination($limit, $offset)
    {
        $list = $this->findBy(
            array(),                // search criteria
            array('name' => 'asc'), // order by
            $limit,                 // limit
            $offset);               // offset
        return $list;
    }

    /**
     * Get count of records in Resource table
     *
     * @return int
     */
    public function getResourceCount()
    {
        $count = $this->createQueryBuilder('r')
            ->select('COUNT(r)')
            ->getQuery()
            ->getSingleScalarResult();
        return $count;
    }
}
