<?php
/*
 * This file is part of the swFunctionalTestGenerationPlugin package.
 *
 * (c) 2008 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 *
 * @package    swToolboxPlugin
 * @subpackage debug
 * @author     Sebastian Schmidt <info@schmidt-seb.de>
 * @version    SVN: $Id$
 */
class swTestPropelFormatter extends swTestFunctionalFormatter {
  public function getHeader() {
    return '<?php

include(dirname(__FILE__).\'/../../bootstrap/functional.php\');

$browser = new sfTestFunctional(new sfBrowser());
$test    = $browser->test();
$conn    = Propel::getConnection();

$conn->beginTransaction();';
  }

  public function getFooter() {
    return '$conn->rollback();';
  }
}