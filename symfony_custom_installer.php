<?php

/**
 *
 */

/* @var $this sfGenerateProjectTask */

/**
 * Create a frontend application
 */
$this->logSection('install', 'create frontend application');
$this->runTask('generate:app', 'frontend');

/**
 * Install some plugins?
 */
while ($plugin = $this->ask('To install another plugin, please enter the name.'))
{
  $this->logSection('install',sprintf('Installing %s',$plugin));
  try
  {
    $this->runTask('plugin:install',$plugin);
    $function = 'configure'.$plugin;
    if (function_exists($function))
    {
      $this->logSection('installer',sprintf('Configuring plugin: %s',$plugin));
      $function($this);
    }
  }
  catch(Exception $e)
  {
    $this->logSection('install',sprintf('Could not install %s',$plugin));
    $this->logBlock($e->getMessage(),'ERROR_LARGE');
  }
}

/**
 * Initialize git
 */
if (!is_dir(sfConfig::get('sf_root_dir') . '/.git'))
{
  if ($this->askConfirmation('Do you want me to git init? [yes]'))
  {
    $this->getFilesystem()->execute('git init');

    if ($remote = $this->ask('Where is the origin located?'))
    {
      $this->getFilesystem()->execute(sprintf('git remote add origin %s',$remote));
    }
  }
}

/**
 *  Submodule File
 *  submodules.php should contain an array as such:
 *  $submodules = array(
 * 	'localLocation' => 'repoLocation'
 *  );
 */
if (file_exists("submodules.php")) 
{
	require_once("submodules.php");
  
	if (!empty($submodules) && is_array($submodules)) 
	{
		if ($this->askConfirmation("Process submodules file? [yes]" )) 
		{
			foreach ($submodules as $localLocation => $repoLocation) 
			{
				if ($this->askConfirmation("Install " . $repoLocation . " in " . $localLocation)) 
				{
					try {
						$this->getFilesystem()->execute('git submodule add ' . $repoLocation . ' ' . sfConfig::get('sf_plugins_dir') . '/' . $localLocation);
						$this->logSection('install', 'added git submodule to ' . $localLocation);
					} catch (Exception $e) {
						$this->logBlock($e->getMessage(), 'ERROR_LARGE');
					}
				}
			}
		}
	}
}

/**
 * If using git, install some submodule plugins
 */
if (is_dir(sfConfig::get('sf_root_dir') . '/.git'))
{
  $this->logSection('install','Install git submodules');
  while ($submodule = $this->ask('To install a git submodule plugin, please enter the git URL.'))
  {
    preg_match('/\/(\w+)\.git/',$submodule,$match);
    if (empty($match[1]))
    {
      $this->logBlock('invalid git repo, must end with .git','ERROR_LARGE');
      continue;
    }

    $submodule_name = $match[1];
    if (substr($submodule_name,-6) != 'Plugin')
    {
      $submodule_name = $submodule_name . 'Plugin';
    }

    try
    {
      $this->getFilesystem()->execute('git submodule add ' . $submodule . ' ' . sfConfig::get('sf_plugins_dir') . '/' . $submodule_name);
      $successfull_install = true;
    }
    catch (Exception $e)
    {
      $successfull_install = false;
      $this->logSection('install',sprintf('Could not install %s',$plugin));
      $this->logBlock($e->getMessage(),'ERROR_LARGE');
    }

    if ($successfull_install)
    {
      $this->enablePlugin($submodule_name);
    }
  }
}

$this->logSection('install', 'publish assets');
$this->runTask('plugin:publish-assets');

if ($this->askConfirmation('Do you want me to help you setup the database config?'))
{
  $host = $this->ask('Database host');
  $dbname = $this->ask('Database database');
  $username = $this->ask('Database username');
  $password = $this->ask('Database password');
  $this->runTask('configure:database','mysql:host='.$host.';dbname='.$dbname.' '.$username.' "'.$password.'"');
}

/**
 * Create the 404 error template
 */
$this->logSection('installer','creating 404 page');
$this->getFilesystem()->execute('mkdir -p ' . sfConfig::get('sf_apps_dir') . '/frontend/modules/default/templates');
$contents = <<<'EOF'
<?php decorate_with(dirname(__FILE__).'/defaultLayout.php') ?>

<div class="sfTMessageContainer sfTAlert"> 
  <?php echo image_tag('/sf/sf_default/images/icons/cancel48.png', array('alt' => 'page not found', 'class' => 'sfTMessageIcon', 'size' => '48x48')) ?>
  <div class="sfTMessageWrap">
    <h1>Oops! Page Not Found</h1>
    <h5>The server returned a 404 response.</h5>
  </div>
