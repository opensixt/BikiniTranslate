class opensixt::mysql {

    include mysqldb

    package {"mysql-server":
        ensure  => present,
    }

    package {"mysql-client":
        ensure  => present,
    }

    service {"mysqld":
        ensure => running,
        require => Package["mysql-server"],
    }

    mysqldb::create { "bikini":
        schema => "bikini",
        user => "bikini",
        password => "",
        require => Service["mysqld"],
    }
}
