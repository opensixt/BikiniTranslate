class opensixt::network {
    host { "localhost":
        ip => "127.0.0.1",
        host_aliases => ["localhost.localdomain",
                         "localhost4", "localhost4.localdomain4", "bikini.dev", "pma.bikini.dev"],
    }

    if $opensixt::devsettings::http_proxy != "" {
        file { "/etc/apt/apt.conf.d/proxy.conf":
            ensure => present,
            content => "Acquire::http::Proxy \"$opensixt::devsettings::http_proxy\";",
        }

        File["/etc/apt/apt.conf.d/proxy.conf"] -> Package <| |>
    } else {
        file { "/etc/apt/apt.conf.d/proxy.conf":
            ensure => absent,
        }
    }
}