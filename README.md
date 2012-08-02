# BikiniTranslate

Translation tool which generates translation files in formats .xliff, .mo/.po, json

## Setup (Tested on Ubuntu)

1.  Clone this repository and fetch submodules:
    ```bash
        git clone git@github.com:opensixt/BikiniTranslate.git
        cd BikiniTranslate
        git submodule init
        git submodule update
    ```

2.  Create copies of .dist-files (without the .dist file extension) and adjust them:
    - vagrant/manifests/opensixt/devsettings.pp.dist
    - app/config/parameters.yml.dist

2.  Install rubygems (required for vagrant) and nfs (for mounting shared folders):
    ```bash
        sudo apt-get install rubygems nfs-kernel-server
    ```

3.  Install Vagrant:
    ```bash
        sudo gem install vagrant
    ```

4.  Create your own development virtual machine:
    ```bash
        cd path/to/repository/clone
        cd vagrant
        vagrant up
    ```
    If there are any yellow or pink lines in the output of the previous command, just re-run puppet configuration:
    ```bash
       vagrant provision
    ```

5.  Create an entry in your ```bash /etc/hosts ``` that points to the vm: (just append the following):
    ```bash
        192.168.10.55   bikini.dev pma.bikini.dev
    ```