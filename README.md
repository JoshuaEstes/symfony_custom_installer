Readme
======

Usage:

    php symfony project:generate ProjectName --installer=/path/to/custom_installer.php


Configure Plugin during install
-------------------------------

Create a function with the name `configure[pluginName]($t)` and when the user
installs a plugin that matches that name, the script should custom configure
that plugin. An example of this is done for the sfDoctrineGuardPlugin.

Advanced Usage
--------------

Open up your php.ini and make sure you have allow_url_include = On

php symfony project:generate ProjectName --installer=https://raw.github.com/JoshuaEstes/symfony_custom_installer/master/symfony_custom_installer.php
