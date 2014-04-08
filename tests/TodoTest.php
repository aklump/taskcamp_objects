<?php
/**
 * @file
 * Tests for the TodoTest class
 *
 * @ingroup taskcamp_objects
 * @{
 */
require_once '../vendor/autoload.php';
use \AKlump\Taskcamp\Todo as Todo;

class TodoTest extends PHPUnit_Framework_TestCase {

  function testEscapingAFlag() {
    $obj = new Todo('- Can we escape \@w300 a 300 weight');
    $this->assertSame('Can we escape \@w300 a 300 weight', $obj->getTitle());
  }

  function testId() {
    $obj = new Todo('- Do this @w-10', array('show_ids' => FALSE));
    $obj->setFlag('id', 'apple');
    $this->assertSame('apple', $obj->getFlag('id'));
    $this->assertSame('- [ ] Do this @w-10', (string) $obj);

    $obj = new Todo('- Do this @w-10', array('show_ids' => TRUE));
    $obj->setFlag('id', 'apple');
    $this->assertSame('apple', $obj->getFlag('id'));
    $this->assertSame('- [ ] Do this @idapple @w-10', (string) $obj);
  
    $obj->setFlag('id', '1234');
    $this->assertSame('1234', $obj->getFlag('id'));
    $this->assertSame('- [ ] Do this @id1234 @w-10', (string) $obj);

    $obj->setFlag('id', 'apple muffin');
    $this->assertSame('apple muffin', $obj->getFlag('id'));
    $this->assertSame('- [ ] Do this @id"apple muffin" @w-10', (string) $obj);    

    $obj = new Todo('- Do this @idapple', array('show_ids' => TRUE));
    $this->assertSame('apple', $obj->getFlag('id'));
    $this->assertSame('- [ ] Do this @idapple', (string) $obj);

    $obj = new Todo('- Do this @id1234', array('show_ids' => TRUE));
    $this->assertSame('1234', $obj->getFlag('id'));
    $this->assertSame('- [ ] Do this @id1234', (string) $obj);

    $obj = new Todo('- Do this @id"apple muffin"', array('show_ids' => TRUE));
    $this->assertSame('apple muffin', $obj->getFlag('id'));
    $this->assertSame('- [ ] Do this @id"apple muffin"', (string) $obj);
  }

  function testGetFlagDate() {
    $obj = new Todo('- [ ] finish this thing @s16:06 @d2014-04-13T12:54', array('timezone' => 'UTC'));
    $control = new \DateTime('2014-04-13T12:54', new \DateTimeZone('UTC'));
    $return = $obj->getFlag('done', TRUE);
    $this->assertEquals($control, $return);

    $control = new \DateTime('now', new \DateTimeZone('UTC'));
    $control->setTime(16, 06, 00);
    $return = $obj->getFlag('start', TRUE);
    $this->assertEquals($control, $return);

    $obj = new Todo('- [ ] finish this thing', array('timezone' => 'UTC'));
    $control = NULL;
    $return = $obj->getFlag('done', TRUE);
    $this->assertEquals($control, $return);
  }

  function testStaticMethods() {
    $this->assertNotEmpty(Todo::dateRegex());
  }

  function testToStringInvalidTodo() {
    $control = '// Some comment';
    $todo = new Todo($control);
    $this->assertEquals('- ' . $control, (string) $todo);
  }

  function testWeight() {
    $todo = new Todo('-something @w-10');
    $this->assertEquals(-10, $todo->getFlag('weight'));

    $todo = new Todo('-something @w10');
    $this->assertEquals(10, $todo->getFlag('weight'));
  }

  function testDateStrings() {
    $datetime = '2004-02-12T15:19:21+00:00';
    $todo = new Todo('', array('timezone' => 'UTC'));
    $this->assertEquals('2004-02-12', $todo->getDate($datetime));
    $this->assertEquals('15:19', $todo->getTime($datetime));
    $this->assertEquals('2004-02-12T15:19', $todo->getDateTime($datetime));
  }

