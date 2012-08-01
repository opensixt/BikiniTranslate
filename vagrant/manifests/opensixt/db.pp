class opensixt::db {
    mysql::db { $opensixt::devsettings::db_name:
      user     => $opensixt::devsettings::db_user,
      password => $opensixt::devsettings::db_user_password,
    }
}