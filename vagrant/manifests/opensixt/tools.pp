class opensixt::tools {
    package {["mlocate",
              "zip",
              "unzip",
              "strace",
              "patch"]:
        ensure => present,
    }

    exec {"find-utils-updatedb":
        command => "/usr/bin/updatedb &",
        require => Package["mlocate"],
    }
}