  function testMilestone() {
    $todo = new todo('- decide on palette @m2014-03-15');
    $this->assertEquals('2014-03-15', $todo->getFlag('milestone'));

    $todo = new todo('- launch homepage @m', array('milestone' => 86400 * 7, 'timezone' => 'America/Los_Angeles'));
    $then = $todo->getDateTime('+7 days');
    $this->assertEquals("- [ ] launch homepage @m$then", (string) $todo, 'Milestone default date is correct based on config.');
  }  

  function testGetFlags() {
    $todo = new Todo('- my item to get done @pJoe @e3.5 @s2014-01-31T13:44 @m2014-02-14 @bc123456 @w4 @d13:44');
    $this->assertEquals('@pJoe @bc123456 @m2014-02-14 @e3.5 @s2014-01-31T13:44 @d13:44 @w1004', $todo->getFlags(), 'getFlags() returns all values as expected');
  }

  function testDuration() {

    // Test creating a date which is a time string and has context, pulls
    // the context day into the time string
    $todo = new Todo('- a long time ago @s11:47 @d2000-01-01T12:47');
    $this->assertEquals(3600, $todo->getDuration(), 'Assert start without date uses the date element from the done flag for duration');

    $todo = new Todo('- a long time ago @s2000-01-01T11:47 @d12:47');
    $this->assertEquals(3600, $todo->getDuration(), 'Assert done without date uses the date element from the start flag for duration');

    $todo = new todo('- design the logo @s15:12');
    $this->assertEquals(FALSE, $todo->getDuration());

    $todo = new todo('- design the logo @s15:12 @d16:12');
    $this->assertEquals(3600, $todo->getDuration());

    $todo = new todo('- design the logo @s2014-01-01T15:12 @d2014-01-02T16:12');
    $this->assertEquals(3600 + 86400, $todo->getDuration());
  }

  function testCarryover() {
    $todo = new todo('- design the logo @s15:12 @d16:12');
    $this->assertSame(FALSE, $todo->getCarryover());

    $todo = new todo('- design the logo @e1 @s15:12 @d16:12');
    $this->assertSame(0, $todo->getCarryover());

    $todo = new todo('- design the logo @e.5 @s15:12 @d16:12');
    $this->assertEquals(-1800, $todo->getCarryover());

    $todo = new todo('- design the logo @e1.5 @s15:12 @d16:12');
    $this->assertEquals(1800, $todo->getCarryover());
  }

  function testStart() {
    $todo = new todo('- finish the design @s');
    $now = $todo->getTime();
    $this->assertEquals("- [ ] finish the design @s$now", (string) $todo, 'Assert time is appended to @s');
  }

  function testShorthand() {
    $variations = array(
      '-a todo item',
      '- a todo item',
      '-[]a todo item',
      '-[ ]a todo item',
      '- []a todo item',
    );
    foreach ($variations as $variation) {
      $todo = new todo($variation);
      $this->assertEquals('- [ ] a todo item', (string) $todo, "'$variation' works");
    }
    $variations = array(
      '-[X]a todo item',
      '-[X ]a todo item',
      '-[x]a todo item',
      '-[x ]a todo item',
      '- [X]a todo item',
      '- [X ]a todo item',
      '- [x]a todo item',
      '- [x ]a todo item',
      '-xa todo item',
      '-Xa todo item',
      '-x a todo item',
      '-X a todo item',
      '- [ ] a todo item x',
      '- [ ] a todo itemxx',
    );
    foreach ($variations as $variation) {
      $todo = new todo($variation);
      $this->assertEquals('- [x] a todo item', (string) $todo, "'$variation' passes.");
    }

  }

  function testComplete() {
    $todo = new todo('- [ ] Do css');
    $now = $todo->getDateTime();
    $todo->complete($now);
    $this->assertEquals("- [x] Do css @d$now @w1000", (string) $todo, 'Completing incomplete with date arg marks is done.');

    $todo->complete($todo->getTime());
    $this->assertEquals("- [x] Do css @d$now @w1000", (string) $todo, 'Completing complete makes no change.');

    $todo = new todo('- [ ] Do css');
    $now = $todo->getTime();
    $todo->complete($now);
    $this->assertEquals("- [x] Do css @d$now @w1000", (string) $todo, 'Completing incomplete with time arg marks is done.');

    $todo = new todo('- [ ] Do css');
    $now = $todo->getDateTime();
    $return = $todo->complete();
    $this->assertEquals("- [x] Do css @d$now @w1000", (string) $todo, 'Completing incomplete with no arg marks is done.');
    $this->assertEquals($todo, $return, '$this is returned');

    $todo = new todo('- [ ] important task @w50', array('weight' => 100));
    $this->assertEquals('- [ ] important task @w50', (string) $todo);
    $time = $todo->getTime();
    $todo->complete($time);
    $this->assertEquals("- [x] important task @d$time @w150", (string) $todo);
  }

