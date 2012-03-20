<?php
/**
 * This is a custom installer that is designed to get a symfony project up and
 * running. You will need to already need to have ran git init on the directory.
 */

/**
 * This will update the filters.yml file
 *
 * @param array $filter
 * @param string $insertBefore
 */
function updateFiltersYaml(array $filter,$insertBefore='cache')
{
  $filters = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/filters.yml');
  $keys = array_keys($filters);
  $vals = array_values($filters);
  $insertAfter = array_search($insertBefore, $keys);
  $keys2 = array_splice($keys, $insertAfter);
  $vals2 = array_splice($vals, $insertAfter);
  $filters = array_merge($filter, array_combine($keys2, $vals2));
  file_put_contents(sfConfig::get('sf_app_config_dir') . '/filters.yml', sfYaml::dump($filters, 10));
}

function updateAppYaml(array $app)
{
  $appYaml = sfYaml::load(sfConfig::get('sf_app_config_dir') . '/app.yml');
  if (null == $appYaml)
  {
      $appYaml = array();
  }

  $appYaml = array_merge($appYaml,$app);
  file_put_contents(sfConfig::get('sf_app_config_dir') . '/app.yml', sfYaml::dump($appYaml, 15));
}

/* @var $this sfGenerateProjectTask */

// generate app
$this->runTask('generate:app', 'frontend');
$this->setConfiguration($this->createConfiguration('frontend', 'dev'));
$this->runTask('generate:module','frontend default');
$this->setConfiguration($this->createConfiguration('frontend', 'dev'));

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
$source->wrapMethod('setup', '', "}\n\tpublic function configureDoctrine(Doctrine_Manager \$manager)\n\t{");
$source->save();

/**
 * Download a .gitignore file
 */
$this->getFilesystem()->execute(sprintf('wget https://raw.github.com/github/gitignore/master/Symfony.gitignore -O %s/.gitignore', sfConfig::get('sf_root_dir')));

/**
 * Configure databases.yml and make a copy
 */
if ($this->askConfirmation('Do you want to setup the database? (default: yes)'))
{
    $this->runTask('configure:database', '"mysql:host=127.0.0.1;dbname=symfony" "root" "root"');
    $this->getFilesystem()->copy(sfConfig::get('sf_config_dir') . '/databases.yml', sfConfig::get('sf_config_dir') . '/databases.yml.example');
    $host = $this->ask('host (default: 127.0.0.1)', 'QUESTION', '127.0.0.1');
    $dbname = $this->ask('dbname (default: symfony)', 'QUESTION', 'symfony');
    $username = $this->ask('username (default: root)', 'QUESTION', 'root');
    $password = $this->ask('password (default: root)', 'QUESTION', 'root');
    $this->runTask('configure:database', sprintf('"mysql:host=%s;dbname=%s" "%s" "%s"',$host,$dbname,$username,$password));
}

/**
 * Commit what we have so far
 */
$this->getFilesystem()->execute('git add .; git commit -m "initial commit"');

/**
 * Let's add some submodules
 */
