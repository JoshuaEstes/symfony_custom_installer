<?php

/**
 * This will setup a project to use apostrophe
 *
 * @package
 * @subpackage
 * @author     Joshua Estes <Joshua.Estes@iostudio.com>
 * @copyright  iostudio 2012
 * @version    0.1.0
 * @category
 * @license
 *
 */
/* @var $this sfGenerateProjectTask */


// generate app
$this->runTask('generate:app', 'frontend');
$this->setConfiguration($this->createConfiguration('frontend', 'dev'));
$this->getFilesystem()->mkdirs(sfConfig::get('sf_app_module_dir') . '/a/templates');


/**
 * Download a .gitignore file
 */
$this->getFilesystem()->execute(sprintf('wget https://raw.github.com/github/gitignore/master/Symfony.gitignore -O %s/.gitignore', sfConfig::get('sf_root_dir')));
$this->getFilesystem()->execute('git add .; git commit -m "added gitignore file"');

/**
 * Install all the needed plugins
 */
// apostrophePlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostrophePlugin.git plugins/apostrophePlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostrophePlugin"');
// apostropheBlogPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostropheBlogPlugin.git plugins/apostropheBlogPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostropheBlogPlugin"');
// apostropheExtraSlotsPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostropheExtraSlotsPlugin.git plugins/apostropheExtraSlotsPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostropheExtraSlotsPlugin"');
// apostrophePeoplePlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostrophePeoplePlugin.git plugins/apostrophePeoplePlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostrophePeoplePlugin"');
// apostropheCkEditorPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostropheCkEditorPlugin.git plugins/apostropheCkEditorPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostropheCkEditorPlugin"');
// apostropheMysqlSearchPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostropheMysqlSearchPlugin.git plugins/apostropheMysqlSearchPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostropheMysqlSearchPlugin"');
// apostropheHTML5Plugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostropheHTML5Plugin.git plugins/apostropheHTML5Plugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostropheHTML5Plugin"');
// apostropheImporterPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostropheImportersPlugin.git plugins/apostropheImportersPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostropheImportersPlugin"');
// apostropheFeedbackPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostropheFeedbackPlugin.git plugins/apostropheFeedbackPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostropheFeedbackPlugin"');
// apostropheAwesomeLoginPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostropheAwesomeLoginPlugin.git plugins/apostropheAwesomeLoginPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostropheAwesomeLoginPlugin"');

// aS3StreamWrapperPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/punkave/aS3StreamWrapper.git plugins/aS3StreamWrapperPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added aS3StreamWrapperPlugin"');

// sfDoctrineActAsTaggablePlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfDoctrineActAsTaggablePlugin.git plugins/sfDoctrineActAsTaggablePlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added sfDoctrineActAsTaggablePlugin"');

// sfDoctrineGuardPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfDoctrineGuardPlugin.git plugins/sfDoctrineGuardPlugin');
$contents = file_get_contents(sfConfig::get('sf_apps_dir') . '/frontend/lib/myUser.class.php');
$contents = preg_replace('/sfBasicSecurityUser/', 'aSecurityUser', $contents);
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/lib/myUser.class.php', $contents);
$this->getFilesystem()->execute('git add .;git commit -m "added sfDoctrineGuardPlugin"');

// sfFeed2Plugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfFeed2Plugin.git plugins/sfFeed2Plugin');
$this->getFilesystem()->execute('git add .;git commit -m "added sfFeed2Plugin"');

// sfSyncContentPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfSyncContentPlugin.git plugins/sfSyncContentPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added sfSyncContentPlugin"');

// sfTaskExtraPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfTaskExtraPlugin.git plugins/sfTaskExtraPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added sfTaskExtraPlugin"');

// sfWebBrowserPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfWebBrowserPlugin.git plugins/sfWebBrowserPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added sfWebBrowserPlugin"');

// apostropheMediaEnhancementsPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/apostropheMediaEnhancementsPlugin.git plugins/apostropheMediaEnhancementsPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added apostropheMediaEnhancementsPlugin"');

// sfJqueryReloadedPlugin
$this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfJqueryReloadedPlugin.git plugins/sfJqueryReloadedPlugin');
$this->getFilesystem()->execute('git add .;git commit -m "added sfJqueryReloadedPlugin"');

/**
 * Enable the plugins
 */
