<?php

namespace opensixt\BikiniTranslateBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Group Admin Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class GroupRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryForAllGroups()
    {
        $q = $this->createQueryBuilder('g')
            ->select('g')
            ->orderBy('g.name', 'ASC');
        return $q;
    }
}

