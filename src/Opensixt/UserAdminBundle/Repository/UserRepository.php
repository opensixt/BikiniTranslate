<?php

namespace Opensixt\UserAdminBundle\Repository;

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
     * @param array $searchParam
     * @param int $userId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryForUserSearch($searchTerm, $searchParam, $userId)
    {
        $q = $this->createQueryBuilder('u')
            ->select('u, l, g, r')
            ->leftJoin('u.userLanguages', 'l')
            ->leftJoin('u.userGroups', 'g')
            ->leftJoin('u.userRoles', 'r')
            ->orderBy(self::USER_NAME, 'ASC');

        if (!empty($userId) && intval($userId)) {
            $q->where(self::USER_ID . '= ?2')
              ->setParameter(2, $userId);
        } else {
            if (!empty($searchParam['languageId'])) {
                $q->andWhere('l.id = ?4')
                ->setParameter(4, $searchParam['languageId']);
            }
            if (!empty($searchParam['groupId'])) {
                $q->andWhere('g.id = ?5')
                ->setParameter(5, $searchParam['groupId']);
            }
            if (!empty($searchParam['roleId'])) {
                $q->andWhere('r.id = ?6')
                ->setParameter(6, $searchParam['roleId']);
            }

            if (!empty($searchTerm)) {
                $searchTerm = '%' . $searchTerm . '%';

                $q->where(self::USER_NAME . ' LIKE ?1')
                ->orWhere(self::USER_EMAIL . ' LIKE ?1')
                ->setParameter(1, $searchTerm);
            }

        }

        return $q;
    }
}
