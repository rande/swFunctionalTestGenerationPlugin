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
abstract class swTestFunctionalFormatter {
  abstract public function getHeader();
  abstract public function getFooter();

  public function build($tests) {
    return htmlspecialchars($this->getHeader() . "\n" . $tests . "\n" . $this->getFooter(), ENT_COMPAT, 'UTF-8');
  }
}