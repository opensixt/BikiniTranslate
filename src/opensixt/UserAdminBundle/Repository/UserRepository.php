<?php

namespace opensixt\UserAdminBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Description of UserRepository
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class UserRepository extends EntityRepository
{

    public function getUserList($limit = null)
    {

        $query = $this->createQueryBuilder('u')
            ->select('u, r')
            ->leftJoin('u.userRoles', 'r')
            ->addOrderBy('u.username', 'ASC');

        if (false === is_null($limit)) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()
            ->getResult();
    }
}
