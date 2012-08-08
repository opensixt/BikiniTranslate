<?php

namespace opensixt\UserAdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class opensixtUserAdminExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('opensixt.user_admin.controller.user.list.num_items',
                                 $config['user']['list_num_items']);
        $container->setParameter('opensixt.user_admin.controller.group.list.num_items',
                                 $config['group']['list_num_items']);
        $container->setParameter('opensixt.user_admin.controller.language.list.num_items',
                                 $config['language']['list_num_items']);
        $container->setParameter('opensixt.user_admin.controller.resource.list.num_items',
                                 $config['resource']['list_num_items']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
