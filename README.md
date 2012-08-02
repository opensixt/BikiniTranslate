# BikiniTranslate

Translation tool which generates translation files in formats .xliff, .mo/.po, json

## Setup (Tested on Ubuntu)

1.  Clone this repository

2.  Create copies of .dist-files (without the .dist file extension) and adjust them
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

If there are any yellow or pink lines in the output of this command, just re-run puppet configuration:
```bash
   vagrant provision
```
