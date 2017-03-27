<?php
/**
 * @file
 * Tests for the Priorities class
 *
 * @ingroup taskcamp_objects
 * @{
 */

use \AKlump\Taskcamp\Todo as Todo;
use \AKlump\Taskcamp\Priorities as Priorities;



class PrioritiesTest extends PHPUnit_Framework_TestCase {

  function testConstruct() {
    $list = new Priorities();
    $this->assertEquals('', $list->getTitle());
    $this->assertEquals('', $list->getDescription());
  }

  function testGetHTML() {
    $list = new Priorities('Old Title', 'Old Description');
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
    $list = new Priorities('Old Title', 'Old Description');
    $control = <<<EOD
Old Title
Old Description

EOD;
    $this->assertEquals($control, (string) $list);
  }

  function testToString() {
    $priorities = new Priorities("Title", "Description");
    $priorities->getList()
      ->add(new Todo('- Mi @w10'))
      ->add(new Todo('- Do'))
      ->add(new Todo('- Re'))
      ->add(new Todo ('Below here after vacation...'));

    $this->assertCount(4, $priorities->getList()->getSorted()); 

    $control = <<<EOD
Title
Description

- [ ] Do
- [ ] Re
Below here after vacation...
- [ ] Mi @w10
EOD;

    $this->assertEquals($control, (string) $priorities);
  }
}

/** @} */ //end of group: taskcamp_objects
