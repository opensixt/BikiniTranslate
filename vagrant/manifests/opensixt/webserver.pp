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
        docroot         => '/srv/www/vhosts/bikini/web',
        logroot         => '/srv/www/vhosts/bikini/app/logs',
        serveradmin     => 'bikini@opensixt.de',
    }

    exec { "allow-override-bikini.dev":
        command => "/bin/sh -c 'sed -i \"s/AllowOverride None/AllowOverride All/\" /etc/apache2/sites-enabled/*bikini*'",
        require => Apache::Vhost["bikini.dev"],
        notify => Service["httpd"],
    }

    a2mod { 'rewrite': ensure => present, }

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
        logroot         => '/var/log/',
        serveradmin     => 'bikini@opensixt.de',
    }
}