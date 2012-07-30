class opensixt::php {
    package {"php5":
        ensure => present,
    }

    exec {"composer_install":
        path => "/usr/bin:/bin",
        command => '/bin/bash -c "export http_proxy=http://wp.sixt.de:8080 && cd /usr/local/bin/ && curl -x wp.sixt.de:8080 https://getcomposer.org/installer | php"',
        unless => "/bin/sh -c 'test -d /usr/local/bin/composer.phar'",
        require => Package["php5"],
    }

    exec {"composer_init_project":
        path => "/usr/bin:/bin:/usr/local/bin",
        command => "/bin/sh -c 'export http_proxy=http://wp.sixt.de:8080 && cd /srv/www/vhosts/bikini && composer.phar install'",
        require => Exec["composer_install"],
    }
}