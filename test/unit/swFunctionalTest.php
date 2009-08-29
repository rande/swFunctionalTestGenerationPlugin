<?php

/*
 * This file is part of the swFunctionalTestGenerationPlugin package.
 *
 * (c) 2008 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


include(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(null, new lime_output_color());

class stdObject{}

$filter = new swFilterFunctionalTest(new stdObject, array());

$cases = array(
  'Lorem ipsum dolor sit amet, <a href="consectetur">consectetur</a> adipiscing elit.',
  
  'Fusce convallis luctus diam. Nullam faucibus, <a href="nunc"><img src="/foo.bar" />nunc</a> non viverra pellentesque, nisi dolor iaculis libero, ac commodo ligula nisl vel neque.',
  
  'Fusce convallis luctus diam. Nullam faucibus, <a href="nunc"><img src="/foo.bar" alt="THE ALT VALUE" />nunc</a> non viverra pellentesque, nisi dolor iaculis libero, ac commodo ligula nisl vel neque.',
  
  "Fusce convallis luctus diam. Nullam faucibus, <a href='nunc'><img src='/foo.bar' alt='THE \"ALT\" VALUE' />nunc</a> non viverra pellentesque, nisi dolor iaculis libero, ac commodo ligula nisl vel neque."

);

$expected_results = array(
  'Lorem ipsum dolor sit amet, <a href="consectetur?_sw_func_link=consectetur">consectetur</a> adipiscing elit.',
  
  'Fusce convallis luctus diam. Nullam faucibus, <a href="nunc?_sw_func_link=<img src="/foo.bar" />nunc"><img src="/foo.bar" />nunc</a> non viverra pellentesque, nisi dolor iaculis libero, ac commodo ligula nisl vel neque.',
  
  'Fusce convallis luctus diam. Nullam faucibus, <a href="nunc?_sw_func_link=THE ALT VALUE"><img src="/foo.bar" alt="THE ALT VALUE" />nunc</a> non viverra pellentesque, nisi dolor iaculis libero, ac commodo ligula nisl vel neque.',
  
  "Fusce convallis luctus diam. Nullam faucibus, <a href='nunc?_sw_func_link=THE \"ALT\" VALUE'><img src='/foo.bar' alt='THE \"ALT\" VALUE' />nunc</a> non viverra pellentesque, nisi dolor iaculis libero, ac commodo ligula nisl vel neque."
  
);
foreach($cases as $index => $case)
{
  $content = preg_replace_callback(swFilterFunctionalTest::LINK_EREG, array($filter, 'linkCallback'), $case);
  
  $t->cmp_ok($content, '==', $expected_results[$index], 'get : '.$content);
}

