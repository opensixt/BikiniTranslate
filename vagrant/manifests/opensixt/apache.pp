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
        notify => Service["apache2"]
    }
}