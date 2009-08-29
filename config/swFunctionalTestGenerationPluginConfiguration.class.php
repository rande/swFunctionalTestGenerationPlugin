<?php

/*
 * This file is part of the swFunctionalTestGenerationPlugin package.
 *
 * (c) 2008 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class swFunctionalTestGenerationPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    if($this->configuration instanceof sfApplicationConfiguration)
    {

    }
    
    // functionnal test debug panel
    if (sfConfig::get('sf_web_debug'))
    {
      
      $this->dispatcher->connect('debug.web.load_panels', array('swFunctionalUnitTestDebugPanel', 'listenToAddPanelEvent'));
    }
  }
}