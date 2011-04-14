<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: actions.class.php 9999 2008-06-29 21:24:44Z fabien $
 */

class swFunctionalTestSaveActions extends sfActions
{
    public function executeSaveTest($request) {
        $testName = $request->getParameter('test_name');
        $testContent = $request->getParameter('test_content');
        $fileName = sfConfig::get('sf_test_dir')."/functional/".sfConfig::get('sf_app')."/".$testName."Test.php";
        file_put_contents ( $fileName , $testContent );
        $this->redirect('/');
    }
}
