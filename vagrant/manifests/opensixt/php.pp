class opensixt::php {
    package {["php5", "php5-mysql", "php5-xdebug"]:
        ensure => present,
        require => Class["mysql"],
        notify => Service["httpd"],
    }

    class {"composer":
      target_dir      => '/usr/local/bin',
      composer_file   => 'composer',
      download_method => 'curl',
      logoutput       => false
    }

    exec {"composer_init_project":
        path => "/usr/bin:/bin:/usr/local/bin",
        command => "/bin/sh -c 'cd /srv/www/vhosts/bikini && composer install'",
        require => [Class["composer"], Package["git"]],
    }

    exec {"build_symfony_db":
        path => "/usr/bin",
        command => "/srv/www/vhosts/bikini/app/console --force doctrine:database:drop \
                    && /srv/www/vhosts/bikini/app/console doctrine:database:create \
                    && /srv/www/vhosts/bikini/app/console doctrine:schema:create \
                    && /srv/www/vhosts/bikini/app/console doctrine:fixtures:load",
        require => [Exec["composer_init_project"]],
    }

    # PEAR
    if $opensixt::devsettings::http_proxy != "" {
        file {["/home/vagrant/.pearrc", "/root/.pearrc"]:
            content => "a:1:{s:10:\"http_proxy\";s:23:\"$opensixt::devsettings::http_proxy\";}",
            before => Package["PEAR"],
        }
    }

    pear::package { "PEAR": }
    pear::package { "PHPUnit":
        repository => "pear.phpunit.de",
    }
}