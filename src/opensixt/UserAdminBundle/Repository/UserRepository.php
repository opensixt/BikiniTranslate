<?php

namespace opensixt\UserAdminBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * User Administration Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 * @author Paul Seiffert <paul.seiffert@mayflower.de>
 */
class UserRepository extends EntityRepository
{
    const USER_ID        = 'u.id';
    const USER_NAME      = 'u.username';
    const USER_EMAIL     = 'u.email';

    /**
     * @param string $searchTerm
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryForUserSearch($searchTerm)
    {
        $q = $this->createQueryBuilder('u')
            ->select('u')
            ->orderBy(self::USER_NAME, 'ASC');

        if (!empty($searchTerm)) {
            $searchTerm = '%' . $searchTerm . '%';

            $q->where(self::USER_NAME . ' LIKE ?1')
              ->orWhere(self::USER_EMAIL . ' LIKE ?1')
              ->setParameter(1, $searchTerm);
        }
        return $q;
    }
}

