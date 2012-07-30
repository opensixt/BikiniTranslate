class opensixt::mysql {
    package {"mysql-cluster-server":
        ensure  => present,
    }

    package {"mysql-cluster-client":
        ensure  => present,
    }

    service {"mysqld":
        ensure => running,
        require => Package["mysql-cluster-server"],
    }
}
