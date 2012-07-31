class mysqldb {

    define grant ($schema, $user, $password) {

        exec { "grant-${name}-db":
            onlyif => "test -d /var/lib/mysql/${schema}",
            unless => "/usr/bin/mysql -u${user} -p${password} ${schema}",
            command => "/usr/bin/mysql -uroot -e \"grant all on ${schema}.* to ${user}@localhost identified by '$password'; grant super on *.* to ${user}@localhost;\"",
            path => $PATH,
            require => [Service["mysqld"],Package["mysql-client"]],
        }
    }

    define create ($schema, $user, $password) {

        exec { "create-${schema}-db":
            unless => "test -d /var/lib/mysql/${schema}",
            command => "/usr/bin/mysql -uroot -e \"create database ${schema};\"",
            path => $PATH,
            require => Package["mysql-client"],
        }

        mysqldb::grant {"grant-$user-$name":
            schema => $schema,
            user => $user,
            password => $password,
        }
    }
}
