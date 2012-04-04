<?php

/**
 * This will setup and install an sf2 project in the current
 *
 * Usage: php /path/to/this/script.php
 *
 * @package
 * @subpackage
 * @author     Joshua Estes
 * @copyright  2012
 * @version    0.1.0
 * @category
 * @license
 *
 */

$symfonyVersion = '2.0.12';

// Download sf2 standard edition
exec(sprintf('wget --no-check-certificate http://symfony.com/download?v=Symfony_Standard_Vendors_%s.tgz -O sf2.tgz',$symfonyVersion));

// Extract contents of file
exec('tar -xzf sf2.tgz');

// remove the file
exec('rm sf2.tgz');

// Move the Symfony folder to the current folder
exec('mv Symfony/* .');
exec('rm -r Symfony/');

// Download the gitignore from github
exec(sprintf('wget --no-check-certificate https://raw.github.com/github/gitignore/master/Symfony2.gitignore -O .gitignore'));

// Copy parameters.ini
exec('cp app/config/parameters.ini app/config/parameters.ini.dist');

// Init git and stuff
exec('git init');
exec('git add .');
exec('git commit -m "Initial commit"');

// install vendor information
exec('php bin/vendors install');