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

    if $opensixt::devsettings::http_proxy != "" {
        $proxyString = "&& export http_proxy=$opensixt::devsettings::http_proxy "
    } else {
        $proxyString = ""
    }

    exec {"composer_init_project":
        path => "/usr/bin:/bin:/usr/local/bin",
        command => "/bin/sh -c 'cd /srv/www/vhosts/bikini $proxyString && composer install'",
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

    if $opensixt::devsettings::http_proxy != "" {
        exec {"pear-proxy":
            path => "/usr/bin",
            command => "/bin/sh -c 'pear config-set http_proxy $opensixt::devsettings::http_proxy'",
            before => Pear::Package["PHPUnit"],
            require => Pear::Package["PEAR"],
        }
    }

    pear::package { "PEAR": }
    pear::package { "PHPUnit":
        repository => "pear.phpunit.de",
    }
}