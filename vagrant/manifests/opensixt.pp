include apt::update
Exec { path => ['/usr/local/bin', '/opt/local/bin', '/usr/bin', '/usr/sbin', '/bin', '/sbin'], logoutput => true }
Exec["apt_update"] -> Package <| |>

require opensixt::devsettings

include opensixt::network
include opensixt::tools

include opensixt::webserver

include opensixt::php

class { "mysql": }
class { "mysql::server":
  config_hash => {
    "root_password" => $opensixt::devsettings::db_root_password,
    "etc_root_password" => true,
    }
}

include opensixt::db