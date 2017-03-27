<?php
/**
 * @file
 * Tests for the File class
 *
 * @ingroup taskcamp_objects
 * @{
 */

use \AKlump\Taskcamp\File as File;


class FileTest extends PHPUnit_Framework_TestCase {

  public function newFile($contents, $extension) {
    $path = tempnam(sys_get_temp_dir(), '') . '.' . trim($extension, '.');
    if ($fp = fopen($path, 'w')) {
      fwrite($fp, $contents);
      fclose($fp);
    }

    return $path;
  }

  public function testExists() {
    $file = new File(md5(time()));
    $this->assertFalse($file->getParsed('exists'));

    $path = $this->newFile('', 'html');
    $file = new File($path);
    $this->assertTrue($file->getParsed('exists'));
  }

  public function testGetHTML() {
    $subject = '<h1>Title</h1>';
    $path = $this->newFile($subject, 'html');
    $file = new File($path);
    
    $basename = pathinfo($path, PATHINFO_BASENAME);
    $control = <<<EOD
<h1>$basename</h1>
<p>$path</p>
<pre><code><h1>Title</h1></code></pre>
EOD;
    $this->assertEquals($control, $file->getHTML());
  
    $path = $this->newFile($subject, 'txt');
    $basename = pathinfo($path, PATHINFO_BASENAME);
    $control = <<<EOD
<h3>$basename</h3>
<p>$path</p>
<pre><code><h1>Title</h1></code></pre>
EOD;
    $file = new File($path);
    $this->assertEquals($control, $file->getHTML(2));
  }

  public function testTitleDescription() {
    $path = $this->newFile('', 'txt');
    $file = new File($path);
    $this->assertEquals(pathinfo($path, PATHINFO_BASENAME), $file->getTitle());
    $this->assertEquals($path, $file->getDescription());
    $this->assertEquals('txt', $file->getParsed('extension'));

    $file->setSource("$path @bc123456 @m2014-01-15");
    $this->assertEquals(pathinfo($path, PATHINFO_BASENAME), $file->getTitle());
    $this->assertEquals($path, $file->getDescription());
    $this->assertEquals(123456, $file->getFlag('basecamp'));
    $this->assertNull($file->getFlag('milestone'));
  }

}

/** @} */ //end of group: taskcamp_objects
