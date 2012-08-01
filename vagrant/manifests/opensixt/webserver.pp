class opensixt::webserver {

    class { "apache": }
    class { "apache::php": }

    file {"/etc/apache2/sites-enabled/000-default":
        ensure => absent,
        notify => Service["httpd"],
    }

    apache::vhost { 'bikini.dev':
        priority        => '10',
        port            => '80',
        docroot         => '/srv/www/vhosts/bikini/',
        logroot         => '/var/log/apache2/bikini',
        serveradmin     => 'bikini@opensixt.de',
    }

    $phpmyadmin_preseed = "/var/cache/debconf/phpmyadmin.preseed"

    file {$phpmyadmin_preseed:
        source => "/vagrant/files$phpmyadmin_preseed",
    }

    package {"phpmyadmin":
        ensure => present,
        require => File[$phpmyadmin_preseed],
        responsefile => $phpmyadmin_preseed,
    }

    apache::vhost { 'pma.bikini.dev':
        priority        => '10',
        port            => '80',
        docroot         => '/usr/share/phpmyadmin/',
        logroot         => '/var/log/apache2/pma',
        serveradmin     => 'bikini@opensixt.de',
    }
}