<?php

namespace opensixt\BikiniTranslateBundle\Command;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use opensixt\BikiniTranslateBundle\Entity\Text;
use opensixt\BikiniTranslateBundle\Entity\Resource;
use opensixt\BikiniTranslateBundle\Entity\Language;
use opensixt\BikiniTranslateBundle\Entity\User;
use opensixt\BikiniTranslateBundle\Entity\Role;
use opensixt\BikiniTranslateBundle\Entity\Group;

/**
 * Description of Migrate
 *
 * @author pries
 */
class MigrateCommand extends ContainerAwareCommand
{
    private $res = array();

    private $locale = array();

    private $user = array();

    private $role = array();

    private $group = array();

    protected function configure()
    {
        $this
            ->setName('bikinitranslate:import')
            ->setDescription('Import live data from gtxt')
            ->addArgument('dsn', InputArgument::REQUIRED, 'Database DSN (mysql://username:password@host/database)')
            ->addArgument('max_rows', InputArgument::OPTIONAL, 'How many rows do you want to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dsn = $input->getArgument('dsn');

        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $conn = $this->getConnection($dsn);

        $this->loadResourcesAndLocales($conn, $manager);

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
        $groupDefault = $this->getGroupDefault();
        $manager->persist($groupDefault);
        $manager->flush();

        $this->group['default'] = $groupDefault;

        // admin user
        $admin = $this->getUserAdmin();
        $manager->persist($admin);
        $manager->flush();

        $this->user['admin'] = $admin;

        // no user
        $nouser = $this->getUserNoUser();
        $manager->persist($nouser);
        $manager->flush();

        $this->user[''] = $nouser;

        $i = 0;
        gc_enable(); // Enable Garbage Collector

        $max_rows = intval($input->getArgument('max_rows') ?: 0);
        $sql = "SELECT * FROM gtxt" . ($max_rows > 0 ? " LIMIT {$max_rows}" : "");

        $stmt = $conn->query($sql);

        while ($row = $stmt->fetch()) {
            if (!isset($this->user[$row['user']])) {
                $user = $this->getUserUser(
                    $row['user'],
                    $row['user'],
                    $row['user'] . '@sixt.de',
                    $this->locale[$row['locale']]
                );

                $manager->persist($user);
                $manager->flush();

                $this->user[$row['user']] = $user;
            }

            $text = $this->getText($row);
            $manager->persist($text);

            // flush every 500th
            if ($i > 0 && $i % 500 === 0) {
                echo "{$i} rows imported\n";
                $manager->flush();
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

    protected function loadResourcesAndLocales(\Doctrine\DBAL\Connection $conn, \Doctrine\ORM\EntityManager $manager)
    {
        $sql = "SELECT module, GROUP_CONCAT(distinct locale) as locales FROM gtxt group by module";
        $stmt = $conn->query($sql);

        while ($row = $stmt->fetch()) {
            $res = $this->getRessource($row['module'], 'Description for ' . $row['module']);
            $manager->persist($res);

            $locales = explode(',', $row['locales']);
            foreach ($locales as $loc) {
                if (!isset($this->locale[$loc])) {
                    $locale = $this->getLanguage($loc, 'Description for ' . $loc);
                    $manager->persist($locale);

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
     * @return \opensixt\BikiniTranslateBundle\Entity\Resource
     */
    protected function getRessource($name, $description)
    {
        $res = new Resource;

        $res->setName($name);
        $res->setDescription($description);

        return $res;
    }

    /**
     * @param $locale
     * @return \opensixt\BikiniTranslateBundle\Entity\Language
     */
    protected function getLanguage($locale, $description)
    {
        $language = new Language;

        $language->setLocale($locale);
        $language->setDescription($description);

        return $language;
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\Entity\Role
     */
    protected function getRoleAdmin()
    {
        $role = new Role;

        $role->setName('Admin');
        $role->setLabel('ROLE_ADMIN');

        return $role;
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\Entity\Role
     */
    protected function getRoleUser()
    {
        $role = new Role;

        $role->setName('User');
        $role->setLabel('ROLE_USER');

        return $role;
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\Entity\Group
     */
    protected function getGroupDefault()
    {
        $group = new Group;

        $group->setName('Default group');
        $group->setDescription('from initial import');
        $group->setResources($this->res);

        return $group;
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\Entity\User
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
     * @return \opensixt\BikiniTranslateBundle\Entity\User
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
        $user->setUserGroups($this->group);

        return $user;
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\Entity\User
     */
    protected function getUserNoUser()
    {
        $user = new User;

        $user->setUsername('nouser');
        $user->setPassword('nouser');
        $user->setEmail('nouser@sixt.de');
        $user->setIsactive(User::ACTIVE_USER);
        $user->addUserRole($this->role['user']);
        $user->setUserLanguages($this->locale);
        $user->setUserGroups($this->group);

        return $user;
    }

    /**
     * @param $row
     * @return \opensixt\BikiniTranslateBundle\Entity\Text
     */
    protected function getText($row)
    {
        $text = new Text;

        $text->setSource($row['msgid']);
        $text->setResource($this->res[$row['module']]);
        $text->setLocale($this->locale[$row['locale']]);
        $text->setUser($this->user[$row['user']]);

        // don't add text TRANSLATE_ME
        if ($row['msgstr'] != 'TRANSLATE_ME') {
            $text->addTarget($row['msgstr']);
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

