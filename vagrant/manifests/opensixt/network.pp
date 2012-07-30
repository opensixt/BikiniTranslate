class opensixt::network {
    host { "localhost":
        ip => "127.0.0.1",
        host_aliases => ["localhost.localdomain",
                         "localhost4", "localhost4.localdomain4", "bikini.dev"],
    }

    file { "/etc/apt/apt.conf.d/proxy.conf":
        ensure => present,
        content => "Acquire::http::Proxy \"http://wp.sixt.de:8080\";",
    }

    File["/etc/apt/apt.conf.d/proxy.conf"] -> Package <| |>
}