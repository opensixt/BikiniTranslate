<?php

namespace opensixt\BikiniTranslateBundle\Acl;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;

use opensixt\BikiniTranslateBundle\Entity\User;

/**
 * @author Paul Seiffert <paul.seiffert@mayflower.de>
 */
class UserPermissions
{
    /** @var \Symfony\Component\Security\Acl\Model\MutableAclProviderInterface */
    public $aclProvider;

    /**
     * Creates a new ACL for the given user. This method is used application-wide to ensure that all new users are
     * created with similar ACEs.
     *
     * @param \opensixt\BikiniTranslateBundle\Entity\User $user
     */
    public function initAclForNewUser(User $user)
    {
        $acl = $this->aclProvider->createAcl(ObjectIdentity::fromDomainObject($user));

        $this->addAdminRoleAce($acl);
        $this->addUserRightsAce($acl, $user);

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * @param MutableAclInterface $acl
     */
    private function addAdminRoleAce(MutableAclInterface $acl)
    {
        $acl->insertObjectAce(new RoleSecurityIdentity('ROLE_ADMIN'), MaskBuilder::MASK_MASTER);
    }

    /**
     * @param MutableAclInterface $acl
     * @param User $user
     */
    private function addUserRightsAce(MutableAclInterface $acl, User $user)
    {
        $userIdentity = new UserSecurityIdentity($user->getUsername(), get_class($user));

        $maskBuilder = new MaskBuilder();
        $maskBuilder->add('view')
                    ->add(256);

        $acl->insertObjectAce($userIdentity, $maskBuilder->get());
    }
}

