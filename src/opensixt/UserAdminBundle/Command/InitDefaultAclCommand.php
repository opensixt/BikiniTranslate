<?php

namespace opensixt\UserAdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class InitDefaultAclCommand extends ContainerAwareCommand
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
        $this->setName('useradminbundle:init_default_acl')
             ->setDescription('Initializes admin ACLs.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initAdminAclForClass('opensixt\\BikiniTranslateBundle\\Entity\\Group');
        $this->initAdminAclForClass('opensixt\\BikiniTranslateBundle\\Entity\\User');
        $this->initAdminAclForClass('opensixt\\BikiniTranslateBundle\\Entity\\Language');
        $this->initAdminAclForClass('opensixt\\BikiniTranslateBundle\\Entity\\Resource');
    }

    /**
     * @param string $route
     * @param string $role
     */
    private function initAdminAclForClass($class)
    {
        $objectIdentity = new ObjectIdentity($class, 'class');

        /**
         * Remove any previously configured ACL.
         */
        $this->getAclProvider()->deleteAcl($objectIdentity);

        $acl = $this->getAclProvider()->createAcl($objectIdentity);

        $roleIdentity = new RoleSecurityIdentity('ROLE_ADMIN');

        $acl->insertObjectAce($roleIdentity, MaskBuilder::MASK_MASTER);

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

