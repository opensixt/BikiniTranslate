class opensixt::apache {
    package {"apache2":
        ensure => present
    }

    service {"apache2":
        ensure  => running,
        hasrestart => true,
        hasstatus => true,
        require => Package["apache2"],
    }

    file {"/etc/apache2/sites-enabled/default":
        ensure => absent,
        notify => Service["apache2"],
    }

    file {"/etc/apache2/sites-available/bikini":
        ensure => present,
        source => "/vagrant/files/etc/apache2/sites-available/bikini",
        require => Package["apache2"],
    }

    file {"/etc/apache2/sites-enabled/bikini":
        ensure => link,
        target => "/etc/apache2/sites-available/bikini",
        require => File["/etc/apache2/sites-available/bikini"],
        notify => Service["apache2"],
    }

    exec {"install_pma":
        command => "/bin/sh -c 'cd /tmp && export http_proxy=wp.sixt.de:8080 && git clone http://github.com/phpmyadmin/phpmyadmin.git && cd phpmyadmin && git checkout STABLE && rm -fr .git && cd .. && cp -R phpmyadmin /srv/www/vhosts/pma'",
        require => Package["php5"],
        unless => "test -d /srv/www/vhosts/pma",
    }

    file {"/etc/apache2/sites-available/pma":
        ensure => present,
        source => "/vagrant/files/etc/apache2/sites-available/pma",
        require => [Package["apache2"], Exec["install_pma"]],
    }

    file {"/etc/apache2/sites-enabled/pma":
        ensure => link,
        target => "/etc/apache2/sites-available/pma",
        require => File["/etc/apache2/sites-available/pma"],
        notify => Service["apache2"],
    }
}