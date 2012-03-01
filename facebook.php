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
 * Merge the current config yml files and the default yml files together
 */
$factoriesDefault = sfYaml::load(sfConfig::get('sf_symfony_lib_dir') . '/config/config/factories.yml');
$factoriesDefault['all'] = $factoriesDefault['default'];
unset($factoriesDefault['default']);
$factories = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/factories.yml');
$factories = sfToolkit::arrayDeepMerge($factoriesDefault,$factories);

$settingsDefault = sfYaml::load(sfConfig::get('sf_symfony_lib_dir') . '/config/config/settings.yml');
$settingsDefault['all'] = $settingsDefault['default'];
unset($settingsDefault['default']);
$settings = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml');
$settings = sfToolkit::arrayDeepMerge($settingsDefault,$settings);

/**
 * Manipulate the settings.yml
 */
$settings['all']['.settings']['check_lock'] = true;
$settings['prod']['.settings']['logging_enabled'] = true;
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml', sfYaml::dump($settings, 5));

/**
 * Manipulate factories.yml
 */
$factories['all']['storage'] = array(
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
$source->wrapMethod('setup', '', "}\n\tpublic function configureDoctrine(Doctrine_Manager \$manager)\n\t{");
$source->save();

/**
 * Download a .gitignore file
 */
$this->getFilesystem()->execute(sprintf('wget https://raw.github.com/github/gitignore/master/Symfony.gitignore -O %s/.gitignore', sfConfig::get('sf_root_dir')));

/**
 * Configure database
 */
if ($this->askConfirmation('Do you need to use a database? (default: no)', 'QUESTION', false))
{
  if ($this->askConfirmation('Do you want to setup the database? (default: yes)'))
  {
      // create a detault databases.yml and copy it over to databases.yml.example
      $this->runTask('configure:database', '"mysql:host=127.0.0.1;dbname=facebook_app" "root" "root"');
      $this->getFilesystem()->copy(sfConfig::get('sf_config_dir') . '/databases.yml', sfConfig::get('sf_config_dir') . '/databases.yml.example');
      $host = $this->ask('host (default: 127.0.0.1)', 'QUESTION', '127.0.0.1');
      $dbname = $this->ask('dbname (default: symfony)', 'QUESTION', 'symfony');
      $username = $this->ask('username (default: root)', 'QUESTION', 'root');
      $password = $this->ask('password (default: root)', 'QUESTION', 'root');
      $this->runTask('configure:database', sprintf('"mysql:host=%s;dbname=%s" "%s" "%s"',$host,$dbname,$username,$password));
  }
}
else
{
  $this->logBlock('settings', 'use_database set to false');
  $settings = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml');
  $settings['all']['.settings']['use_database'] = false;
  file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml', sfYaml::dump($settings, 5));
}

$this->getFilesystem()->execute('git add .; git commit -m "initial commit"');

$this->getFilesystem()->execute('git submodule add git://github.com/facebook/facebook-php-sdk.git lib/vendor/facebook-php-sdk');
$this->getFilesystem()->execute('git commit -m \'added facebook-php-sdk subbmodule\'');

if ($this->askConfirmation('Do you want to install npAssetsOptimizerPlugin? (default: yes)'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/n1k0/npAssetsOptimizerPlugin.git plugins/npAssetsOptimizerPlugin');
  sfSymfonyPluginManager::enablePlugin('sfErrorNotifierPlugin', sfConfig::get('sf_config_dir'));
  $this->getFilesystem()->execute('git add. ;git commit -m \'added npAssetsOptimizerPlugin\'');
}

$this->runTask('plugin:publish-assets');

/**
 * Setup a vhost file
 */
if ($this->askConfirmation('Would you like to generate a vhost config file? (default: yes'))
{
    $this->getFilesystem()->touch(sfConfig::get('sf_config_dir') . '/vhost.dev');
    $tmpl = <<<EOF
<VirtualHost *:80>
  ServerName %SERVER_NAME%
  DocumentRoot "%SF_WEB_DIR%"
  <Directory "%SF_WEB_DIR%">
    AllowOverride All
    Allow from All
  </Directory>

  Alias /sf %SF_DATA_WEB_SF_DIR%
  <Directory "%SF_DATA_WEB_SF_DIR%">
    AllowOverride All
    Allow from All
  </Directory>

  <Directory "%SF_UPLOAD_DIR%">
     php_flag engine off
   </Directory>
</VirtualHost>
EOF;
    $serverName = $this->ask('ServerName (default: symfony.local)','QUESTION','symfony.local');
    $vhost = strtr($tmpl,array(
      '%SF_WEB_DIR%' => sfConfig::get('sf_web_dir'),
      '%SF_UPLOAD_DIR%' => sfConfig::get('sf_upload_dir'),
      '%SF_DATA_WEB_SF_DIR%' => realpath(sfConfig::get('sf_symfony_lib_dir') . '/../data/web/sf')
    ));
    file_put_contents(sfConfig::get('sf_config_dir') . '/vhost.dev', $vhost);
    $this->getFilesystem()->execute('git add .; git commit -m "Added vhost file"');
}

/**
 * Install capistrano?
 */
if ($this->askConfirmation('Do you want to install Capistrano? (default: no)', 'QUESTION', false))
{
    try
    {
        $this->getFilesystem()->execute('sudo gem install capistrano');
        $this->getFilesystem()->execute('sudo gem install capistrano-ext');
        $this->getFilesystem()->execute('sudo gen install capifony');
    } catch (Exception $e)
    {
        $this->logBlock($e->getMessage(), 'ERROR_LARGE');
    }
}

/**
 * Generate the capistrano stuff
 */
if ($this->askConfirmation('Would you like to setup the cap files? (default: yes)'))
{
    $this->getFilesystem()->mkdirs(sfConfig::get('sf_config_dir') . '/deploy');
    $this->getFilesystem()->touch(array(
        sfConfig::get('sf_web_dir') . '/robots.txt.beta',
        sfConfig::get('sf_web_dir') . '/robots.txt.production',
        sfConfig::get('sf_config_dir') . '/app.yml',
        sfConfig::get('sf_config_dir') . '/app.yml.beta',
        sfConfig::get('sf_config_dir') . '/app.yml.production',
        sfConfig::get('sf_config_dir') . '/deploy.rb',
        sfConfig::get('sf_config_dir') . '/deploy/beta.rb',
        sfConfig::get('sf_config_dir') . '/deploy/production.rb',
        sfConfig::get('sf_root_dir') . '/Capfile',
    ));
    $CapfileTmpl = <<<EOF
load 'deploy' if respond_to?(:namespace) # cap2 differentiator
Dir['plugins/*/lib/recipes/*.rb'].each { |plugin| load(plugin) }
load Gem.required_location('capifony', 'symfony1.rb')
load 'config/deploy' # remove this line to skip loading any of the default tasks

# Load in the multistage configuration and setup the stages
set :stages, %w(beta production)
require 'capistrano/ext/multistage'

set :shared_children,   %w(log web/uploads cache)
set :shared_files,      %w(config/databases.yml config/app.yml)
EOF;
    file_put_contents(sfConfig::get('sf_root_dir') . '/Capfile', $CapfileTmpl);

    $deployTmpl = <<<EOF
set :application,             "%APPLICATION%"
set :scm,                     :git
set :git_enable_submodules,   1
set :repository,              "%REPO%"
set :deploy_via,              :remote_cache
set :use_sudo,                false
set :group_writable,          false
set :keep_releases,           3
ssh_options[:forward_agent] = true
EOF;
    $applicationName = $this->ask('Application name (default: symfony)', 'QUESTION', 'symfony');
    do
    {
        $repoURL = $this->ask('Git repo (ie git@github.com:JoshuaEstes/repo.git)', 'QUESTION', false);
    }
    while(!$repoURL);
    $deployRb = strtr($deployTmpl, array(
      '%APPLICATION%' => $applicationName,
      '%REPO%' => $repoURL,
    ));
    file_put_contents(sfConfig::get('sf_config_dir') . '/deploy.rb', $deployRb);

    $this->logBlock("Setup beta deply", 'INFO_LARGE');
    $betaTmpl = <<<EOF
set :domain,      "%BETA_DOMAIN%"
set :deploy_to,   "%BETA_DEPLOY_TO%"
set :user,        "%BETA_USER%"
set :branch,      "%BETA_BRANCH%"
role :web,        domain
role :app,        domain
role :db,         domain, :primary => true
set :can_cold_deploy,   false
set :frontend_application_name,  "frontend"
set :symfony_env_prod, "prod"
EOF;
    do
    {
        $betaDomain = $this->ask('Beta domain (ie beta.example.com)','QUESTION',false);
    }
    while(!$betaDomain);
    $betaDeployTo = $this->ask(sprintf('Beta deploy path (default: /var/www/%s)',$betaDomain),'QUESTION',sprintf('/var/www/%s',$betaDomain));
    do
    {
        $betaUser = $this->ask('Beta ssh username','QUESTION',false);
    }
    while(!$betaUser);
    $betaBranch = $this->ask('Beta branch (default: deploy)','QUESTION','deploy');
    $betaRb = strtr($betaTmpl,array(
      '%BETA_DOMAIN%' => $betaDomain,
      '%BETA_DEPLOY_TO%' => $betaDeployTo,
      '%BETA_USER%' => $betaUser,
      '%BETA_BRANCH%' => $betaBranch,
    ));
    file_put_contents(sfConfig::get('sf_config_dir') . '/deploy/beta.rb', $betaRb);

    $this->logBlock("Setup production deploy", 'INFO_LARGE');

    $productionTmpl = <<<EOF
set :domain,      "%PROD_DOMAIN%"
set :deploy_to,   "%PROD_DEPLOY_TO%"
set :user,        "%PROD_USER%"
set :branch,      "%PROD_BRANCH%"
role :web,        domain
role :app,        domain
role :db,         domain, :primary => true
set :can_cold_deploy,   false
set :frontend_application_name,  "frontend"
set :symfony_env_prod, "prod"
EOF;
    do
    {
        $prodDomain = $this->ask('production domain (ie example.com)','QUESTION',false);
    }
    while(!$prodDomain);
    $prodDeployTo = $this->ask(sprintf('production deploy path (default: /var/www/%s)',$prodDomain),'QUESTION',sprintf('/var/www/%s',$prodDomain));
    do
    {
        $prodUser = $this->ask('production ssh username','QUESTION',false);
    }
    while(!$prodUser);
    $prodBranch = $this->ask('production branch (default: master)','QUESTION','master');
    $prodRb = strtr($productionTmpl,array(
      '%PROD_DOMAIN%' => $prodDomain,
      '%PROD_DEPLOY_TO%' => $prodDeployTo,
      '%PROD_USER%' => $prodUser,
      '%PROD_BRANCH%' => $prodBranch,
    ));
    file_put_contents(sfConfig::get('sf_config_dir') . '/deploy/production.rb', $prodRb);

    $this->getFilesystem()->execute('git add .; git commit -m "Added capistrano files"');
}