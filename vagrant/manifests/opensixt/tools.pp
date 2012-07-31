class opensixt::tools {
    package {["mlocate",
              "zip",
              "unzip",
              "strace",
              "patch",
              "git",
              "vim"]:
        ensure => present,
    }

    exec {"find-utils-updatedb":
        command => "/usr/bin/updatedb &",
        require => Package["mlocate"],
    }
}