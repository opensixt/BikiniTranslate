<?php

namespace Opensixt\BikiniTranslateBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Resources Admin Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class ResourceRepository extends EntityRepository
{
    const FIELD_ID     = 'r.id';
    const FIELD_NAME   = 'r.name';
    const FIELD_DESCR  = 'r.description';

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

    /**
     * execute QueryBuilder from getQueryForAllResources()
     *
     * @return array
     */
    public function getAllResources()
    {
        $result = array();
        $allResources = $this->getQueryForAllResources()->getQuery()
            ->getResult();

        if (count($allResources)) {
            foreach ($allResources as $resource) {
                $result[$resource->getId()] = $resource->getName();
            }
        }
        return $result;
    }
}
