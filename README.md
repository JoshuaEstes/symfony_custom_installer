Readme
======

Usage:

    php symfony project:generate ProjectName --installer=/path/to/custom_installer.php


Configure Plugin during install
-------------------------------

Create a function with the name `configure[pluginName]($t)` and when the user
installs a plugin that matches that name, the script should custom configure
that plugin. An example of this is done for the sfDoctrineGuardPlugin.

