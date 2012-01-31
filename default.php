<?php
/**
 * This is a custom installer that is designed to get a symfony project up and
 * running. You will need to already need to have ran git init on the directory.
 */

/* @var $this sfGenerateProjectTask */

/**
 *
 * @param sfGenerateProjectTask $task
 * @param string $commit_message
 */
function gitAddAndCommit($task,$commit_message)
{
    $task->getFilesystem()->execute('git add .');
    $task->getFilesystem()->execute(sprintf('git commit -m "%s"',$commit_message));
}

// generate app
$this->runTask('generate:app', 'frontend');
$this->setConfiguration($this->createConfiguration('frontend', 'dev'));
$this->runTask('generate:module','frontend default');

/**
 * Copy all the default files that we will use
 */
$this->getFilesystem()->remove(array(
  sfConfig::get('sf_app_module_dir') . '/default/actions/actions.class.php',
  sfConfig::get('sf_app_module_dir') . '/default/templates/indexSuccess.php'
));
$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir') . '/controller/default/actions/actions.class.php', sfConfig::get('sf_app_module_dir') . '/default/actions/actions.class.php');
$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir') . '/controller/default/templates/defaultLayout.php', sfConfig::get('sf_app_module_dir') . '/default/templates/defaultLayout.php');
$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir') . '/controller/default/templates/disabledSuccess.php', sfConfig::get('sf_app_module_dir') . '/default/templates/disabledSuccess.php');
$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir') . '/controller/default/templates/error404Success.php', sfConfig::get('sf_app_module_dir') . '/default/templates/error404Success.php');
$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir') . '/controller/default/templates/indexSuccess.php', sfConfig::get('sf_app_module_dir') . '/default/templates/indexSuccess.php');
$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir') . '/controller/default/templates/loginSuccess.php', sfConfig::get('sf_app_module_dir') . '/default/templates/loginSuccess.php');
$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir') . '/controller/default/templates/moduleSuccess.php', sfConfig::get('sf_app_module_dir') . '/default/templates/moduleSuccess.php');
$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir') . '/controller/default/templates/secureSuccess.php', sfConfig::get('sf_app_module_dir') . '/default/templates/secureSuccess.php');
$this->getFilesystem()->mkdirs(sfConfig::get('sf_app_config_dir') . '/error');
$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir') . '/exception/data/error.html.php', sfConfig::get('sf_app_config_dir') . '/error/error.html.php');
$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir') . '/exception/data/unavailable.php', sfConfig::get('sf_app_config_dir') . '/unavailable.php');

/**
 * Manipulate the settings.yml
 */
$settings = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml');
$settings['all']['.actions']['login_module'] = 'default';
$settings['all']['.actions']['login_action'] = 'login';
$settings['all']['.actions']['secure_module'] = 'default';
$settings['all']['.actions']['secure_action'] = 'secure';
$settings['all']['.actions']['error_404_module'] = 'default';
$settings['all']['.actions']['error_404_action'] = 'error404';
$settings['all']['.actions']['module_disabled_module'] = 'default';
$settings['all']['.actions']['module_disabled_action'] = 'disabled';
$settings['all']['.settings']['check_lock'] = true;
$settings['prod']['.settings']['logging_enabled'] = true;
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml', sfYaml::dump($settings, 3));

/**
 * Manipulate factories.yml
 */
$factories = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/factories.yml');
$factories['all']['storage'] = array(
  'class' => 'sfSessionStorage',
  'param' => array(
    'session_name' => 'symfony'
  )
);
$factories['prod']['logger'] = array(
  'class' => 'sfAggregateLogger',
  'param' => array(
    'level' => 'err',
    'loggers' => array(
      'sf_file_debug' => array(
        'class' => 'sfFileLogger',
        'param' => array(
          'level' => 'err',
          'file' => '%SF_LOG_DIR%/%SF_APP%_%SF_ENVIRONMENT%.log'
        ),
      ),
    ),
  ),
);
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/factories.yml', sfYaml::dump($factories, 7));

/**
 * Manipulate the ProjectConfiguration.class.php
 */
$source = sfClassManipulator::fromFile(sfConfig::get('sf_config_dir') . '/ProjectConfiguration.class.php');
$source->wrapMethod('setup', '', '}public function configureDoctrine(Doctrine_Manager $manager){');
$source->save();

/**
 * Download a .gitignore file
 */
$this->getFilesystem()->execute(sprintf('wget https://raw.github.com/github/gitignore/master/Symfony.gitignore -O %s/.gitignore', sfConfig::get('sf_root_dir')));

/**
 * Configure databases.yml and make a copy
 */
$this->runTask('configure:database', '"mysql:host=127.0.0.1;dbname=symfony_app" "root" "root"');
$this->getFilesystem()->copy(sfConfig::get('sf_config_dir') . '/databases.yml', sfConfig::get('sf_config_dir') . '/databases.yml.example');

/**
 * Commit what we have so far
 */
gitAddAndCommit($this, 'initial commit');

/**
 * Let's add some submodules
 */
