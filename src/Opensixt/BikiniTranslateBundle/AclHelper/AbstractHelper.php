<?php

namespace Opensixt\BikiniTranslateBundle\AclHelper;

use Opensixt\BikiniTranslateBundle\Entity\User;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

abstract class AbstractHelper
{
    /**
     * @var MutableAclProviderInterface
     */
    public $aclProvider;

    /**
     * @param MutableAclInterface $acl
     * @param int|array $masks
     */
    protected function addAdminRoleAce(MutableAclInterface $acl, $masks = array(MaskBuilder::MASK_MASTER))
    {
        $this->addAceForIdentityWithMasks($acl, new RoleSecurityIdentity('ROLE_ADMIN'), $masks);
    }

    /**
     * @param MutableAclInterface $acl
     * @param User $user
     * @param int|array $masks
     */
    protected function addUserAce(MutableAclInterface $acl, User $user, $masks = array(MaskBuilder::MASK_VIEW))
    {
        $this->addAceForIdentityWithMasks(
            $acl,
            new UserSecurityIdentity(
                $user->getUsername(),
                get_class($user)
            ),
            $masks
        );
    }

    /**
     * @param MutableAclInterface $acl
     * @param SecurityIdentityInterface $identity
     * @param array $masks
     */
    public function addAceForIdentityWithMasks(
        MutableAclInterface $acl,
        SecurityIdentityInterface $identity,
        $masks = array()
    ) {
        if (!is_array($masks)) {
            $masks = array($masks);
        }

        $maskBuilder = new MaskBuilder();
        foreach ($masks as $mask) {
            $maskBuilder->add($mask);
        }

        $acl->insertObjectAce($identity, $maskBuilder->get(0));
    }
}