  function testUncomplete() {
    $todo = new todo('- [X] This is done @d15:27');
    $todo->unComplete();
    $this->assertEquals('- [ ] This is done', (string) $todo, 'Uncompleting a completed todo works');

    $return = $todo->unComplete();
    $this->assertEquals('- [ ] This is done', (string) $todo, 'Uncompleting an impcomplete todo does nothing');    
    $this->assertEquals($todo, $return, '$this is returned');
  }

  function testConfig() {
    $todo = new todo();
    $control = (object) array(
      'timezone' => 'America/Los_Angeles', 
      'flag_prefix' => '@', 
      'weight' => 1000,
      'milestone' => 1209600, 
      'show_ids' => FALSE, 
    );
    $this->assertEquals($control, $todo->getConfig(), 'Default config is set correctly.');

    $todo = new todo('', array(
      'timezone' => 'UTC', 
      'hair_color' => 'blonde', 
    ));
    $control = (object) array(
      'timezone' => 'UTC', 
      'flag_prefix' => '@', 
      'weight' => 1000,
      'hair_color' => 'blonde',
      'milestone' => 1209600,  
      'show_ids' => FALSE,      
    );
    $this->assertEquals($control, $todo->getConfig(), 'Setting config vars works correctly.');    
  }

  function testToString() {
    $todo = new todo('- Buy milk @d', array('weight' => 1000));
    $now = $todo->getDateTime();
    $this->assertEquals("- [x] Buy milk @d$now @w1000", (string) $todo);

    $todo = new todo('- Buy milk');
    $this->assertEquals('- [ ] Buy milk', (string) $todo);
  }

  function testGetFlag() {
    $todo = new Todo('- my item to get done @pJoe @e3.5 @s2014-01-31T13:44 @bc123456 @w4 @d13:44');
    $this->assertEquals('123456', $todo->getFlag('basecamp'), 'Basecamp is retrieved');
    $this->assertEquals(3.5, $todo->getFlag('estimate'), 'Estimate is retrieved');
    $this->assertEquals('Joe', $todo->getFlag('person'), 'Person is retrieved');
    $this->assertEquals('2014-01-31T13:44', $todo->getFlag('start'), 'Start is retrieved');
    $this->assertEquals(1004, $todo->getFlag('weight'), 'Weight is retrieved');
    $this->assertEquals('13:44', $todo->getFlag('done'), 'Done is retrieved');

    $this->assertNull($todo->getFlag('valid_syntax'));
    $this->assertTrue($todo->getParsed('valid_syntax'));
  }

  function testTitle() {
    $todo = new todo('- This is the title @e14 @w10 @pAaron');
    $this->assertEquals('This is the title', $todo->getTitle());
  }

  function testDescription() {
    $todo = new todo('- This is the description @e14 @w10 @pAaron');
    $this->assertEquals('This is the description @pAaron @e14 @w10', $todo->getDescription());
  }

  function testIsDone() {
    $todo = new todo('- Some todo item that is finished @d12:00');
    $this->assertTrue($todo->isComplete(), 'A todo with @d12:00 is recognized as done.');

    $todo = new todo('- Some todo item that is finished @d');
    $this->assertTrue($todo->isComplete(), 'A todo with @d is recognized as done.');

    $todo = new todo('- [X] Some todo item that is finished.');
    $this->assertTrue($todo->isComplete(), 'A completed todo is recognized.');

    $todo = new todo('- [ ] Some todo item that is pending.');
    $this->assertFalse($todo->isComplete(), 'A no-x todo is recognized as incomplete.');
  }
}

/** @} */ //end of group: taskcamp_objects