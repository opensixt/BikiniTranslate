<?php

namespace Opensixt\BikiniTranslateBundle\AclHelper;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Opensixt\BikiniTranslateBundle\Entity\User as UserEntity;

/*
 * @author Paul Seiffert <paul.seiffert@mayflower.de>
 */
class User extends AbstractHelper
{
    /**
     * Creates a new ACL for the given user. This method is used application-wide to ensure that all new users are
     * created with similar ACEs.
     *
     * @param \Opensixt\BikiniTranslateBundle\Entity\User $user
     */
    public function initAclForNewUser(UserEntity $user)
    {
        $acl = $this->aclProvider->createAcl(ObjectIdentity::fromDomainObject($user));

        $this->addAdminRoleAce($acl);
        $this->addUserAce($acl, $user, array(MaskBuilder::MASK_VIEW, 256));

        $this->aclProvider->updateAcl($acl);
    }
}
