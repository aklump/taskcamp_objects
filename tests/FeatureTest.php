<?php
/**
 * @file
 * Tests for the Feature class
 *
 * @ingroup taskcamp_objects
 * @{
 */

use \AKlump\Taskcamp\Feature;
use \AKlump\Taskcamp\Todo;

require_once '../vendor/autoload.php';

class FeatureTest extends PHPUnit_Framework_TestCase {

  public function testQuestions() {
    $subject = <<<EOD
# Question Test

This will test if questions get extracted correctly.

?A one line question.

- a todo

? This is a bit
more complicated because it goes
around multiple lines

? Does this work?
EOD;

    $obj = new Feature($subject);
    $questions = $obj->getQuestions();
    $this->assertCount(3, $questions);
    $this->assertSame("A one line question.", $questions[0]);
    $this->assertSame("This is a bit\nmore complicated because it goes\naround multiple lines", $questions[1]);
    $this->assertSame("Does this work?", $questions[2]);
  }

  public function testDescription() {
    $subject = "My Not Nice Title\n\n# My Nice Title\n\nMy Nice Description";
    $obj = new Feature($subject);
    $this->assertEquals('My Nice Title', $obj->getTitle());    
    $this->assertEquals('My Nice Description', $obj->getDescription()); 

    $obj = new Feature($this->getSource());
    $control = "This feature will add a new RSS feed to the website. See http://en.wikipedia.org/wiki/RSS\nIt is a solid feature.";
    $this->assertEquals($control, $obj->getDescription());

    // Make sure title and description are erased with new source
    $obj->setSource('');
    $this->assertSame('', $obj->getTitle());
    $this->assertSame('', $obj->getDescription());

    $subject = "My Not Nice Title\n\n# My Nice Title\n\nMy Nice Description";
    $obj->setSource($subject);
    $this->assertEquals('My Nice Title', $obj->getTitle());    
    $this->assertEquals('My Nice Description', $obj->getDescription());  

    $control = "# My Nice Title\n\nMy Nice Description\n\nMy Not Nice Title";
    $this->assertSame($control, (string) $obj);
  }

  public function testDescriptionNoDescription() {
    $subject2 = "# Title @w-10 @pAaron\n\n\n\n\n\n\n\nThis\nis\nthe\ndescription.\n\n\n\n\n\n\n\nbut this is not.\n";
    $obj = new Feature($subject2);
    $control = <<<EOD
This
is
the
description.
EOD;
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

is the
description because it's the first paragraph to
follow title.

but this isn't
it's the
second paragraph to follow title.
EOD;

    $obj = new Feature($subject);
    $this->assertSame('Title', $obj->getTitle());
    $this->assertSame("is the\ndescription because it's the first paragraph to\nfollow title.", $obj->getDescription());

  }
  
  public function testTitle() {
    $feature = new Feature($this->getSource());
    $this->assertEquals("New RSS feed", $feature->getTitle());

    $subject = <<<EOD
Text without markdown headings. No title in document, default should be used.
Lorem ipsum dolar...
EOD;
    $feature = new Feature($subject);
    $this->assertEquals('Feature Title', $feature->getTitle());

    $subject = <<<EOD
Text before a heading.
### This is out of order
## Not Title
# Title
EOD;
    $feature = new Feature($subject);
    $this->assertEquals('Title', $feature->getTitle());
  }

  public function testNotTopH2() {
    $subject = <<<EOD
## This is not title

This is not a description

- [ ] some todo    
EOD;
    $obj = new Feature($subject);

    $this->assertSame('Feature Title', $obj->getTitle());
    $this->assertSame('', $obj->getDescription());
  }


  public function testFirstH1IsTitle() {
    $subject = <<<EOD
not the title

not
the
description
EOD;
    $obj = new Feature($subject);
    $this->assertSame('Feature Title', $obj->getTitle());
    $this->assertSame('', $obj->getDescription());    
    
    $obj = new Feature($subject, array('default_title' => ''));
    $this->assertSame('', $obj->getTitle());
    $this->assertSame('', $obj->getDescription());   

    $subject = <<<EOD
# title

description

## subtitle

# not the title

not
the
description
EOD;
    $obj = new Feature($subject);
    $this->assertSame('title', $obj->getTitle());
    $this->assertSame('description', $obj->getDescription());

    $subject = <<<EOD
not the title

not
the
description

# title

this
is
the
description

## subtitle

# not the title

not
the
description
EOD;
    $obj = new Feature($subject);
    $this->assertSame('title', $obj->getTitle());
    $this->assertSame("this\nis\nthe\ndescription", $obj->getDescription());


  }

