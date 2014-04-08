<?php
/**
 * @file
 * Tests for the Feature class
 *
 * @ingroup taskcamp_objects
 * @{
 */

use \AKlump\Taskcamp\PriorityList as PriorityList;
use \AKlump\Taskcamp\Feature as Feature;
require_once '../vendor/autoload.php';

class FeatureTest extends PHPUnit_Framework_TestCase {

  public function testAddingWithoutId() {
    $subject = <<<EOD
# Make cookies

- [x] mill the flour @e1 @d2014-04-08T15:13 @w1000 @id2
- [x] melt the chocolate @id3 @e2

- [x] eat breakfast
    
EOD;
    $obj = new Feature($subject);
    $this->assertSame(3, $obj->getTodos()->getList()->count());
    $this->assertCount(3, $obj->getTodos()->getList()->get());
    $this->assertCount(3, $obj->getTodos()->getList()->getCompleted());
    $this->assertCount(0, $obj->getTodos()->getList()->getIncomplete());
  }

  public function testToString() {
    $subject = <<<EOD
# Make cookies

- mill the flour @e1 @d
- melt the chocolate @e2

# when something is marked @d
- reset the running timer
- stamp the done item with @s based on timer @d and @h
- remove it from field_body
- add it to object_active
EOD;
    $obj = new Feature($subject, array('rewrite_todos' => FALSE));
    $this->assertSame($subject, (string) $obj);
  }

  public function testLineBreakChars() {
    $subject = "# Make cookies\r\n\r\n- mill the flour @e1 @d\r\n- melt the chocolate @e2\r\n\r\n# when something is marked @d\r\n- reset the running timer\r\n- stamp the done item with @s based on timer @d and @h\r\n- remove it from field_body\r\n- add it to object_active\r\n";
    $obj = new Feature($subject);
    $this->assertCount(3, $obj->getParsed('p'));
  
    $subject = "# Make cookies\n\n- mill the flour @e1 @d\n- melt the chocolate @e2\n\n# when something is marked @d\n- reset the running timer\n- stamp the done item with @s based on timer @d and @h\n- remove it from field_body\n- add it to object_active\n";
    $obj = new Feature($subject);
    $this->assertCount(3, $obj->getParsed('p'));
  }

  public function testParagraphs() {
    $subject = <<<EOD
# Make cookies

- mill the flour @e1 @d
- melt the chocolate @e2

# when something is marked @d
- reset the running timer
- stamp the done item with @s based on timer @d and @h
- remove it from field_body
- add it to object_active    
EOD;
    $obj = new Feature($subject);
    $this->assertCount(3, $obj->getParsed('p'));
  }

  public function testDescription() {
    $feature = new Feature($this->getSource());
    $control = <<<EOD
This feature will add a new RSS feed to the website. See http://en.wikipedia.org/wiki/RSS
It is a solid feature.
EOD;
    $this->assertEquals($control, $feature->getDescription());

    $subject = <<<EOD
    My Nice Title

    My Nice Description           
EOD;
    $feature->setSource($subject);

    $this->assertEquals('My Nice Title', $feature->getTitle());    
    $this->assertEquals('My Nice Description', $feature->getDescription());    
  }
  public function testTitle() {
    $feature = new Feature($this->getSource());
    $this->assertEquals("New RSS feed", $feature->getTitle());

    $subject = <<<EOD
Text without markdown headings.
Lorem ipsum dolar...
EOD;
    $feature = new Feature($subject);
    $this->assertEquals('Text without markdown headings.', $feature->getTitle());

    $subject = <<<EOD
Text before a heading.
### This is out of order
## Title
EOD;
    $feature = new Feature($subject);
    $this->assertEquals('Title', $feature->getTitle());
  }

  public function testDescriptionNoDescription() {
    $subject = <<<EOD
# Title @w-10 @pAaron








This
is
the
description.







but this is not.
EOD;
    $control = "This
is
the
description.";

    $obj = new Feature($subject);
    $this->assertSame('Title', $obj->getTitle());
    $this->assertSame($control, $obj->getDescription());

    $subject = <<<EOD
# Title @w-10 @pAaron

This
is
the
description.

but this is not.
EOD;
    $control = "This
is
the
description.";

    $obj = new Feature($subject);
    $this->assertSame('Title', $obj->getTitle());
    $this->assertSame($control, $obj->getDescription());

    $subject = <<<EOD
# Title @w-10 @pAaron
This
is
not the description
because
this is not a paragraph

EOD;
    $obj = new Feature($subject);
    $this->assertSame('Title', $obj->getTitle());
    $this->assertSame('', $obj->getDescription());

    $subject = <<<EOD
# Title @w-10 @pAaron

- [ ] This is a todo item; no description

is
not the
description.

and neither is this.
EOD;

    $obj = new Feature($subject);
    $this->assertSame('Title', $obj->getTitle());
    $this->assertSame('', $obj->getDescription());

  }

