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
use opensixt\BikiniTranslateBundle\Entity\Groups;

/**
 * Description of Migrate
 *
 * @author pries
 */
class MigrateCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('bikinitranslate:import')
            ->setDescription('Import live data from gtxt')
            ->addArgument('url', InputArgument::OPTIONAL, 'Database url?')
            ->addArgument('max_rows', InputArgument::OPTIONAL, 'How many rows do you want to import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $max_rows = intval($input->getArgument('max_rows') ?: 0);

        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $tokens = parse_url($input->getArgument('url'));

        $config = new Configuration();
        $connectionParams = array(
            'dbname' => ltrim($tokens['path'], '/'),
            'user' => $tokens['user'],
            'password' => $tokens['pass'],
            'host' => $tokens['host'],
            'driver' => 'pdo_' . $tokens['scheme'],
            'driverOptions' => array(
                1002 => 'SET NAMES utf8'
            )
        );

        $conn = DriverManager::getConnection($connectionParams, $config);

        $grouped = array(
            'res' => array(),
            'locale' => array(),
            'user' => array(),
        );

        $sql = "SELECT module, GROUP_CONCAT(distinct locale) as locales FROM gtxt group by module";
        $stmt = $conn->query($sql);
        while ($row = $stmt->fetch()) {
            $res = new Resource;

            $res->setName($row['module']);
            $res->setDescription($row['module']);

            $locales = explode(',', $row['locales']);
            foreach ($locales as $loc) {
                if (!isset($grouped['locale'][$loc])) {
                    $locale = new Language;
                    $locale->setLocale($loc);
                    $locale->setDescription($loc);

                    $manager->persist($locale);

                    $grouped['locale'][$loc] = $locale;
                }
            }

            $manager->persist($res);
            $manager->flush();

            $grouped['res'][$row['module']] = $res;
        }

        $sql = "SELECT * FROM gtxt" . ($max_rows > 0 ? " LIMIT {$max_rows}" : "");
        $stmt = $conn->query($sql);

        // default role
        $role = new Role;
        $role->setName('User');
        $role->setLabel('ROLE_USER');
        $manager->persist($role);
        $manager->flush();

        // default group
        $defaultgroup = new Groups;
        $defaultgroup->setName('Default group');
        $defaultgroup->setDescription('from initial import');
        $defaultgroup->setResources($grouped['res']);

        $manager->persist($defaultgroup);
        $manager->flush();

        // no user
        $nouser = new User;
        $nouser->setUsername('nouser');
        $nouser->setPassword('nouser');
        $nouser->setEmail('nouser@sixt.de');
        $nouser->setIsactive(User::ActiveUser);
        $nouser->addUserRole($role);
        $nouser->setUserLanguages($grouped['locale']);
        $nouser->setUserGroups(array($defaultgroup));

        $manager->persist($nouser);
        $manager->flush();

        $grouped['user'][''] = $nouser;

        $i = 0;
        gc_enable(); // Enable Garbage Collector

        while ($row = $stmt->fetch()) {
            $text = new Text;

            if (!isset($grouped['user'][$row['user']])) {
                $user = new User;

                $user->setUsername($row['user']);
                $user->setPassword($row['user']);
                $user->setEmail($row['user'] . '@sixt.de');
                $user->setIsactive(User::ActiveUser);
                $user->addUserRole($role);
                $user->addUserLanguage($grouped['locale'][$row['locale']]);
                $user->setUserGroups(array($defaultgroup));

                $manager->persist($user);
                $manager->flush();

                $grouped['user'][$row['user']] = $user;
            }

            $text->setSource($row['msgid']);
            $text->setResource($grouped['res'][$row['module']]);
            $text->setLocale($grouped['locale'][$row['locale']]);
            $text->setUser($grouped['user'][$row['user']]);
            $text->addTarget($row['msgstr']);

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

}