</div>
<dl class="sfTMessageInfo">
  <dt>Did you type the URL?</dt>
  <dd>You may have typed the address (URL) incorrectly. Check it to make sure you've got the exact right spelling, capitalization, etc.</dd>

  <dt>Did you follow a link from somewhere else at this site?</dt>
  <dd>If you reached this page from another part of this site, please email us at <?php echo mail_to('[email]') ?> so we can correct our mistake.</dd>

  <dt>Did you follow a link from another site?</dt>
  <dd>Links from other sites can sometimes be outdated or misspelled. Email us at <?php echo mail_to('[email]') ?> where you came from and we can try to contact the other site in order to fix the problem.</dd>

  <dt>What's next</dt>
  <dd>
    <ul class="sfTIconList">
      <li class="sfTLinkMessage"><a href="javascript:history.go(-1)">Back to previous page</a></li>
      <li class="sfTLinkMessage"><?php echo link_to('Go to Homepage', '@homepage') ?></li>
    </ul>
  </dd>
</dl>
EOF;
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/modules/default/templates/error404Success.php', $contents);

/**
 * Create the disabled page template
 */
$this->logSection('installer','Creating disabled page');
$contents = <<<'EOF'
<?php decorate_with(dirname(__FILE__).'/defaultLayout.php') ?>

<div class="sfTMessageContainer sfTAlert">
  <?php echo image_tag('/sf/sf_default/images/icons/disabled48.png', array('alt' => 'module disabled', 'class' => 'sfTMessageIcon', 'size' => '48x48')) ?>
  <div class="sfTMessageWrap">
    <h1>This Module is Unavailable</h1>
    <h5>This module has been disabled.</h5>
  </div>
</div>
<dl class="sfTMessageInfo">

  <dt>What's next</dt>
  <dd>
    <ul class="sfTIconList">
      <li class="sfTLinkMessage"><a href="javascript:history.go(-1)">Back to previous page</a></li>
      <li class="sfTLinkMessage"><?php echo link_to('Go to Homepage', '@homepage') ?></li>
    </ul>
  </dd>
</dl>
EOF;
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/modules/default/templates/disabledSuccess.php', $contents);

/**
 * Make sure the error404 and disabled pages are not secured
 */
$this->getFilesystem()->execute('mkdir -p ' . sfConfig::get('sf_apps_dir') . '/frontend/modules/default/config');
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/modules/default/config/security.yml',sfYaml::dump(array(
  'error404' => array('is_secure' => false),
  'disabled' => array('is_secure' => false),
)));


/**
 * Create the 500 error page
 */
$this->logSection('installer','creating 500 error page');
$this->getFilesystem()->execute('mkdir -p ' . sfConfig::get('sf_apps_dir') . '/frontend/config');
$contents = <<<'EOF'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php $path = sfConfig::get('sf_relative_url_root', preg_replace('#/[^/]+\.php5?$#', '', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : ''))) ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="title" content="symfony project" />
<meta name="robots" content="index, follow" />
<meta name="description" content="symfony project" />
<meta name="keywords" content="symfony, project" />
<meta name="language" content="en" />
<title>symfony project</title>

<link rel="shortcut icon" href="/favicon.ico" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $path ?>/sf/sf_default/css/screen.css" />
<!--[if lt IE 7.]>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $path ?>/sf/sf_default/css/ie.css" />
<![endif]-->

</head>
<body>
<div class="sfTContainer">
  <a title="symfony website" href="http://www.symfony-project.org/"><img alt="symfony PHP Framework" class="sfTLogo" src="<?php echo $path ?>/sf/sf_default/images/sfTLogo.png" height="39" width="186" /></a>
  <div class="sfTMessageContainer sfTAlert">
    <img alt="page not found" class="sfTMessageIcon" src="<?php echo $path ?>/sf/sf_default/images/icons/tools48.png" height="48" width="48" />
    <div class="sfTMessageWrap">
      <h1>Oops! An Error Occurred</h1>
      <h5>The server returned a "<?php echo $code ?> <?php echo $text ?>".</h5>
    </div>
  </div>

  <dl class="sfTMessageInfo">
    <dt>Something is broken</dt>
    <dd>Please e-mail us at [email] and let us know what you were doing when this error occurred. We will fix it as soon as possible.
    Sorry for any inconvenience caused.</dd>

    <dt>What's next</dt>
    <dd>
      <ul class="sfTIconList">
        <li class="sfTLinkMessage"><a href="javascript:history.go(-1)">Back to previous page</a></li>
        <li class="sfTLinkMessage"><a href="/">Go to Homepage</a></li>
      </ul>
    </dd>
  </dl>