  public function testGetCompletedTodoList() {
    $subject = <<<EOD
# New feature

- [ ] This is done @d
- [ ] This is not done

EOD;

    $obj = new Feature($subject);

    $todos = $obj->getTodos()->getList()->getIncomplete();
    $this->assertCount(1, $todos);
    $this->assertSame('This is not done', $todos[0]->getTitle());

    $todos = $obj->getTodos()->getList()->getCompleted();
    $this->assertCount(1, $todos);
    $this->assertSame('This is done', $todos[0]->getTitle());
  }

  public function getSource() {
    return <<<EOD
# New RSS feed

This feature will add a new RSS feed to the website. See http://en.wikipedia.org/wiki/RSS
It is a solid feature.

This is not part of the description.

## Round One
A description of Round One goes here.
-- research best format @e2
-- code the module @e6
-- QA testing @e2

## Round Two
Don't forget to refer to http://en.wikipedia.org/wiki/RSS

A description of R2.

-- refactor @e4
-- QA testing @e2

# References
http://www.digitaltrends.com/how-to/how-to-use-rss/

## Files
/Library/Packages/php/taskcamp_objects/index.html
/Library/Packages/php/taskcamp_objects/cover.html
/Library/Packages/php/taskcamp_objects/import.php
file:///Library/Packages/php/taskcamp_objects/index.html
http:///Library/Packages/php/taskcamp_objects/index.html

anything else
EOD;
  }

  public function testGetSource() {
    $feature = new Feature($this->getSource());
    return $this->assertSame($this->getSource(), $feature->getSource());
  }

  public function testFiles() {
    $feature = new Feature($this->getSource());
    $this->assertEquals(5, $feature->getFiles()->count());
  }

  public function testGroup() {
    $feature = new Feature('Feature @g"After Launch" @p"Jim Barkley"');
    $this->assertEquals('After Launch', $feature->getFlag('group'));
    $this->assertEquals('Jim Barkley', $feature->getFlag('person'));
  }

  public function testFlags() {
    $subject = <<<EOD
A little preamble

# Security Updates to Core @w-10 @pAaron @bc123456 @f2014-01-31 @e3 @s2014-01-05 @gWednesday @qb"In the Loft:Taskcamp"
-- download drupal
-- upgrade    
EOD;
    $feature = new Feature($subject);
    $this->assertEquals('Security Updates to Core', $feature->getTitle());
    $this->assertEquals('@gWednesday @w-10 @pAaron @qb"In the Loft:Taskcamp" @bc123456 @f2014-01-31 @e3 @s2014-01-05', $feature->getFlags());
    $this->assertEquals('Wednesday', $feature->getFlag('group'));
    $this->assertEquals(-10, $feature->getFlag('weight'));
    $this->assertEquals('Aaron', $feature->getFlag('person'));
    $this->assertEquals(123456, $feature->getFlag('basecamp'));
    $this->assertEquals('2014-01-31', $feature->getFlag('finish'));
    $this->assertEquals(3, $feature->getFlag('estimate'));
    $this->assertEquals('2014-01-05', $feature->getFlag('start'));
    $this->assertEquals("In the Loft:Taskcamp", $feature->getFlag('quickbooks'));
  }

  public function testGetTodos() {
    $feature = new Feature($this->getSource());
    $this->assertCount(5, $feature->getTodos()->getList()->getSorted());
    $this->assertSame($feature->getTitle(), $feature->getTodos()->getTitle());
    $this->assertNotEmpty($feature->getTodos()->getTitle());
    $this->assertSame($feature->getDescription(), $feature->getTodos()->getDescription());
    $this->assertNotEmpty($feature->getTodos()->getDescription());
  }

  public function testGetURLS() {
    $feature = new Feature($this->getSource());

    $this->assertCount(3, $feature->getUrls());

    $control = array(
      'http://en.wikipedia.org/wiki/RSS',
      'http://www.digitaltrends.com/how-to/how-to-use-rss/',
      'http:///Library/Packages/php/taskcamp_objects/index.html',
    );
    $this->assertEquals($control, $feature->getUrls());
  }
}

/** @} */ //end of group: taskcamp_objects