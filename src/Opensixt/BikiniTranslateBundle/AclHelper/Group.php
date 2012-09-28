<?php

namespace Opensixt\BikiniTranslateBundle\AclHelper;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

use Opensixt\BikiniTranslateBundle\Entity\Group as GroupEntity;

/*
 * @author Paul Seiffert <paul.seiffert@mayflower.de>
 */
class Group extends AbstractHelper
{
    /**
     * Creates a new ACL for the given group.
     *
     * @param GroupEntity $group
     */
    public function initAclForNewGroup(GroupEntity $group)
    {
        $acl = $this->aclProvider->createAcl(ObjectIdentity::fromDomainObject($group));

        $this->addAdminRoleAce($acl);

        $this->aclProvider->updateAcl($acl);
    }
}
