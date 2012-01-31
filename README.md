Readme
======

These are custom install scripts that allow you to get a symfony project up in
running within a matter of minutes. It will configure your project as well with
symfony best practices.

Install
=======

    cd ~
    git clone git://github.com/JoshuaEstes/symfony_custom_installer.git
    cd /var/www
    mkdir custom_installer_test
    cd customer_installer_test
    git init
    git submodule add git://github.com/symfony/symfony1.git lib/vendor/symfony
    php lib/vendor/symfony/data/bin/symfony generate:project "ProjectName" --installer="$HOME/symfony_custom_installer/default.php"

Answer the questions and you should be good to go.

facebook.php Features
=====================

    * Generates frontend app
    * Generates frontend module default
    * Copies 404, 500, and other default files for you to edit
    * Adds configureDoctrine function to ProjectConfiguration.class.php
    * Pulls the lastest .gitignore file from https://github.com/github/gitignore
    * Configures databases.yml for you and makes a copy of your databases.yml
    * Adds the submodule https://github.com/facebook/facebook-php-sdk
    * Adds the submodule https://github.com/n1k0/npAssetsOptimizerPlugin
    * Runs git add and git commit so you don't have to ;p

default.php Features
====================

    * Generates frontend app
    * Generates frontend module default
    * Copies 404, 500, and other default files for you to edit
    * Adds configureDoctrine function to ProjectConfiguration.class.php
    * Pulls the lastest .gitignore file from https://github.com/github/gitignore
    * Configures databases.yml for you and makes a copy of your databases.yml
    * Runs git add and git commit so you don't have to ;p
    * Gives you the option to add the following submodules:
        * sfTaskExtraPlugin
        * sfFormExtraPlugin
        * sfDoctrineGuardPlugin
        * sfDoctrineExtraPlugin
        * sfGoogleAnalyticsPlugin
        * sfGoogleWebsiteOptimizerPlugin
        * sfErrorNotifierPlugin
        * npAssetsOptimizerPlugin
    * If you choose to install any of the plugins it will automatically setup the
      files that need to be setup.