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
class swFunctionalUnitTestDebugPanel extends sfWebDebugPanel
{
  private $default_formatter = 'swTestPropelFormatter';

  public function getTitle()
  {
    
    return 'Functional Test';
  }
  
  public function getPanelTitle()
  {
    
    return 'Functional Test (autogenerated)';
  }
  
  public function getPanelContent()
  {
    $config = sfYaml::load(sfConfig::get('sf_config_dir').'/test_generation.yml');
    
    if(isset($config['default']) && isset($config['default']['formatter'])) {
      $formatter_class = $config['default']['formatter'];
    } else {
      $formatter_class = $this->default_formatter;
    }
    
    $formatter = new $formatter_class();
    
    if(!$formatter instanceof swTestFunctionalFormatter) {
      throw new InvalidArgumentException('invalid formatter');
    }
    
    return '
      <div id="sfDebugPanelFunctionalUnitTest"><div style="float:left">'.
      "<a href='?_sw_func_reset=1'>Reset</a> - ".
      (!sfContext::getInstance()->getUser()->getAttribute('sw_func_enabled', false, 'swToolbox') ?
      "<a href='?_sw_func_enabled=1'>Activate</a>" :
      "<a href='?_sw_func_enabled=0'>Deactivate</a>")
      .'<br /><form action="'.url_for('swFunctionalTestSave/saveTest').'" method="post" ><textarea name="test_content" style="width:500px; height: 200px; font-family:courier">'
      .$formatter->build(swFilterFunctionalTest::getRawPhp()).
      '</textarea>
      <br/>
      Test Name : <input type="text" size="20" name="test_name"/>
      <input type="Submit" name="Save Test" value="Save Test" /><br/><br/>
      </form>
      </div>
     </div>
    ';
  }
  
  static public function listenToAddPanelEvent(sfEvent $event)
  {
    $event->getSubject()->setPanel('sw.functional_unit_test', new self($event->getSubject()));
  }
}