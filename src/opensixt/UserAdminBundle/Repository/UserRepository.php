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

    const USER_ID        = 'u.id';
    const USER_NAME      = 'u.username';
    const USER_EMAIL     = 'u.email';

    /**
     * @var SecurityContext
     */
    private $_securityContext;

    /**
     * @var string
     */
    private $_searchString;

    /**
     *
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     */
    public function setSecurityContext(SecurityContext $securityContext) {
        $this->_securityContext = $securityContext;
    }

    /**
     * Set search string
     *
     * @param string $searchString
     */
    public function setSearchString($searchString)
    {
        $this->_searchString = $searchString;
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
        $query = $this->createQueryBuilder('u')
            ->select('u');

        $this->setQueryParameters($query);

        // pagination limit and offset
        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        $results = $query->getQuery()->getResult();

        return $results;
    }

    /**
     * Get count of records in User table
     *
     * @param string search
     * @return int
     */
    public function getUserCount($search)
    {
        $this->setSearchString($search);

        $query = $this->createQueryBuilder('u')
            ->select('COUNT(u)');

        $this->setQueryParameters($query);

        $count = $query->getQuery()
            ->getSingleScalarResult();


        return $count;
    }

    /**
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param array $parameters
     */
    private function setQueryParameters($query)
    {
        if (!empty($this->_searchString)) {
            $searchString = '%' . $this->_searchString . '%';

            $query->where(self::USER_NAME . ' LIKE ?1')
                ->orWhere(self::USER_EMAIL . ' LIKE ?1')
                ->setParameter(1, $searchString)
                ->orderBy(self::USER_NAME);
        }
    }

    /*private function checkLoggedUser()
    {
        $criteria = array();
        // user without ROLE_ADMIN can view only himself
        //if (false === $this->_securityContext->isGranted('ROLE_ADMIN')) {
            //$userdata = $this->_securityContext->getToken()->getUser();
            //$criteria['id'] = $userdata->getId();
        //}

        return $criteria;
    }*/



}
