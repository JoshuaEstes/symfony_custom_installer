<?php

/**
 * This custom installer will setup a symfony app for a facebook project. This
 * installer assumes that this is a git repo and will try to add submodules
 */
/* @var $this sfGenerateProjectTask */

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
    'session_name' => 'sfFacebook'
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


$this->runTask('plugin:publish-assets');

$this->runTask('configure:database', '"mysql:host=127.0.0.1;dbname=facebook_app" "root" "root"');
$this->getFilesystem()->copy(sfConfig::get('sf_config_dir') . '/databases.yml', sfConfig::get('sf_config_dir') . '/databases.yml.example');

$this->getFilesystem()->execute('git add .');
$this->getFilesystem()->execute('git commit -m \'initial commit\'');

$this->getFilesystem()->execute('git submodule add git://github.com/facebook/facebook-php-sdk.git lib/vendor/facebook-php-sdk');
$this->getFilesystem()->execute('git commit -m \'added facebook-php-sdk subbmodule\'');



if ($this->askConfirmation('Do you want to install npAssetsOptimizerPlugin?'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/n1k0/npAssetsOptimizerPlugin.git plugins/npAssetsOptimizerPlugin');
  sfSymfonyPluginManager::enablePlugin('sfErrorNotifierPlugin', sfConfig::get('sf_config_dir'));
  $this->getFilesystem()->execute('git add .');
  $this->getFilesystem()->execute('git commit -m \'added npAssetsOptimizerPlugin\'');
}







$this->runTask('plugin:publish-assets');