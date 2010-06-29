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
   * Initializes the browser tester instance.
   *
   * @param sfBrowserBase $browser A sfBrowserBase instance
   * @param lime_test     $lime    A lime instance
   */
  public function __construct(sfBrowserBase $browser, lime_test $lime = null, $testers = array())
  {
    
    // Since symfony 1.4.5
    if($lime === null)
    {
      $lime = new lime_test(0, array(
        'error_reporting' => true,
        'verbose'         => true
      ));
    }
    
    $testers = array_merge(array(
      'view_cache' => 'sfTesterViewCache',
      'form'       => 'sfTesterForm',
    ), $testers);

    parent::__construct($browser, $lime, $testers);
  }
  
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

  public function call($uri, $method = 'get', $parameters = array(), $changeStack = true)
  {
    $conn = Doctrine::getConnectionByTableName('sf_guard_user');
    $conn->clear();
    
    return parent::call($uri, $method, $parameters, $changeStack);
  }
  
  /**
   * @param string $user 
   * @param string $password
   */
  public function login($user = 'thomas.rabaix@soleoweb.com', $password = 'test')
  {
    $this
      ->call('/login', 'post', array (
        'signin' =>
          array (
            'username' => $user,
            'password' => $password,
          ),
        ))
      ->with('request')->begin()
        ->isParameter('module', 'sfGuardAuth')
        ->isParameter('action', 'signin')
      ->end()
    ;

    $this
      ->with('response')->begin()
        ->isRedirected(1)
        ->isStatusCode(302)
        ->followRedirect()
      ->end()
    ;

    if (!$this->getContext()->getUser()->getGuardUser())
    {
      $this->test()->fail('No sfGuardUser linked to : '.$user);
      die();
    }

    $this->test()->ok($this->getContext()->getUser()->getGuardUser()->getUsername() == $user, 'user is '.$user);

    return $this;
  }
  
  
  public function logout($restart = true, $redirect = true)
  {
    $this
      ->call('/logout', 'post', array ())
      ->with('request')->begin()
        ->isParameter('module', 'sfGuardAuth')
        ->isParameter('action', 'signout')
      ->end()
    ;

    if($redirect)
    {
      $this
        ->with('response')->begin()
          ->isRedirected(1)
          ->isStatusCode(302)
        ->end()
        ->followRedirect();
    }
    else
    {
      $this
        ->with('response')->begin()
          ->isStatusCode(200)
        ->end();
    }

    if ($restart)
    {
      $this->info('Restarting Browser');
      $this->browser->restart();
    }

    $this->test()->ok(!$this->getContext()->getUser()->isAuthenticated(), 'user is not authenticated anymore');

    return $this;
  }
  
}