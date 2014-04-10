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

  public function testAdd() {
    $obj = new ObjectList();
    $obj
      ->add(new Todo('- do'))
      ->add(new Todo('- re'))
      ->add(new Todo('- mi'))
      ->generateIds();

    $obj->add(new Todo('- fa @id3'));
    $obj->add(new Todo('- ray @id1'));
    $obj->add(new Todo('- sol'));

    $control = <<<EOD
- [ ] do @id0
- [ ] re @id1
- [ ] mi @id2
- [ ] fa @id3
- [ ] sol
EOD;
    $this->assertSame($control, (string) $obj);
  }

  public function testReplace() {
    $obj = new ObjectList();
    $obj
      ->add(new Todo('- do'))
      ->add(new Todo('- ray'))
      ->add(new Todo('- mi'))
      ->generateIds();

    $return = $obj->replace(3, new Todo('- fa'), FALSE);
    $this->assertSame(0, $return);

    $return = $obj->replace(3, new Todo('- fa'));
    $this->assertSame(2, $return);

    $return = $obj->replace(1, new Todo('- re'));
    $this->assertSame(1, $return);

    $control = <<<EOD
- [ ] do @id0
- [ ] re @id1
- [ ] mi @id2
- [ ] fa @id3
EOD;
    $this->assertSame($control, (string) $obj);
  }

  public function testAutoIncrement() {
    $obj = new ObjectList();
    $return = $obj->generateIds(100);
    $this->assertSame(-1, $return);

    $this->assertSame(100, $obj->getNextId(100));
    
    $obj->add(new Todo('- a todo @id"goat heard"'));
    $this->assertSame(100, $obj->getNextId(100));

    $obj->add(new Todo('- another todo'));
    $this->assertSame(101, $obj->getNextId(100));
    
    $obj->add(new Todo('- another todo'));
    $this->assertSame(102, $obj->getNextId(100));

    $obj->add(new Todo('- another todo'));
    $this->assertSame(103, $obj->getNextId(100));

    $obj->generateIds(100);
    $this->assertSame(103, $obj->getNextId());
  }

  public function testGetNextId() {
    $obj = new ObjectList();
    $this->assertSame(0, $obj->getNextId());
    
    $obj->add(new Todo('- a todo @id"goat heard"'));
    $this->assertSame(0, $obj->getNextId());

    $obj->add(new Todo('- another todo'));
    $this->assertSame(1, $obj->getNextId());
    
    $obj->add(new Todo('- another todo'));
    $this->assertSame(2, $obj->getNextId());

    $obj->add(new Todo('- another todo'));
    $this->assertSame(3, $obj->getNextId());

    $obj->generateIds();
    $this->assertSame(3, $obj->getNextId());
  }

  function testGenerateIds() {
    $obj = new ObjectList();
    $todo = new Todo('- do this', array('show_ids' => FALSE));
    $todo->setFlag('id', 'do');
    $obj->add($todo);
    $obj->add(new Todo('- do this now', array('show_ids' => FALSE)));
    $return = $obj->generateIds();
    $this->assertSame(0, $return);
    $result = reset($obj->get(0));
    $this->assertSame('do this now', $result->getTitle());

    $obj = new ObjectList();
    $obj->add(new Todo('- do this', array('show_ids' => FALSE)));
    $obj->add(new Todo('- do this', array('show_ids' => FALSE)));
    $obj->add(new Todo('- do this', array('show_ids' => FALSE)));
    $obj->add(new Todo('- do this last', array('show_ids' => FALSE)));
    $obj->generateIds();

    $result = reset($obj->get(3));
    $this->assertSame('do this last', $result->getTitle());
    $this->assertSame('3', $result->getFlag('id', TRUE));

    $obj->generateIds();
    $result = reset($obj->get(3));
    $this->assertSame('do this last', $result->getTitle());
    $this->assertSame('3', $result->getFlag('id', TRUE));

    $todos = $obj->get();
    $todo = array_shift($todos);
    $this->assertSame('0', $todo->getFlag('id'));
    $todo = array_shift($todos);
    $this->assertSame('1', $todo->getFlag('id'));
    $todo = array_shift($todos);
    $this->assertSame('2', $todo->getFlag('id'));

    $obj = new ObjectList();
    $todo = new Todo('- do this', array('show_ids' => FALSE));
    $todo->setFlag('id', 7);
    $obj->add($todo);
    $obj->add(new Todo('- do this now', array('show_ids' => FALSE)));
    $obj->generateIds();
    $result = reset($obj->get(8));
    $this->assertSame('do this now', $result->getTitle());
  

  }

  /**
   * Factory method to get a new list
   *
   * @return ObjectList
   */
  function getList() {
    $list = new ObjectList();
    $items = array(
      new Todo('- layout the bread', array('show_ids' => FALSE)),
      new Todo('- eat it @w10', array('show_ids' => FALSE)),
      new Todo('- spread the PB', array('show_ids' => FALSE)),
      new Todo('- spread the honey', array('show_ids' => FALSE)),
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
    $obj->generateIds();

    $result = reset($obj->get(3));
    $this->assertSame('do this last', $result->getTitle());
    $this->assertSame('3', $result->getFlag('id', TRUE));
  }

  function testToString() {
    $list = $this->getList();
    $control = <<<EOD
- [ ] layout the bread
- [ ] spread the PB
- [ ] spread the honey
- [ ] eat it @w10
EOD;
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
    $this->assertEquals(4, count($list->get()), 'Item was replaced not appended.');
    
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