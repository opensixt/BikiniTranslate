class opensixt::mysql {

    include mysqldb

    package {"mysql-server":
        ensure  => present,
    }

    package {"mysql-client":
        ensure  => present,
    }

    service {"mysql":
        ensure => running,
        require => Package["mysql-server"],
    }

    exec {"mysql-init":
        command => "mysql -u root < /vagrant/files/init.sql",
        path    => "/usr/bin",
        onlyif  => "mysql -uroot -e 'exit'",
        require => [Package["mysql-client"],
                    Service["mysql"]],
    }

    mysqldb::create { "bikini":
        schema => "bikini",
        user => "bikini",
        password => "bikini",
        require => Service["mysql"],
    }
}
