<?php

namespace opensixt\BikiniTranslateBundle\AclHelper;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

use opensixt\BikiniTranslateBundle\Entity\Language as LanguageEntity;

/*
 * @author Paul Seiffert <paul.seiffert@mayflower.de>
 */
class Language extends AbstractHelper
{
    /**
     * Creates a new ACL for the given language.
     *
     * @param LanguageEntity $language
     */
    public function initAclForNewLanguage(LanguageEntity $language)
    {
        $acl = $this->aclProvider->createAcl(ObjectIdentity::fromDomainObject($language));

        $this->addAdminRoleAce($acl);

        $this->aclProvider->updateAcl($acl);
    }
}

