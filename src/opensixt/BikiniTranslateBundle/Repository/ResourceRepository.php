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
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryForAllResources()
    {
        $q = $this->createQueryBuilder('r')
                  ->select('r');
        return $q;
    }
}