if ($this->askConfirmation('Do you want to install sfTaskExtraPlugin? (default: yes)'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfTaskExtraPlugin.git plugins/sfTaskExtraPlugin');
  sfSymfonyPluginManager::enablePlugin('sfTaskExtraPlugin', sfConfig::get('sf_config_dir'));
  $this->getFilesystem()->execute('git add. ;git commit -m "added sfTaskExtraPlugin"');
}

if ($this->askConfirmation('Do you want to install sfFormExtraPlugin? (default: yes)'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfFormExtraPlugin.git plugins/sfFormExtraPlugin');
  sfSymfonyPluginManager::enablePlugin('sfFormExtraPlugin', sfConfig::get('sf_config_dir'));
  $this->getFilesystem()->execute('git add. ;git commit -m "added sfFormExtraPlugin"');
}

if ($this->askConfirmation('Do you want to install sfDoctrineGuardPlugin? (default: yes)'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfDoctrineGuardPlugin.git plugins/sfDoctrineGuardPlugin');
  $this->getFilesystem()->copy(sfConfig::get('sf_plugins_dir') . '/sfDoctrineGuardPlugin/data/fixtures/fixtures.yml.sample', sfConfig::get('sf_data_dir') . '/fixtures/sfGuard.yml');
  updateFiltersYaml(array('remember_me'=>array('class'=>'sfGuardRememberMeFilter')), 'security');

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
  $this->getFilesystem()->execute('git add. ;git commit -m "added sfDoctrineGuardPlugin"');
}

if ($this->askConfirmation('Do you want to install sfDoctrineExtraPlugin? (default: yes)'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfDoctrineExtraPlugin.git plugins/sfDoctrineExtraPlugin');
  sfSymfonyPluginManager::enablePlugin('sfDoctrineExtraPlugin', sfConfig::get('sf_config_dir'));
  $this->getFilesystem()->execute('git add. ;git commit -m "added sfDoctrineExtraPlugin"');
}

if ($this->askConfirmation('Do you want to install sfGoogleAnalyticsPlugin? (default: yes)'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfGoogleAnalyticsPlugin.git plugins/sfGoogleAnalyticsPlugin');
  $filters = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/filters.yml');
  // add the filter
  updateFiltersYaml(array('sf_google_analytics_plugin'=>array('class' => 'sfGoogleAnalyticsFilter')));
  // add options in app.yml
  updateAppYaml(array(
    'all' => array(
      'sf_google_analytics_plugin' => array(
        'enabled' => 'false',
        'profile_id' => 'xx-xxxxx-x',
        'tracker' => 'google',
      )
    ),
    'prod' => array(
      'sf_google_analytics_plugin' => array(
        'enabled' => 'true'
      ),
    ),
  ));
  sfSymfonyPluginManager::enablePlugin('sfGoogleAnalyticsPlugin', sfConfig::get('sf_config_dir'));
  $this->getFilesystem()->execute('git add. ;git commit -m "added sfGoogleAnalyticsPlugin"');
}

if ($this->askConfirmation('Do you want to install sfGoogleWebsiteOptimizerPlugin? (default: yes)'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfGoogleWebsiteOptimizerPlugin.git plugins/sfGoogleWebsiteOptimizerPlugin');
  // add filter
  updateFiltersYaml(array(
    'sf_google_website_optimizer_plugin' => array(
      'class' => 'sfGWOFilter'
    ),
  ));
  // add app config
  updateAppYaml(array(
    'all' => array(
      'sf_google_website_optimizer_plugin' => array(
        'enabled' => 'false',
        'uacct' => 'xx-xxxxx-x',
        'experiments' => array(
          'experiment_one' => array(
            'type' => 'ab',
            'key' => 'xxxxxxxxxx',
            'pages' => array(
              'original' => array(
                'module' => 'auth',
                'action' => 'register',
                'alt' => null,
              ),
              'variations' => array(
                array(
                  'module' => 'auth',
                  'action' => 'register',
                  'alt' => 1
                ),
                array(
                  'module' => 'auth',
                  'action' => 'register',
                  'alt' => 2
                ),
              ),
              'conversion' => array(
                'module' => 'auth',
                'action' => 'success',
              ),
            ),
          ),
        ),
      ),
    ),
    'prod' => array(
      'sf_google_webmaster_optimizer_plugin' => array(
        'enabled' => 'true'
      ),
    )
  ));
  sfSymfonyPluginManager::enablePlugin('sfGoogleWebsiteOptimizerPlugin', sfConfig::get('sf_config_dir'));
  $this->getFilesystem()->execute('git add. ;git commit -m "added sfGoogleWebsiteOptimizerPlugin"');
}

if ($this->askConfirmation('Do you want to install sfErrorNotifierPlugin? (default: yes)'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/JoshuaEstes/sfErrorNotifierPlugin.git plugins/sfErrorNotifierPlugin');
  $emailTo = $this->ask('Email address to send errors to? (ie your@email.com)');
  $emailFrom = $this->ask('Email should be from what email address? (ie no-reply@example.com)');
  // add settings to app.yml
  updateAppYaml(array(
    'all' => array(
      'sfErrorNotifier' => array(
        'enabled' => 'false',
        'emailTo' => $emailTo,
        'emailFrom' => $emailFrom,
        'emailFormat' => 'html',
        'reportPHPErrors' => 'false',
        'reportPHPWarnings' => 'false',
        'report404' => 'false'
      ),
    ),
    'prod' => array(
      'sfErrorNotifier' => array(
        'enabled' => 'true'
      )
    ),
  ));
  sfSymfonyPluginManager::enablePlugin('sfErrorNotifierPlugin', sfConfig::get('sf_config_dir'));
  $this->getFilesystem()->execute('git add. ;git commit -m "added sfErrorNotifierPlugin"');
}

if ($this->askConfirmation('Do you want to install npAssetsOptimizerPlugin? (default: yes)'))
{
  $this->getFilesystem()->execute('git submodule add git://github.com/n1k0/npAssetsOptimizerPlugin.git plugins/npAssetsOptimizerPlugin');
  // add settings to app.yml file
  updateAppYaml(array(
    'all' => array(
      'np_assets_optimizer_plugin' => array(
        'enabled' => 'false',
        'class' => 'npAssetsOptimizerService',
        'configuration' => array(
          'javascript' => array(
            'enabled' => 'false',
            'class' => 'npOptimizerJavascript',
            'params' => array(
              'driver' => 'JSMin',
              'destination' => '/js/optimized.js',
              'timestamp' => 'true',
              'files' => array('/js/js.js'),
            ),
          ),
          'stylesheet' => array(
            'enabled' => 'false',
            'class' => 'npOptimizerStylesheet',
            'params' => array(
              'driver' => 'Cssmin',
              'destination' => '/css/optimized.css',
              'timestamp' => 'true',
              'files' => array('main.css'),
            ),
          ),
          'png_image' => array(
            'enabled' => 'false',
            'class' => 'npOptimizerPngImage',
            'params' => array(
              'driver' => 'Pngout',
              'folders' => array('%SF_WEB_DIR%/images'),
            ),
          ),
          'jpeg_image' => array(
            'enabled' => 'false',
            'class' => 'npOptimizerJpegImage',
            'params' => array(
              'driver' => 'Jpegtran',
              'folders' => array('%SF_WEB_DIR%/images'),
            ),
          ),
        ),
      ),
    ),
    'prod' => array(
      'np_assets_optimizer_plugin' => array(
        'enabled' => 'true',
        'configuration' => array(
          'javascript' => array(
            'enabled' => 'true',
          ),
          'stylesheet' => array(
            'enabled' => 'true',
          ),
          'png_image' => array(
            'enabled' => 'false',
          ),
          'jpeg_image' => array(
            'enabled' => 'false',
          ),
        ),
      ),
    ),
  ));
  $layout = file_get_contents(sfConfig::get('sf_app_template_dir') . '/layout.php');
  $layout = preg_replace("/<?php include_stylesheets() ?>/", '<?php include_optimized_stylesheets() ?>', $layout);
  $layout = preg_replace("/<?php include_javascripts() ?>/", '<?php include_optimized_javascripts() ?>', $layout);
  file_put_contents(sfConfig::get('sf_app_template_dir') . '/layout.php', $layout);
  sfSymfonyPluginManager::enablePlugin('sfErrorNotifierPlugin', sfConfig::get('sf_config_dir'));
  $this->getFilesystem()->execute('git add. ;git commit -m "added npAssetsOptimizerPlugin"');
}


/**
 * publish the plugin assets or make them symlinks son!
 */
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
if ($this->askConfirmation('Would you like to install the cap files? (default: yes)'))
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
    $betaBranch = $this->ask('Beta branch (default: develop)','QUESTION','develop');
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