<?php

namespace opensixt\BikiniTranslateBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Resources Admin Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class ResourceRepository extends EntityRepository
{
    const ENTITY_RESOURCE  = 'opensixt\BikiniTranslateBundle\Entity\Resource';

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryForAllResources()
    {
        $q = $this->createQueryBuilder('r')
            ->select('r')
            ->orderBy('r.name', 'ASC');
        return $q;
    }
}

