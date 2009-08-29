<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/..');

require_once(dirname(__FILE__).'/../../../../config/ProjectConfiguration.class.php');
$configuration = new ProjectConfiguration();
include($configuration->getSymfonyLibDir().'/vendor/lime/lime.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true);
$context = sfContext::createInstance($configuration);
