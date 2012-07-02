<?php

namespace opensixt\UserAdminBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * User Administration Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class UserRepository extends EntityRepository
{

    /**
     * Get list of users from the DB
     *
     * @param type $limit
     * @return array
     */
    public function getUserList($limit = null)
    {
        /*if (isset($this->userid) && $this->userid) {
            $query = $this->createQueryBuilder('u')
                ->select('u, r, l')
                ->leftJoin('u.userRoles', 'r')
                ->leftJoin('u.userLanguages', 'l')
                ->where('u.id = ' . $this->userid);
        } else {*/
        $query = $this->createQueryBuilder('u')
            ->select('u, r')
            ->leftJoin('u.userRoles', 'r')
            ->addOrderBy('u.username', 'ASC');
        //}

        if (false === is_null($limit)) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()
            ->getResult();
    }

    /**
     * Update User Table with Request fields
     *
     * @param User $user
     */
    /*public function updateUser($user)
    {
        if ($this->userid) {
            $this->createQueryBuilder('u')
                ->update()
                ->set('u.username', '?1')
                ->set('u.email', '?2')
                ->where('u.id = ?3')
                ->setParameter(1, $user->getUsername())
                ->setParameter(2, $user->getEmail())
                ->setParameter(3, $this->userid)
                ->getQuery()
                ->execute();

        }
    }*/

}
