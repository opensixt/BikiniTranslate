<?php

namespace opensixt\BikiniTranslateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * Description of Migrate
 *
 * @author pries
 */
class InitControllerAclCommand extends ContainerAwareCommand
{
    /**
     * @var \Symfony\Component\Security\Acl\Model\MutableAclProviderInterface
     */
    private $aclProvider;

    /**
     *
     */
    protected function configure()
    {
        $this->setName('bikinitranslate:init_controller_acl')
             ->setDescription('Initializes the ACL for controllers/Actions');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initRouteAclForRole('_home', 'ROLE_USER');
        $this->initRouteAclForRole('_page', 'ROLE_USER');
        $this->initRouteAclForRole('_logout', 'ROLE_USER');
        $this->initRouteAclForRole('_translate_home', 'ROLE_USER');
        $this->initRouteAclForRole('_translate_setlocale', 'ROLE_USER');
        $this->initRouteAclForRole('_translate_edittext', 'ROLE_USER');
        $this->initRouteAclForRole('_translate_searchstring', 'ROLE_USER');
        $this->initRouteAclForRole('_translate_changetext', 'ROLE_USER');
        $this->initRouteAclForRole('_translate_cleantext', 'ROLE_USER');
        $this->initRouteAclForRole('_translate_copylanguage', 'ROLE_USER');
        $this->initRouteAclForRole('_translate_copyresource', 'ROLE_USER');
        $this->initRouteAclForRole('_translate_releasetext', 'ROLE_USER');
        $this->initRouteAclForRole('_translate_sendtots', 'ROLE_USER');
        $this->initRouteAclForRole('_wdt', 'ROLE_USER');
        $this->initRouteAclForRole('_profiler', 'ROLE_USER');

        $this->initRouteAclForRole('_user_admin_home', 'ROLE_USER');
        $this->initRouteAclForRole('_admin_userlist', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_user_create', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_user_save_new', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_user', 'ROLE_USER');
        $this->initRouteAclForRole('_admin_user_save', 'ROLE_USER');
        $this->initRouteAclForRole('_admin_grouplist', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_group_create', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_group_save_new', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_group', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_group_save', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_langlist', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_language_create', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_language_save_new', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_language', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_language_save', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_reslist', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_resource_create', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_resource_save_new', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_resource', 'ROLE_ADMIN');
        $this->initRouteAclForRole('_admin_resource_save', 'ROLE_ADMIN');

    }

    /**
     * @param string $route
     * @param string $role
     */
    private function initRouteAclForRole($route, $role)
    {
        $objectIdentity = new ObjectIdentity($route, 'route');
        $acl = $this->getAclProvider()->createAcl($objectIdentity);

        $roleIdentity = new RoleSecurityIdentity($role);

        $acl->insertObjectAce($roleIdentity, MaskBuilder::MASK_VIEW);

        $this->getAclProvider()->updateAcl($acl);
    }

    /**
     * @return \Symfony\Component\Security\Acl\Model\MutableAclProviderInterface
     */
    private function getAclProvider()
    {
        if (null === $this->aclProvider) {
            $this->aclProvider = $this->getContainer()->get('security.acl.provider');
        }
        return $this->aclProvider;
    }
}