if ($this->askConfirmation('Do you want to install sfTaskExtraPlugin?'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfTaskExtraPlugin.git plugins/sfTaskExtraPlugin');
  sfSymfonyPluginManager::enablePlugin('sfTaskExtraPlugin', sfConfig::get('sf_config_dir'));
  gitAddAndCommit($this, 'added sfTaskExtraPlugin');
}

if ($this->askConfirmation('Do you want to install sfFormExtraPlugin?'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfFormExtraPlugin.git plugins/sfFormExtraPlugin');
  sfSymfonyPluginManager::enablePlugin('sfFormExtraPlugin', sfConfig::get('sf_config_dir'));
  gitAddAndCommit($this, 'added sfFormExtraPlugin');
}

if ($this->askConfirmation('Do you want to install sfDoctrineGuardPlugin?'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfDoctrineGuardPlugin.git plugins/sfDoctrineGuardPlugin');
  $this->getFilesystem()->copy(sfConfig::get('sf_plugins_dir') . '/sfDoctrineGuardPlugin/data/fixtures/fixtures.yml.sample', sfConfig::get('sf_data_dir') . '/fixtures/sfGuard.yml');
  $filters = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/filters.yml');
  $insertKey = 'security';
  $keys = array_keys($filters);
  $vals = array_values($filters);
  $insertAfter = array_search($insertKey, $keys);
  $keys2 = array_splice($keys, $insertAfter);
  $vals2 = array_splice($vals, $insertAfter);
  $keys[] = "remember_me";
  $vals[] = array('class' => 'sfGuardRememberMeFilter');
  $filters = array_merge(array_combine($keys, $vals), array_combine($keys2, $vals2));
  file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/filters.yml', sfYaml::dump($filters, 10));

  /**
   * enable the module in the settings.yml
   */
  $settings = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml');
  $default_modules = empty($settings['all']['.settings']['default_modules']) ? array('default') : $settings['all']['.settings']['default_modules'];
  array_push($default_modules, 'sfGuardAuth');
  $settings = array_merge($settings, array(
      'all' => array(
        '.settings' => array(
          'enabled_modules' => $default_modules,
          'login_module' => 'sfGuardAuth',
          'login_action' => 'signin',
          'secure_module' => 'sfGuardAuth',
          'secure_action' => 'secure',
        )
      )
    ));
  file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml', sfYaml::dump($settings, 10));

  /**
   * update the myUser.class.php
   */
  $contents = file_get_contents(sfConfig::get('sf_apps_dir') . '/frontend/lib/myUser.class.php');
  $contents = preg_replace('/sfBasicSecurityUser/', 'sfGuardSecurityUser', $contents);
  file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/lib/myUser.class.php', $contents);
  sfSymfonyPluginManager::enablePlugin('sfDoctrineGuardPlugin', sfConfig::get('sf_config_dir'));
  gitAddAndCommit($this, 'added sfDoctrineGuardPlugin');
}

if ($this->askConfirmation('Do you want to install sfDoctrineExtraPlugin?'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfDoctrineExtraPlugin.git plugins/sfDoctrineExtraPlugin');
  sfSymfonyPluginManager::enablePlugin('sfDoctrineExtraPlugin', sfConfig::get('sf_config_dir'));
  gitAddAndCommit($this, 'added sfDoctrineExtraPlugin');
}

if ($this->askConfirmation('Do you want to install sfGoogleAnalyticsPlugin?'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfGoogleAnalyticsPlugin.git plugins/sfGoogleAnalyticsPlugin');
  sfSymfonyPluginManager::enablePlugin('sfGoogleAnalyticsPlugin', sfConfig::get('sf_config_dir'));
  gitAddAndCommit($this, 'added sfGoogleAnalyticsPlugin');
}

if ($this->askConfirmation('Do you want to install sfGoogleWebsiteOptimizerPlugin?'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfGoogleWebsiteOptimizerPlugin.git plugins/sfGoogleWebsiteOptimizerPlugin');
  sfSymfonyPluginManager::enablePlugin('sfGoogleWebsiteOptimizerPlugin', sfConfig::get('sf_config_dir'));
  gitAddAndCommit($this, 'added sfGoogleWebsiteOptimizerPlugin');
}

if ($this->askConfirmation('Do you want to install sfErrorNotifierPlugin?'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfErrorNotifierPlugin.git plugins/sfErrorNotifierPlugin');
  sfSymfonyPluginManager::enablePlugin('sfErrorNotifierPlugin', sfConfig::get('sf_config_dir'));
  gitAddAndCommit($this, 'added sfErrorNotifierPlugin');
}

if ($this->askConfirmation('Do you want to install npAssetsOptimizerPlugin?'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/n1k0/npAssetsOptimizerPlugin.git plugins/npAssetsOptimizerPlugin');
  sfSymfonyPluginManager::enablePlugin('sfErrorNotifierPlugin', sfConfig::get('sf_config_dir'));
  gitAddAndCommit($this, 'added npAssetsOptimizerPlugin');
}


/**
 * publish the plugin assets or make them symlinks son!
 */
$this->runTask('plugin:publish-assets');