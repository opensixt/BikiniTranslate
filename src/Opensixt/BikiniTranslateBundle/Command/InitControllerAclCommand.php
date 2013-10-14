<?php

namespace Opensixt\BikiniTranslateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class InitControllerAclCommand extends ContainerAwareCommand
{
    /**
     * @var \Symfony\Component\Security\Acl\Model\MutableAclProviderInterface
     */
    private $aclProvider;

    /**
     * Array of routes that should have USER access.
     *
     * @var array
     */
    private $userRoutes = array(
        '_home',
        '_page',
        '_logout',
        '_translate_home',
        '_translate_setlocale',
        '_translate_edittext',
        '_translate_searchstring',
        '_translate_changetext',
        '_translate_cleantext',
        '_translate_copylanguage',
        '_translate_copyresource',
        '_translate_releasetext',
        '_translate_sendtots',
        '_translate_ajax_savetext',
        '_translate_ajax_gettextsbyhash',
        '_wdt',
        '_profiler',
        '_internal', // needed for _profiler
        '_user_admin_home',
        '_admin_user',
        '_admin_user_save',
        '_sxtranslate_getfromts',
        '_sxfreetext_add',
        '_sxfreetext_edit',
        '_sxfreetext_status',
        '_sxmobile_edit',
        '_sxmobile_change',
        '_admin_user',
        '_admin_userlist',
    );

    /**
     * Array of routes that should have ADMIN access only.
     *
     * @var array
     */
    private $adminRoutes = array(
        '_admin_user_create',
        '_admin_user_save_new',
        '_admin_grouplist',
        '_admin_group_create',
        '_admin_group_save_new',
        '_admin_group',
        '_admin_group_save',
        '_admin_langlist',
        '_admin_language_create',
        '_admin_language_save_new',
        '_admin_language',
        '_admin_language_save',
        '_admin_reslist',
        '_admin_resource_create',
        '_admin_resource_save_new',
        '_admin_resource',
        '_admin_resource_save',
    );

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
        foreach ($this->userRoutes as $route) {
            $this->initRouteAclForRole($route, 'ROLE_USER');
            $output->writeln('Configuring access for <info>ROLE_USER</info> to route <info>' . $route . '</info>');
        }
        foreach ($this->adminRoutes as $route) {
            $this->initRouteAclForRole($route, 'ROLE_ADMIN');
            $output->writeln('Configuring access for <info>ROLE_ADMIN</info> to route <info>' . $route . '</info>');
        }
    }

    /**
     * @param string $route
     * @param string $role
     */
    private function initRouteAclForRole($route, $role)
    {
        $objectIdentity = new ObjectIdentity($route, 'route');

        /**
         * Remove any previously configured ACL.
         */
        $this->getAclProvider()->deleteAcl($objectIdentity);

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
