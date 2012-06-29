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
     * @param type $limit
     * @return array
     */
    public function getGroupList($limit = null)
    {
        $query = $this->createQueryBuilder('g')
            ->select('g')
            ->addOrderBy('g.name', 'ASC');

        if (false === is_null($limit)) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()
            ->getResult();
    }

}