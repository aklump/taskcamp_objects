<?php
/**
 * @file
 * Tests for the ObjectList class
 *
 * @ingroup taskcamp_objects
 * @{
 */
require_once '../vendor/autoload.php';
use \AKlump\Taskcamp\ObjectList as ObjectList;
use \AKlump\Taskcamp\Todo as Todo;

class ObjectListTest extends PHPUnit_Framework_TestCase {

  /**
   * Factory method to get a new list
   *
   * @return ObjectList
   */
  function getList() {
    $list = new ObjectList();
    $items = array(
      new Todo('- layout the bread'),
      new Todo('- eat it @w10'),
      new Todo('- spread the PB'),
      new Todo('- spread the honey'),
    );
    foreach ($items as $item) {
      $item->setFlag('id', $item->getTitle());
      $list->add($item);
    }    

    return $list;
  }

  function testAutoIds() {
    $obj = new ObjectList();
    $obj->add(new Todo('- do this'));
    $obj->add(new Todo('- do this'));
    $obj->add(new Todo('- do this'));
    $obj->add(new Todo('- do this last'));

    $result = reset($obj->get(3));
    $this->assertSame('do this last', $result->getTitle());
    $this->assertSame('', $result->getFlag('id', TRUE));
  }

  function testToString() {
    $list = $this->getList();
    $control = '- [ ] layout the bread
- [ ] spread the PB
- [ ] spread the honey
- [ ] eat it @w10
';
    $this->assertEquals($control, (string) $list);
  }  

  function testGetSorted() {
    $list = $this->getList();
    $return = $list->getSorted();
    $this->assertTrue(is_array($return));

    $result = array();
    foreach ($return as $item) {
      $result[] = $item->getTitle();
    }
    $control = array (
      0 => 'layout the bread',
      1 => 'spread the PB',
      2 => 'spread the honey',
      3 => 'eat it',
    );
    $this->assertEquals($control, $result);
  }

  function testAddGet() {
    $list = $this->getList();
    $this->assertEquals(4, count($list->get()));

    $subject = new Todo('- layout the bread carefully');
    $id = 'layout the bread';
    $subject->setFlag('id', $id);
    $return = $list->add($subject);
    $this->assertInstanceOf('AKlump\Taskcamp\ObjectList', $return);
    $this->assertEquals(5, count($list->get()), 'Item was replaced not appended.');
    
    // $result = reset($list->get($id));
    // $this->assertEquals($subject, $result, 'Title was updated.');

    // $this->assertEquals(4, $list->count());
  }

  function testRemove() {
    $list = $this->getList();
    $list->remove();
    $this->assertEquals(0, count($list->get()), 'All items removed.');

    $list = $this->getList();
    $return = $list->remove('layout the bread');
    $this->assertInstanceOf('AKlump\Taskcamp\ObjectList', $return);
    $this->assertEquals(3, count($list->get()), 'All items removed.');

    $list->remove('eat it');
    $this->assertEquals(2, count($list->get()), 'All items removed.');

    $list->remove('spread the PB');
    $this->assertEquals(1, count($list->get()), 'All items removed.');

    $list->remove('spread the honey');
    $this->assertEquals(0, count($list->get()), 'All items removed.');
  }
}

/** @} */ //end of group: taskcamp_objects