sfSymfonyPluginManager::enablePlugin('sfDoctrineGuardPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('sfDoctrineActAsTaggablePlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('sfTaskExtraPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('sfWebBrowserPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('sfFeed2Plugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('sfSyncContentPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('sfJqueryReloadedPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('apostrophePlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('apostropheBlogPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('apostropheExtraSlotsPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('apostropheFeedbackPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('apostropheImportersPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('apostropheMysqlSearchPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('apostropheAwesomeLoginPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('apostropheCkEditorPlugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('apostropheHTML5Plugin', sfConfig::get('sf_config_dir'));
sfSymfonyPluginManager::enablePlugin('apostrophePeoplePlugin', sfConfig::get('sf_config_dir'));
$this->getFilesystem()->execute('git add .; git commit -m "Enabled plugins"');
$this->reloadTasks();

/**
 * Manipulate the ProjectConfiguration.class.php
 */
$source = sfClassManipulator::fromFile(sfConfig::get('sf_config_dir') . '/ProjectConfiguration.class.php');
$source->wrapMethod('setup', <<<EOF
set_include_path(sfConfig::get('sf_lib_dir') . '/vendor' . PATH_SEPARATOR . get_include_path());
EOF
    , "");
$source->save();
$this->getFilesystem()->execute('git add .; git commit -m "Added lib/vendor to include path"');

/**
 * Copy databases.yml
 */
$host = $this->ask('host (default: 127.0.0.1)', 'QUESTION', '127.0.0.1');
$dbname = $this->ask('dbname (default: apostrophe)', 'QUESTION', 'apostrophe');
$username = $this->ask('username (default: root)', 'QUESTION', 'root');
$password = $this->ask('password (default: root)', 'QUESTION', 'root');
$databasesYml = <<<EOF
all:
  doctrine:
    class:        sfDoctrineDatabase
    param:
      dsn:      mysql:dbname=%DBNAME%;host=%HOST%
      username: %USERNAME%
      password: %PASSWORD%
      # You need these for non-latin character sets and full I18N
      encoding: utf8
      attributes:
        DEFAULT_TABLE_TYPE: INNODB
        DEFAULT_TABLE_CHARSET: utf8
        DEFAULT_TABLE_COLLATE: utf8_general_ci

test:
  doctrine:
    class:        sfDoctrineDatabase
    param:
      dsn:      mysql:dbname=%DBNAME%_test;host=%HOST%
      username: %USERNAME%
      password: %PASSWORD%
      # You need these for non-latin character sets and full I18N
      encoding: utf8
      attributes:
        DEFAULT_TABLE_TYPE: INNODB
        DEFAULT_TABLE_CHARSET: utf8
        DEFAULT_TABLE_COLLATE: utf8_general_ci
EOF;
file_put_contents(sfConfig::get('sf_config_dir') . '/databases.yml', strtr($databasesYml,array(
    '%DBNAME%' => $dbname,
    '%HOST%' => $host,
    '%USERNAME%' => $username,
    '%PASSWORD%' => $password,
)));
file_put_contents(sfConfig::get('sf_config_dir') . '/databases.yml.example', strtr($databasesYml,array(
    '%DBNAME%' => $dbname,
    '%HOST%' => $host,
    '%USERNAME%' => 'root',
    '%PASSWORD%' => 'root',
)));
$this->getFilesystem()->execute('git add .; git commit -m "Updated databases.yml"');

/**
 * Copy over fixtures
 */
$this->getFilesystem()->copy(sfConfig::get('sf_plugins_dir') . '/apostrophePlugin/data/fixtures/a_users_and_groups.yml.suggested', sfConfig::get('sf_data_dir') . '/fixtures/a_users_and_groups.yml');
$this->getFilesystem()->copy(sfConfig::get('sf_plugins_dir') . '/apostrophePlugin/data/fixtures/demo_admin_user.yml.suggested', sfConfig::get('sf_data_dir') . '/fixtures/demo_admin_user.yml');
$this->getFilesystem()->copy(sfConfig::get('sf_plugins_dir') . '/apostrophePlugin/data/fixtures/pages.yml.suggested', sfConfig::get('sf_data_dir') . '/fixtures/pages.yml');
$this->getFilesystem()->execute('git add .; git commit -m "added a few fixtures"');

/**
 * Setup the yml files
 *
 * Not sure the best way other then to download them from the svn repo
 */
$this->setConfiguration($this->createConfiguration('frontend', 'dev'));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/apps/frontend/config/app.yml -O %s/app.yml', sfConfig::get('sf_app_config_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/apps/frontend/config/cache.yml -O %s/cache.yml', sfConfig::get('sf_app_config_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/apps/frontend/config/factories.yml -O %s/factories.yml', sfConfig::get('sf_app_config_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/apps/frontend/config/filters.yml -O %s/filters.yml', sfConfig::get('sf_app_config_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/apps/frontend/config/routing.yml -O %s/routing.yml', sfConfig::get('sf_app_config_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/apps/frontend/config/security.yml -O %s/security.yml', sfConfig::get('sf_app_config_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/apps/frontend/config/settings.yml -O %s/settings.yml', sfConfig::get('sf_app_config_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/apps/frontend/config/view.yml -O %s/view.yml', sfConfig::get('sf_app_config_dir')));
$this->getFilesystem()->mkdirs(sfConfig::get('sf_app_config_dir') . '/error');
$this->getFilesystem()->touch(array(
    sprintf('%s/error/error.html.php', sfConfig::get('sf_app_config_dir')),
    sprintf('%s/js/fckextraconfig.js', sfConfig::get('sf_web_dir')),
    sprintf('%s/js/site.js', sfConfig::get('sf_web_dir')),
    sprintf('%s/css/ie.css', sfConfig::get('sf_web_dir')),
    sprintf('%s/css/main.less', sfConfig::get('sf_web_dir')),
));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/apps/frontend/config/error/error.html.php -O %s/error/error.html.php', sfConfig::get('sf_app_config_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/apps/frontend/templates/layout.php -O %s/templates/layout.php', sfConfig::get('sf_app_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/web/js/site.js -O %s/js/site.js', sfConfig::get('sf_web_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/web/js/fckextraconfig.js -O %s/js/fckextraconfig.js', sfConfig::get('sf_web_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/web/css/ie.css -O %s/css/ie.css', sfConfig::get('sf_web_dir')));
$this->getFilesystem()->execute(sprintf('wget http://svn.apostrophenow.org/sandboxes/asandbox/branches/1.5/web/css/main.less -O %s/css/main.less', sfConfig::get('sf_web_dir')));
$this->getFilesystem()->execute('git add .; git commit -m "updated frontend config"');


$this->setConfiguration($this->createConfiguration('frontend', 'dev'));
$this->reloadTasks();
$this->getFilesystem()->execute('php symfony doctrine:build --all-classes');
$this->getFilesystem()->execute('rm -rf cache/*');
$this->getFilesystem()->execute('php symfony doctrine:build --all --and-load --no-confirmation');
$this->getFilesystem()->execute('php symfony plugin:publish-assets');
$this->getFilesystem()->execute('rm -rf cache/*');
$this->getFilesystem()->execute('php symfony project:permissions');
