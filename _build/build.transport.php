<?php
/**
 * Quip build script
 *
 * @package quip
 * @subpackage build
 */

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0); /* makes sure our script doesnt timeout */

ini_set("memory_limit", "196M");

$root = dirname(dirname(__FILE__)).'/';
$sources= array (
    'root' => $root,
    'build' => $root .'_build/',
    'resolvers' => $root . '_build/resolvers/',
    'data' => $root . '_build/data/',
    'source_core' => $root.'core/components/visioncart',
    'lexicon' => $root . 'core/components/visioncart/lexicon/',
    'source_assets' => $root.'assets/components/visioncart',
    'docs' => $root.'core/components/visioncart/docs/',
);
unset($root); /* save memory */

require_once dirname(dirname(dirname(__FILE__))) . '/core/config/config.inc.php';

define('MODX_CONFIG_KEY','config');

require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx= new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

$modx->loadClass('transport.modPackageBuilder','',false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage('visioncart', '0.2.1', 'beta3');
$builder->registerNamespace('visioncart', false, true, '{core_path}components/visioncart/');
 
// Add system settings
require_once(dirname(__FILE__).'/builder.systemsettings.php');

// Add system settings
require_once(dirname(__FILE__).'/builder.plugins.php');

// Add categories+snippets+chunks and create file vehicle
require_once(dirname(__FILE__).'/builder.categories.php');

// Add files to the category vehicle and put the category vehicle into the builder
require_once(dirname(__FILE__).'/builder.files.php');

// Add modMenu and modAction
require_once(dirname(__FILE__).'/builder.menu.php');

// Add readme and license and pre-setup files
/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
    'setup-options' => array(
        'source' => $sources['build'] . 'setup.options.php'
    )
));

$builder->pack();

$mtime= microtime();
$mtime= explode(" ", $mtime);
$mtime= $mtime[1] + $mtime[0];
$tend= $mtime;
$totalTime= ($tend - $tstart);
$totalTime= sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO,"\nPackage Built.\nExecution time: {$totalTime}\n");
exit();
