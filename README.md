# BikiniTranslate

Translation tool which generates translation files in formats .xliff, .mo/.po, json

## Setup

1.  Clone this repository and fetch submodules:
    ```bash
        git clone git@github.com:opensixt/BikiniTranslate.git
        cd BikiniTranslate
        git submodule init
        git submodule update
    ```

2.  Create a copy of .dist-file (without the .dist file extension) and adjust it (no need to):
    - ```cp app/config/parameters.yml.dist app/config/parameters.yml```

## Setup in developer VM (Tested on Ubuntu)

3.  Install virtualbox (needed to run the VM), rubygems (required for vagrant) and nfs (for mounting shared folders):
    ```bash
        sudo apt-get install virtualbox rubygems nfs-kernel-server
    ```

4.  Create a copy of .dist-file (without the .dist file extension) and adjust it (no need to):
    - ```cp vagrant/manifests/opensixt/devsettings.pp.dist vagrant/manifests/opensixt/devsettings.pp```

5.  Install Vagrant:
    ```bash
        sudo gem install vagrant
    ```

6.  Create your own development virtual machine:
    ```bash
        cd path/to/repository/clone
        cd vagrant
        vagrant up
    ```
    If there are any yellow or pink lines in the output of the previous command, just re-run puppet configuration:
    ```bash
       vagrant provision
    ```

7.  Create an entry in your ```/etc/hosts ``` that points to the vm: (just append the following):
    ```bash
        192.168.10.55   bikini.dev pma.bikini.dev
    ```

## Setup in your own software stack

8.  Setup the database, the schema and load fixtures
    ```bash
        wget http://getcomposer.org/composer.phar
        php composer.phar install

        mysql -uroot -p -e 'create database bikini character set utf8 default character set utf8 collate utf8_general_ci default collate utf8_general_ci;'
        mysql -uroot -p -e 'grant all on bikini.* to bikini@localhost identified by "bikini";'

        php app/console doctrine:schema:create
        php app/console doctrine:fixtures:load

        php app/console bikinitranslate:init_controller_acl
        php app/console assets:install web --symlink

    ```

9.  Create a vhost, eg "bikini.dev", which points to /path/to/BikiniTranslate/web

10. Create an entry in your ```/etc/hosts ```: (just append the following):
    ```bash
        127.0.0.1   bikini.dev
    ```