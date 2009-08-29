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
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class swTestFunctional extends sfTestFunctional
{
  
  /**
   * get var from the last action stack
   * 
   * @param $name name of the var
   * @return mixed
   */
  public function getActionVar($name)
  {

    return $this->browser->getContext()->getActionStack()->getLastEntry()->getActionInstance()->getVar($name);
  }
}