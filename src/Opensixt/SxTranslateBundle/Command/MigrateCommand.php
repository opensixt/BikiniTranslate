<?php

namespace Opensixt\SxTranslateBundle\Command;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Opensixt\BikiniTranslateBundle\Entity\Text;
use Opensixt\BikiniTranslateBundle\Entity\Resource;
use Opensixt\BikiniTranslateBundle\Entity\Language;
use Opensixt\BikiniTranslateBundle\Entity\User;
use Opensixt\BikiniTranslateBundle\Entity\Role;
use Opensixt\BikiniTranslateBundle\Entity\Group;

/**
 * Description of MigrateCommand
 *
 * @author Uwe Pries <uwe.pries@sixt.com>
 */
class MigrateCommand extends ContainerAwareCommand
{
    private $res = array();

    private $locale = array();

    private $user = array();

    private $role = array();

    private $group = array();

    private $resToGroup = array();

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('sxtranslate:import')
            ->setDescription('Import live data from gtxt')
            ->addArgument('dsn', InputArgument::REQUIRED, 'Database DSN (mysql://username:password@host/database)')
            ->addArgument('max_rows', InputArgument::OPTIONAL, 'How many rows do you want to import');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dsn = $input->getArgument('dsn');

        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $userPermissions = $this->getContainer()->get('opensixt.bikini_translate.acl_helper.user');

        $conn = $this->getConnection($dsn);

        $this->loadGroupsAndResources($conn, $manager);

        $this->loadResourcesAndLocales($conn, $manager, $userPermissions);

        // admin role
        $adminRole = $this->getRoleAdmin();
        $manager->persist($adminRole);
        $manager->flush();

        $this->role['admin'] = $adminRole;

        // user role
        $userRole = $this->getRoleUser();
        $manager->persist($userRole);
        $manager->flush();

        $this->role['user'] = $userRole;

        // default group
        $resourceDefault = $this->getResourceDefault();
        $manager->persist($resourceDefault);
        $manager->flush();

        $this->res['default'] = $resourceDefault;

        // default group
        $groupDefault = $this->getGroupDefault();
        $groupDefault->setResources(array($resourceDefault));
        $manager->persist($groupDefault);
        $manager->flush();

        $this->group['default'] = $groupDefault;

        // admin user
        $admin = $this->getUserAdmin();
        $manager->persist($admin);
        $manager->flush();

        $this->user['admin'] = $admin;

        $userPermissions->initAclForNewUser($admin);

        // no user
        $nouser = $this->getUserNoUser();
        $manager->persist($nouser);
        $manager->flush();

        $this->user[''] = $nouser;

        $userPermissions->initAclForNewUser($nouser);

        $i = 0;
        gc_enable(); // Enable Garbage Collector

        $max_rows = intval($input->getArgument('max_rows') ?: 0);
        $sql = "SELECT * FROM gtxt" . ($max_rows > 0 ? " LIMIT {$max_rows}" : "");

        $stmt = $conn->query($sql);
        $texts = array();

        $usersToAcl = array();

        while ($row = $stmt->fetch()) {
            if (isset($this->user[$row['user']])) {
                $user = $this->user[$row['user']];
                $user->addUserLanguage($this->locale[$row['locale']]);
            } else {
                $user = $this->getUserUser(
                    $row['user'],
                    $row['user'],
                    $row['user'] . '@sixt.de',
                    $this->locale[$row['locale']]
                );
                $manager->persist($user);
                $manager->flush();

                $usersToAcl[] = $user;
                $this->user[$row['user']] = $user;
            }

            // get the group by resource name
            $userGroup = $this->resToGroup[$row['module']];
            $user->addUserGroup($userGroup);

            $manager->persist($user);

            $text = $this->getText($row);
            $manager->persist($text);

            // collect texts to init acl after flush
            $texts[] = $text;

            // flush every 500th
            if ($i > 0 && $i % 500 === 0) {
                echo "{$i} rows imported\n";
                $manager->flush();

                foreach ($usersToAcl as $user) {
                    $userPermissions->initAclForNewUser($user);
                }
                $usersToAcl = array();

                gc_collect_cycles();
            }

            $i++;
        }

