<?php

/*
 * This file is part of the swFunctionalTestGenerationPlugin package.
 *
 * (c) 2008 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(ticks = 1);

class lime_harness_fork extends lime_harness
{

  public
    $output_dir;

  // used by the parent process
  protected
    $cpt = 0,
    $started = 0,
    $fork_limit;

  // used by the child process
  protected
    $file,
    $pid,
    $ppid;

  const
    SEPARATOR = "\n---|---|---\n";

  public function __construct($output_instance, $php_cli = null, $process = 3)
  {
    parent::__construct($output_instance, $php_cli);

    $this->fork_limit = $process;
  }

  public function sig_handler($signo)
  {

    switch ($signo) {
     case SIGHUP:
       $this->cpt--;

       break;
     case SIGTERM:
       $this->cpt--;

       break;
     case SIGCHLD:
       $this->cpt--;
       break;
    }
  }

  public function output_folder()
  {

    if(!$this->output_dir)
    {

      throw new Exception('please provide an output_dir folder');
    }

    return $this->output_dir;
  }

  public function init_output_folder()
  {

    if(is_dir($this->output_folder()))
    {
      $files = sfFinder::type('file')->name('*.log')->in($this->output_folder());

      foreach($files as $file)
      {
        unlink($file);
      }
    }
    else
    {
      mkdir($this->output_folder());
    }
  }

  public function save_process_output($lines)
  {
    $filename = $this->output_folder().'/'.$this->pid.'_output.log';

    $content = $this->file.self::SEPARATOR.$this->return.self::SEPARATOR.$lines;

    file_put_contents($filename, $content);

    return;
  }

  public function run()
  {
    if (!count($this->files))
    {

      throw new Exception('You must register some test files before running them!');
    }


    // sort the files to be able to predict the order
    sort($this->files);

    // install signal handler for dead kids
    pcntl_signal(SIGCHLD, array($this,"sig_handler"));
    pcntl_signal(SIGTERM, array($this,"sig_handler"));
    pcntl_signal(SIGHUP, array($this,"sig_handler"));

    $this->cpt = 0;
    $cpt_file = 0;

    $this->pid = posix_getpid();

    $this->init_output_folder();

    while(true)
    {
      //echo "[$current_pid] \$loop=$loop, \$cpt=${this->cpt}, \$fork_limit=$fork_limit, \$cpt_file=$cpt_file \n";

      $file = null;

      // wait to have a fork finish
      if($this->cpt < $this->fork_limit && $cpt_file < count($this->files))
      {
        $file = $this->files[$cpt_file];
        $cpt_file++;
        $this->cpt++;
        $this->started++;
        $children_pid = pcntl_fork();

        if($children_pid == -1) {
           $this->output->echoln("[$this->pid] Error creating fork");
        }
        elseif($children_pid == 0)
        {
          $current_pid = posix_getpid();

          $this->file = $file;
          $this->ppid = posix_getppid();
          $this->pid  = posix_getpid();
          $num_files = count($this->files);
          $size = sizeof($num_files);
          $this->output->echoln(sprintf("%0{$size}d/%{$size}d - executing : %s",
           $this->started,
           $num_files,
           $file
          ));

          ob_start(array($this, 'save_process_output'));
          // see http://trac.symfony-project.org/ticket/5437 for the explanation on the weird "cd" thing
          passthru(sprintf('cd & "%s" "%s" 2>&1', $this->php_cli, $this->file), $return);
          $this->return = $return;
          ob_end_clean();

          exit();
        }
        else
        {
          $children[] = $children_pid;
          $current_pid = posix_getpid();
        }
      }
      else if($cpt_file > count($this->files) - 1)
      {
        $this->output->echoln("all files has been started, now wait for all child to complete");

        while(pcntl_wait($status, WNOHANG OR WUNTRACED) > 0) {
          usleep(5000);
        }

        break;
      }

      usleep(50000);
    }

    $this->output->echoln("analysing output");

    $this->analyse_output();
  }

  public function analyse_output()
  {

    $this->stats =array(
      '_failed_files' => array(),
      '_failed_tests' => 0,
      '_nb_tests'     => 0,
    );

    $files = sfFinder::type('file')
      ->name('*.log')
      ->in($this->output_folder());

    foreach($files as $log_file)
    {

      $this->output->echoln('Analysing : '.$log_file);

      $content = file_get_contents($log_file);

      list($file, $return, $lines) = explode(self::SEPARATOR, $content);

      $this->stats[$file] = array(
        'plan'     =>   null,
        'nb_tests' => 0,
        'failed'   => array(),
        'passed'   => array(),
      );

      $this->current_test = 0;
      $this->current_file = $file;
      $this->process_test_output($lines);

      $_failed_test = $this->stats['_failed_tests'];

      $this->add_stats($file, $return);

      // rename output file
      // /base/path/app/rep/baseTest.php => rep.baseTest.php
      $new_name = str_replace($this->base_dir, '', $file);
      $new_name = str_replace(array('/', '.php'), array('_', '.log'), $new_name);
      $new_name = $this->output_folder().'/'.$new_name;

      rename($log_file, $new_name);

      // TODO : make this optional
      if($_failed_test == $this->stats['_failed_tests'])
      {
        unlink($new_name);
      }
    }

    return $this->analyse_stats();

  }

  /*
   * Fabien : Maybe you can set this method as protected ...
   *
   */
  private function process_test_output($lines)
  {
    foreach (explode("\n", $lines) as $text)
    {
      if (false !== strpos($text, 'not ok '))
      {
        ++$this->current_test;
        $test_number = (int) substr($text, 7);
        $this->stats[$this->current_file]['failed'][] = $test_number;

        ++$this->stats[$this->current_file]['nb_tests'];
        ++$this->stats['_nb_tests'];
      }
      else if (false !== strpos($text, 'ok '))
      {
        ++$this->stats[$this->current_file]['nb_tests'];
        ++$this->stats['_nb_tests'];
      }
      else if (preg_match('/^1\.\.(\d+)/', $text, $match))
      {
        $this->stats[$this->current_file]['plan'] = $match[1];
      }
    }

    return;
  }

  public function add_stats($file, $return)
  {

    if ($return > 0)
    {
      $this->stats[$file]['status'] = 'dubious';
      $this->stats[$file]['status_code'] = $return;
    }
    else
    {
      $delta = $this->stats[$file]['plan'] - $this->stats[$file]['nb_tests'];
      if ($delta > 0)
      {
        $this->output->echoln(sprintf('%s%s%s', substr($relative_file, -min(67, strlen($relative_file))), str_repeat('.', 70 - min(67, strlen($relative_file))), $this->output->colorizer->colorize(sprintf('# Looks like you planned %d tests but only ran %d.', $this->stats[$file]['plan'], $this->stats[$file]['nb_tests']), 'COMMENT')));
        $this->stats[$file]['status'] = 'dubious';
        $this->stats[$file]['status_code'] = 255;
        $this->stats['_nb_tests'] += $delta;
        for ($i = 1; $i <= $delta; $i++)
        {
          $this->stats[$file]['failed'][] = $this->stats[$file]['nb_tests'] + $i;
        }
      }
      else if ($delta < 0)
      {
        $this->output->echoln(sprintf('%s%s%s', substr($relative_file, -min(67, strlen($relative_file))), str_repeat('.', 70 - min(67, strlen($relative_file))), $this->output->colorizer->colorize(sprintf('# Looks like you planned %s test but ran %s extra.', $this->stats[$file]['plan'], $this->stats[$file]['nb_tests'] - $this->stats[$file]['plan']), 'COMMENT')));
        $this->stats[$file]['status'] = 'dubious';
        $this->stats[$file]['status_code'] = 255;
        for ($i = 1; $i <= -$delta; $i++)
        {
          $this->stats[$file]['failed'][] = $this->stats[$file]['plan'] + $i;
        }
      }
      else
      {
        $this->stats[$file]['status_code'] = 0;
        $this->stats[$file]['status'] = $this->stats[$file]['failed'] ? 'not ok' : 'ok';
      }
    }

    $this->output->echoln(sprintf('%s%s%s', substr($relative_file, -min(67, strlen($relative_file))), str_repeat('.', 70 - min(67, strlen($relative_file))), $this->stats[$file]['status']));
    if (($nb = count($this->stats[$file]['failed'])) || $return > 0)
    {
      if ($nb)
      {
        $this->output->echoln(sprintf("    Failed tests: %s", implode(', ', $this->stats[$file]['failed'])));
      }
      $this->stats['_failed_files'][] = $file;
      $this->stats['_failed_tests']  += $nb;
    }

    if ('dubious' == $this->stats[$file]['status'])
    {
      $this->output->echoln(sprintf('    Test returned status %s', $this->stats[$file]['status_code']));
    }
  }

  public function analyse_stats()
  {
    if (count($this->stats['_failed_files']))
    {
      $format = "%-30s  %4s  %5s  %5s  %s";
      $this->output->echoln(sprintf($format, 'Failed Test', 'Stat', 'Total', 'Fail', 'List of Failed'));
      $this->output->echoln("------------------------------------------------------------------");

      foreach ($this->stats as $file => $file_stat)
      {
        if (!in_array($file, $this->stats['_failed_files'])) continue;

        $relative_file = $this->get_relative_file($file);
        $this->output->echoln(sprintf($format, substr($relative_file, -min(30, strlen($relative_file))), $file_stat['status_code'], count($file_stat['failed']) + count($file_stat['passed']), count($file_stat['failed']), implode(' ', $file_stat['failed'])));
      }

      $this->output->red_bar(sprintf('Failed %d/%d test scripts, %.2f%% okay. %d/%d subtests failed, %.2f%% okay.',
        $nb_failed_files = count($this->stats['_failed_files']),
        $nb_files = count($this->files),
        ($nb_files - $nb_failed_files) * 100 / $nb_files,
        $nb_failed_tests = $this->stats['_failed_tests'],
        $nb_tests = $this->stats['_nb_tests'],
        $nb_tests > 0 ? ($nb_tests - $nb_failed_tests) * 100 / $nb_tests : 0
      ));
    }
    else
    {
      $this->output->green_bar(' All tests successful.');
      $this->output->green_bar(sprintf(' Files=%d, Tests=%d', count($this->files), $this->stats['_nb_tests']));
    }

    return $this->stats['_failed_files'] ? false : true;
  }

}