  public function testMissingTitleGetsDefault() {
    $subject = <<<EOD
- here is a todo

I marked a todo complete using an x, the todo was duplicated with the same id.  It was not removed from the top box, but it was appended to completed todos AND it was moved to the bottom and given a new id.  The id that it had was not present in the bottom box and it was not carried over to the bottom
EOD;
    $obj = new Feature($subject);
    $this->assertSame('Feature Title', $obj->getTitle());

    $control = <<<EOD
I marked a todo complete using an x, the todo was duplicated with the same id.  It was not removed from the top box, but it was appended to completed todos AND it was moved to the bottom and given a new id.  The id that it had was not present in the bottom box and it was not carried over to the bottom
EOD;
    $this->assertSame('', $obj->getDescription());

    $obj->setSource("# Title\n\n$control");
    $this->assertSame($control, $obj->getDescription());
  }

  public function testAnchorNotHeadingOne() {
    $subject = <<<EOD
# photo essay

http://dev.globalonenessproject.local/node/4045
https://globalonenessproject.basecamphq.com/C274515128

- [ ] fix this page: http://dev.globalonenessproject.local/library/articles/forty-days @id1

Single Column View

- [ ] Does it bother you that the "support the project" and footer blocks are aligned left while the newsletter signup block is center aligned? @id0
- [ ] fix this page: http://dev.globalonenessproject.local/library/articles/forty-days @id1

Widescreen / two column view

- [ ] Can the two column promo (in this case the ELEMENTAL promo) stay as a two column promo in this view.  This way it won't be squeezed as much as it is and we won't have an empty block in the 2nd right promo block. @id2


---

- [ ] implement crazy egg js @id7 @e1

- [ ] remove caching from loft dev. @id6

- [ ] implement crazy egg js @id7 @e1

Here is photo essay SOM
http://dev.globalonenessproject.local/node/4043#photo=1


- [ ] hide tiles xml, promo xml @id8
---
- [ ] look into why page is so slow to load @id9
- [ ] refactor tokens for speed? @id10
---
## responsive
in repsponsive make the lesson plan fall below the synopsis @e1

---


/Library/Projects/globalonenessproject/resources/architecture/Story of the Month Format/som-article.png
http://dev.globalonenessproject.local/story-month-october-2014


https://drupal.org/node/2086643

? For a large display, please confirm the attached is how you want it, specifically, that the white are doesn't span 100% width, but stays in the middle like a box.?
Yes, white background should be fixed width with the nav, hero image, promos and footer.

https://globalonenessproject.basecamphq.com/projects/10673902-2014-website-update/posts/82035402/comments#263453719

## som films
- [ ] add correct icon in the theme after shawn sends, as a sprite to the film overlays @id11
- [ ] enter in wiki how to remove ads by emptying the xml; or to show global use 
 @id12
- [ ] need a gear for editing the homepage som node @id13
- [ ] check for margin below flash player and remove; @id14
- [ ] callback to start playing flash @id15
- [ ] resize the photo for correct output using an image style @id16
- [ ] resize poster in html5 correct style? @id17
- [ ] test click/replace for mobile ios, etc @id18

http://dev.globalonenessproject.local/node/4044
https://globalonenessproject.basecamphq.com/projects/10673902-2014-website-update/posts/81232973/comments#271321570
[All page comps](https://globalonenessproject.basecamphq.com/projects/10673902-2014-website-update/posts/81232973/comments#261824681)
http://dev.globalonenessproject.local/som/2013/12
http://dev.globalonenessproject.local/som/2014/8
EOD;
    $obj = new Feature($subject);
    $this->assertSame('photo essay', $obj->getTitle());
  }

  public function testFlagsInTitleToString() {
    $subject = <<<EOD
# Demo of saving @s2014-04-10 @m2014-04-10 @f2014-04-10

Use this to test the saving and completion process.

- [ ] when marking done with id in master, make it replace original @id0
- [ ] when marking done with no id, make it append to the original @id1
- [ ] when done make it add the effective start date @id2
- [ ] when done it should be removed from notes @id3
- [ ] when creating a node, the original should be reprinted @id4
- [ ] print the feature flags in the title @id7 @e.5
- [ ] add to field_todos_completed with UTC timestamp when marking complete @id8 @e.25
EOD;
    $obj = new Feature($subject);
    $this->assertSame($subject, (string) $obj);
  }

  public function testPurgeScenario() {
    $subject = <<<EOD
- [ ] get things using the correct timezone @id10

- timezone woking correctly @d

- [ ] when done make it add the effective start date @id2
- [x] timezone woking correctly @id9 @s21:42 @d2014-04-09T22:05 @w1000    
EOD;
    $obj = new Feature($subject, array('default_title' => ''));
    $obj->purgeCompleted();
    $control = <<<EOD
- [ ] get things using the correct timezone @id10


- [ ] when done make it add the effective start date @id2
EOD;
    $this->assertSame($control, (string) $obj);
  }

  public function testTimeZoneInheritance() {
    $tz = "Antarctica/Casey";
    $obj = new Feature('- a todo with an inherited timezone', array('timezone' => $tz));
    $this->assertSame($tz, $obj->getConfig('timezone'));

    foreach ($obj->getTodos()->getList()->get() as $todo) {
      $this->assertSame($tz, $todo->getConfig('timezone'));
    }
  }

  public function testPurgeCompleted() {
    $subject = <<<EOD
# Title

- [ ] item @id0
- [x] another item @id1
- [ ] third item
EOD;

    $obj = new Feature($subject);
    $return = $obj->purgeCompleted();
    $this->assertInstanceOf('\AKlump\Taskcamp\ObjectInterface', $return);
    $control = <<<EOD
# Title

- [ ] item @id0
- [ ] third item @id2
EOD;

    $this->assertSame($control, (string) $obj);    
  }

  public function testNextId() {
    $subject = <<<EOD
# Demo of saving

Use this to test the saving and completion process.

- [ ] when marking done with id in master, make it replace original @id0
- [ ] when marking done with no id, make it append to the original @id1
- [ ] when done make it add the effective start date @id2
- [ ] when done it should be removed from notes @id3
- [ ] when creating a node, the original should be reprinted @id4
- [x] goat @id5 @s18:23 @d2014-04-09T18:34 @w1000    
EOD;
    $obj = new Feature($subject);
    $this->assertSame(6, $obj->getTodos()->getList()->getNextId());
  }

  public function testAutoIncrementConfig() {
    $subject = <<<EOD
# Title

- [ ] item @id0
- [x] another item @id1
- [ ] third item
EOD;

    $obj = new Feature($subject, array('auto_increment' => 17));
    $control = <<<EOD
# Title

- [ ] item @id0
- [x] another item @id1
- [ ] third item @id17
EOD;
    $this->assertSame($control, (string) $obj);

    $subject = <<<EOD
# Title

- [ ] item
- [x] another item
- [ ] third item
EOD;

    $obj = new Feature($subject, array('auto_increment' => 17));
    $control = <<<EOD
# Title

- [ ] item @id17
- [x] another item @id18
- [ ] third item @id19
EOD;
    $this->assertSame($control, (string) $obj);
  }

  public function testRemoveParsedLine() {
    $subject = <<<EOD
# Title

- [ ] item @id0
- [ ] another item @id1
- [ ] third item
EOD;

    $obj = new Feature($subject);
    $return = $obj->deleteLine(1);
    $this->assertSame('- [ ] another item @id1', $return);
    $control = <<<EOD
# Title

- [ ] item @id0
- [ ] third item @id2
EOD;

    $this->assertSame($control, (string) $obj);
  }

  public function testExtraTodo() {
    $subject = <<<EOD
# Title

- item
- another item    
EOD;
    $obj = new Feature($subject);
    $obj->getTodos()->getList()->add(new Todo('- third item', array('show_ids' => TRUE)));
    $control = <<<EOD
# Title

- [ ] item @id0
- [ ] another item @id1
- [ ] third item
EOD;
    $this->assertSame($control, (string) $obj);

    $control = <<<EOD
# Title

- [ ] item @id0
- [ ] another item @id1
- [ ] third item @id2
EOD;
    $obj->getTodos()->getList()->generateIds();
    $this->assertSame($control, (string) $obj);
  }  

  public function testDoNotEraseTitleWhenParsing() {
    $subject = <<<EOD
- a single todo in a feature    
EOD;
    $obj = new Feature($subject);
    $obj->parse();
    $obj->setTitle('Once upon a time');
    $obj->setDescription('...in a galaxy far...');    

    $control = <<<EOD
# Once upon a time

...in a galaxy far...

- [ ] a single todo in a feature @id0
EOD;

    $this->assertSame($control, (string) $obj);
  }

  public function testSetTitleAndDescription() {
    $subject = <<<EOD
# a Title

a description.

- [ ] first todo @id0
EOD;
    $obj = new Feature($subject);
    $this->assertSame($subject, (string) $obj);

    $control = <<<EOD
# A different title

a description.

- [ ] first todo @id0
EOD;
    $obj->setTitle('A different title');
    $this->assertSame($control, (string) $obj);

    $control = <<<EOD
# A different title

Eat breakfast soon!

- [ ] first todo @id0
EOD;
    $obj->setDescription('Eat breakfast soon!');
    $this->assertSame($control, (string) $obj);
  }

  public function testToString() {
    $subject = <<<EOD
# Make cookies

Here is the
description
to see if it works.

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
    $control = <<<EOD
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
    $this->assertSame($control, (string) $obj);


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
    $feature = new Feature('# Feature @g"After Launch" @p"Jim Barkley"');
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
    $this->assertEquals('@gWednesday @pAaron @e3 @s2014-01-05 @f2014-01-31 @qb"In the Loft:Taskcamp" @bc123456 @w-10', $feature->getFlags());
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