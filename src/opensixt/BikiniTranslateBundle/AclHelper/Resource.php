<?php

namespace opensixt\BikiniTranslateBundle\AclHelper;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

use opensixt\BikiniTranslateBundle\Entity\Resource as ResourceEntity;

/*
 * @author Paul Seiffert <paul.seiffert@mayflower.de>
 */
class Resource extends AbstractHelper
{
    /**
     * Creates a new ACL for the given resource.
     *
     * @param ResourceEntity $resource
     */
    public function initAclForNewResource(ResourceEntity $resource)
    {
        $acl = $this->aclProvider->createAcl(ObjectIdentity::fromDomainObject($resource));

        $this->addAdminRoleAce($acl);

        $this->aclProvider->updateAcl($acl);
    }
}