</div>
</body>
</html>
EOF;
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/error.html.php', $contents);


/**
 *
 */
$this->logSection('installer','Creating Website Temporarily Unavailable page');
$contents = <<<'EOF'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php $path = preg_replace('#/[^/]+\.php5?$#', '', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : '')) ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo sfConfig::get('sf_charset', 'utf-8') ?>" />
<meta name="title" content="symfony project" />
<meta name="robots" content="index, follow" />
<meta name="description" content="symfony project" />
<meta name="keywords" content="symfony, project" />
<meta name="language" content="en" />
<title>symfony project</title>

<link rel="shortcut icon" href="/favicon.ico" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $path ?>/sf/sf_default/css/screen.css" />
<!--[if lt IE 7.]>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $path ?>/sf/sf_default/css/ie.css" />
<![endif]-->

</head>
<body>
<div class="sfTContainer">
  <a title="symfony website" href="http://www.symfony-project.org/"><img alt="symfony PHP Framework" class="sfTLogo" src="<?php echo $path ?>/sf/sf_default/images/sfTLogo.png" height="39" width="186" /></a>
  <div class="sfTMessageContainer sfTAlert">
    <img alt="page not found" class="sfTMessageIcon" src="<?php echo $path ?>/sf/sf_default/images/icons/tools48.png" height="48" width="48" />
    <div class="sfTMessageWrap">
      <h1>Website Temporarily Unavailable</h1>
      <h5>Please try again in a few seconds...</h5>
    </div>
  </div>

  <dl class="sfTMessageInfo">
    <dt>What's next</dt>
    <dd>
      <ul class="sfTIconList">
        <li class="sfTReloadMessage"><a href="javascript:window.location.reload()">Try again: Reload Page</a></li>
      </ul>
    </dd>
  </dl>
</div>
</body>
</html>
EOF;
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/unavailable.php',$contents);

/**
 * Edit the settings.yml file
 */
$settings = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml');
$settings['all']['.settings']['login_module'] = 'default';
$settings['all']['.settings']['login_action'] = 'login';
$settings['all']['.settings']['secure_module'] = 'default';
$settings['all']['.settings']['secure_action'] = 'secure';
$settings['all']['.settings']['error_404_module'] = 'default';
$settings['all']['.settings']['error_404_action'] = 'error404';
$settings['all']['.settings']['module_disabled_module'] = 'default';
$settings['all']['.settings']['module_disabled_action'] = 'disabled';
$settings['all']['.settings']['check_lock'] = true;
$settings['prod']['.settings']['logging_enabled'] = true;
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml',sfYaml::dump($settings,3));

/**
 * Update cookie names
 */
$cookie_name = $this->ask('What do you want to name your cookie?');
$cookie_name = empty($cookie_name) ? 'symfony' : $cookie_name;
$factories = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/factories.yml');
$factories['all']['storage'] = array(
  'class' => 'sfSessionStorage',
  'param' => array(
    'session_name' => $cookie_name
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
file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/factories.yml',sfYaml::dump($factories,7));


/**
 *
 * @param sfGenerateProjectTask $t
 */
function configuresfDoctrineGuardPlugin(sfGenerateProjectTask $t)
{
  $t->getFilesystem()->copy(sfConfig::get('sf_plugins_dir') . '/sfDoctrineGuardPlugin/data/fixtures/fixtures.yml.sample',sfConfig::get('sf_data_dir') . '/fixtures/sfGuard.yml');
  
  /**
   * configure the remember me filter here
   */
  $filters = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/filters.yml');
  file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/filters.yml',sfYaml::dump($filters,10));

  /**
   * enable the module in the settings.yml
   */
  $settings = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml');
  $default_modules = empty($settings['all']['.settings']['default_modules']) ? array('default') : $settings['all']['.settings']['default_modules'];
  array_push($default_modules,'sfGuardAuth');
  $settings = array_merge($settings,array(
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
  file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/settings.yml',sfYaml::dump($settings,10));

  /**
   * update the myUser.class.php
   */
  $contents = file_get_contents(sfConfig::get('sf_apps_dir') . '/frontend/lib/myUser.class.php');
  $contents = preg_replace('/sfBasicSecurityUser/','sfGuardSecurityUser',$contents);
  file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/lib/myUser.class.php',$contents);
}
