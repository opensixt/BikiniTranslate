class opensixt::php {
    package {["php5", "php5-mysql"]:
        ensure => present,
        require => Package["mysql-client"],
    }

    exec {"composer_install":
        path => "/usr/bin:/bin",
        command => '/bin/bash -c "export http_proxy=http://wp.sixt.de:8080 && export HTTP_PROXY=http://wp.sixt.de:8080 && cd /usr/local/bin/ && curl -x wp.sixt.de:8080 https://getcomposer.org/installer | php"',
        unless => "/bin/sh -c 'test -d /usr/local/bin/composer.phar'",
        require => Package["php5"],
    }

    exec {"composer_init_project":
        path => "/usr/bin:/bin:/usr/local/bin",
        command => "/bin/sh -c 'cd /srv/www/vhosts/bikini && export http_proxy=http://wp.sixt.de:8080 && composer.phar install'",
        require => [Exec["composer_install"], Package["git"]],
    }

    exec {"build_symfony_db":
        path => "/usr/bin",
        command => "/srv/www/vhosts/bikini/app/console --force doctrine:database:drop \
                    && /srv/www/vhosts/bikini/app/console doctrine:database:create \
                    && /srv/www/vhosts/bikini/app/console doctrine:schema:create \
                    && /srv/www/vhosts/bikini/app/console doctrine:fixtures:load",
        require => [Exec["composer_init_project"]],
    }
}