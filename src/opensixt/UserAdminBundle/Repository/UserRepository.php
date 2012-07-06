<?php

namespace opensixt\UserAdminBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * User Administration Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class UserRepository extends EntityRepository
{

    /**
     * @var SecurityContext
     */
    private $_securityContext;

    public function setSecurityContext(SecurityContext $securityContext) {
        $this->_securityContext = $securityContext;
    }

    /**
     * Get list of users from the DB
     *
     * @param string $search
     * @param int $page pagination offset
     * @return array
     */
    public function getUserListWithPagination($limit, $offset)
    {
        $criteria = $this->checkLogedUser();

        $list = $this->findBy(
            $criteria,             // search criteria
            array('id' => 'asc'),  // order by
            $limit,                // limit
            $offset);              // offset
        return $list;
    }

    /**
     * Get count of records in User table
     *
     * @return int
     */
    public function getUserCount()
    {
        $criteria = $this->checkLogedUser();

        if (isset($criteria['id'])) {
            // user without ROLE_ADMIN can view only himself
            $count = 1;
        } else {
            $count = $this->createQueryBuilder('u')
                ->select('COUNT(u)')
                ->getQuery()
                ->getSingleScalarResult();
        }
        return $count;
    }

    private function checkLogedUser()
    {
        $criteria = array();
        // user without ROLE_ADMIN can view only himself
        if (false === $this->_securityContext->isGranted('ROLE_ADMIN')) {
            //$userdata = $this->_securityContext->getToken()->getUser();
            //$criteria['id'] = $userdata->getId();
        }

        return $criteria;
    }

}
