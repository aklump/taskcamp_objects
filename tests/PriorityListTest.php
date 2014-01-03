<?php
/**
 * @file
 * Tests for the PriorityList class
 *
 * @ingroup taskcamp_objects
 * @{
 */

use \AKlump\Taskcamp\Todo as Todo;
use \AKlump\Taskcamp\PriorityList as PriorityList;

require_once '../vendor/autoload.php';

class PriorityListTest extends PHPUnit_Framework_TestCase {

  function testConstruct() {
    $list = new PriorityList();
    $this->assertEquals('', $list->getTitle());
    $this->assertEquals('', $list->getDescription());
  }

  function testGetHTML() {
    $list = new PriorityList('Old Title', 'Old Description');
    $control = <<<EOD
<h1>Old Title</h1>
<p>Old Description</p>

EOD;
    $this->assertEquals($control, $list->getHTML());    

    $control = <<<EOD
<h3>Old Title</h3>
<p>Old Description</p>

EOD;
    $this->assertEquals($control, $list->getHTML(2));    
  }

  function testSetTitleDescription() {
    $list = new PriorityList('Old Title', 'Old Description');
    $control = <<<EOD
Old Title
Old Description

EOD;
    $this->assertEquals($control, (string) $list);
  }

  function testToString() {
    $priorities = new PriorityList("Title", "Description");
    $priorities->getList()
      ->add('mi', new Todo('-- Mi @w10'))
      ->add('do', new Todo('-- Do'))
      ->add('re', new Todo('-- Re'))
      ->add('br', new Todo ('Below here after vacation...'));

    $this->assertCount(4, $priorities->getList()->getSorted()); 

    $control = <<<EOD
Title
Description

- [ ] Do
- [ ] Re
- Below here after vacation...
- [ ] Mi @w10

EOD;

    $this->assertEquals($control, (string) $priorities);
  }
}

/** @} */ //end of group: taskcamp_objects