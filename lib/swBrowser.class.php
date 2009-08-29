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
class swBrowser extends sfBrowser
{

  /**
   *
   * add file to browser, use this method when you POST data to a functional test
   * the implementation in sf works only with 'click' event
   * 
   * @param $elementName string ie, myform[data]
   * @param $filename string path to the file
   * @param $type string mimetype of the file
   * @param $create_tmp boolean create a tmp file of the filename, so original file is never altered, default is true
   *
   * @return swBrowser
   **/
  public function addFile($elementName, $filename, $type = '', $create_tmp = true)
  {

    if (is_readable($filename))
    {
      if($create_tmp)
      {
        $temp_name = tempnam(sys_get_temp_dir(), 'sf_test_file_').'_'.basename($filename);
        copy($filename, $temp_name);

        $filename = $temp_name;
      }

      $fileError = UPLOAD_ERR_OK;
      $fileSize = filesize($filename);
    }
    else
    {
      $fileError = UPLOAD_ERR_NO_FILE;
      $fileSize = 0;
    }

    $this->parseArgumentAsArray($elementName, array(
      'name' => basename($filename),
      'type' => $type,
      'tmp_name' => $filename,
      'error' => $fileError,
      'size' => $fileSize
    ), $this->files);

    return $this;
  }

}