        $manager->flush();
        echo "{$i} rows imported\n";

        gc_disable(); // Disable Garbage Collector
    }

    /**
     * @param $dsn
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnection($dsn)
    {
        $tokens = parse_url($dsn);

        $config = new Configuration();
        $connectionParams = array(
            'dbname' => ltrim($tokens['path'], '/'),
            'driver' => 'pdo_' . $tokens['scheme'],
            'driverOptions' => array(
                1002 => 'SET NAMES utf8'
            )
        );

        if (!empty($tokens['host'])) {
            $connectionParams['host'] = $tokens['host'];
        }

        if (!empty($tokens['user'])) {
            $connectionParams['user'] = $tokens['user'];
        }

        if (!empty($tokens['pass'])) {
            $connectionParams['password'] = $tokens['pass'];
        }

        return DriverManager::getConnection($connectionParams, $config);
    }

    /**
     * @param \Doctrine\DBAL\Connection $conn
     * @param \Doctrine\ORM\EntityManager $manager
     */
    protected function loadGroupsAndResources(\Doctrine\DBAL\Connection $conn, \Doctrine\ORM\EntityManager $manager)
    {
        $sql = "SELECT a.name `group`, GROUP_CONCAT(DISTINCT c.name) AS `resources`
                FROM groups a
                JOIN group_resource b
                ON b.group_id=a.id
                JOIN resource c
                ON c.id=b.resource_id
                GROUP BY `group`";
        $stmt = $conn->query($sql);

        while ($row = $stmt->fetch()) {
            $group = $this->getGroup($row['group'], 'Description for ' . $row['group']);

            $resources = explode(',', $row['resources']);
            $my_resources = array(); // for this group

            foreach ($resources as $name) {
                if (isset($this->res[$name])) {
                    $my_resources[] = $this->res[$name];
                } else {
                    $resource = $this->getResource($name, 'Description for ' . $name);
                    $manager->persist($resource);

                    $this->res[$name] = $resource;
                    $this->resToGroup[$name] = $group;

                    $my_resources[] = $resource;
                }
            }

            $group->setResources($my_resources);
            $manager->persist($group);
            $manager->flush();

            $this->group[$row['group']] = $group;
        }
    }

    protected function loadResourcesAndLocales(
        \Doctrine\DBAL\Connection $conn,
        \Doctrine\ORM\EntityManager $manager,
        \Opensixt\BikiniTranslateBundle\AclHelper\User $userPermissions
    ) {
        $sql = "SELECT module, GROUP_CONCAT(distinct locale) as locales FROM gtxt group by module";
        $stmt = $conn->query($sql);

        while ($row = $stmt->fetch()) {
            if (isset($this->res[$row['module']])) {
                $res = $this->res[$row['module']];
            } else {
                $res = $this->getResource($row['module'], 'Description for ' . $row['module']);
                $manager->persist($res);
            }

            $locales = explode(',', $row['locales']);
            foreach ($locales as $loc) {
                if (!isset($this->locale[$loc])) {
                    $locale = $this->getLanguage($loc, 'Description for ' . $loc);
                    $manager->persist($locale);
                    $manager->flush();

                    $this->locale[$loc] = $locale;
                }
            }

            $manager->flush();

            $this->res[$row['module']] = $res;
        }
    }

    /**
     * @param $name
     * @param $description
     * @return \Opensixt\BikiniTranslateBundle\Entity\Group
     */
    protected function getGroup($name, $description)
    {
        $group = new Group;

        $group->setName($name);
        $group->setDescription($description);

        return $group;
    }

    /**
     * @param $name
     * @param $description
     * @param $visibility
     * @return \Opensixt\BikiniTranslateBundle\Entity\Resource
     */
    protected function getResource($name, $description, $visibility = true)
    {
        $res = new Resource;

        $res->setName($name);
        $res->setDescription($description);
        $res->setVisibility($visibility);

        return $res;
    }

    /**
     * @param $locale
     * @return \Opensixt\BikiniTranslateBundle\Entity\Language
     */
    protected function getLanguage($locale, $description)
    {
        $language = new Language;

        $language->setLocale($locale);
        $language->setDescription($description);

        return $language;
    }

    /**
     * @return \Opensixt\BikiniTranslateBundle\Entity\Role
     */
    protected function getRoleAdmin()
    {
        $role = new Role;

        $role->setName('Admin');
        $role->setLabel('ROLE_ADMIN');

        return $role;
    }

    /**
     * @return \Opensixt\BikiniTranslateBundle\Entity\Role
     */
    protected function getRoleUser()
    {
        $role = new Role;

        $role->setName('User');
        $role->setLabel('ROLE_USER');

        return $role;
    }

    /**
     * @return \Opensixt\BikiniTranslateBundle\Entity\Group
     */
    protected function getResourceDefault()
    {
        $visibility = false;
        return $this->getResource('Default', 'from initial import', $visibility);
    }

    /**
     * @return \Opensixt\BikiniTranslateBundle\Entity\Group
     */
    protected function getGroupDefault()
    {
        return $this->getGroup('Default', 'from initial import');
    }

    /**
     * @return \Opensixt\BikiniTranslateBundle\Entity\User
     */
    protected function getUserAdmin()
    {
        $admin = new User;

        $admin->setUsername('admin');
        $admin->setPassword('admin');
        $admin->setEmail('bikinitranslate@sixt.de');
        $admin->setIsactive(User::ACTIVE_USER);
        $admin->addUserRole($this->role['admin']);
        $admin->setUserLanguages($this->locale);
        $admin->setUserGroups($this->group);

        return $admin;
    }

    /**
     * @param $username
     * @param $password
     * @param $email
     * @param $locale
     * @return \Opensixt\BikiniTranslateBundle\Entity\User
     */
    protected function getUserUser($username, $password, $email, $locale)
    {
        $user = new User;

        $user->setUsername($username);
        $user->setPassword($password);
        $user->setEmail($email);
        $user->setIsactive(User::ACTIVE_USER);
        $user->addUserRole($this->role['user']);
        $user->addUserLanguage($locale);

        return $user;
    }

    /**
     * @return \Opensixt\BikiniTranslateBundle\Entity\User
     */
    protected function getUserNoUser()
    {
        $user = new User;

        $user->setUsername('bikini');
        $user->setPassword('bikini');
        $user->setEmail('bikini@bikinitranslate');
        $user->setIsactive(User::ACTIVE_USER);
        $user->addUserRole($this->role['user']);
        $user->setUserLanguages($this->locale);

        return $user;
    }

    /**
     * @param $row
     * @return \Opensixt\BikiniTranslateBundle\Entity\Text
     */
    protected function getText($row)
    {
        $text = new Text;

        $text->setSource($row['msgid']);
        $text->setResource($this->res[$row['module']]);
        $text->setLocale($this->locale[$row['locale']]);
        $text->setUser($this->user[$row['user']]);
        $text->setTranslationType(Text::TRANSLATION_TYPE_TEXT);

        // don't add text TRANSLATE_ME
        if ($row['msgstr'] != 'TRANSLATE_ME') {
            $text->setTarget($row['msgstr']);
        }

        // flags
        if ($row['exp']) {
            $text->setExpiryDate(new \DateTime($row['exp']));
        }
        if ($row['rel']) {
            $text->setReleased(true);
        }
        if ($row['hts']) {
            $text->setTranslationService(true);
        }
        if ($row['block']) {
            $text->setBlock(true);
        }
        if ($row['msgstr'] == 'TRANSLATE_ME') {
            $text->setTranslateMe(true);
        }
        if ($row['msgstr'] == 'DONT_TRANSLATE') {
            $text->setDontTranslate(true);
        }

        return $text;
    }
}
