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

  public function getSubject() {
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

  public function testFiles() {
    $feature = new Feature($this->getSubject());
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
    $feature = new Feature($this->getSubject());
    $this->assertCount(5, $feature->getTodos()->getList()->getSorted());
    $this->assertSame($feature->getTitle(), $feature->getTodos()->getTitle());
    $this->assertNotEmpty($feature->getTodos()->getTitle());
    $this->assertSame($feature->getDescription(), $feature->getTodos()->getDescription());
    $this->assertNotEmpty($feature->getTodos()->getDescription());
  }

  public function testGetURLS() {
    $feature = new Feature($this->getSubject());
    $this->assertCount(3, $feature->getUrls());

    $control = array(
      'http://en.wikipedia.org/wiki/RSS',
      'http://www.digitaltrends.com/how-to/how-to-use-rss/',
      'http:///Library/Packages/php/taskcamp_objects/index.html',
    );
    $this->assertEquals($control, $feature->getUrls());
  }

  public function testTitle() {
    $feature = new Feature($this->getSubject());
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

  public function testDescription() {
    $feature = new Feature($this->getSubject());
    $control = <<<EOD
This feature will add a new RSS feed to the website. See http://en.wikipedia.org/wiki/RSS
It is a solid feature.
EOD;
    $this->assertEquals($control, $feature->getDescription());

    $subject = <<<EOD
    My Nice Title
    My Nice Description           
EOD;
    $feature->set($subject);
    $this->assertEquals('My Nice Title', $feature->getTitle());    
    $this->assertEquals('My Nice Description', $feature->getDescription());    
  }

}

/** @} */ //end of group: taskcamp_objects