<?php
/**
 * @file
 * Tests for the TodoTest class
 *
 * @ingroup taskcamp_objects
 * @{
 */

use AKlump\Taskcamp\Todo as Todo;

class TodoTest extends TestBase {


    public function testGroup()
    {
        $todo = new Todo('- ensure the file exists @e20% @s07:48 @d18:23 @pAaron @gdelayed');
        $this->assertTodoValue('delayed', $todo, 'group');

        $todo = new Todo('- ensure the file exists @e20% @s07:48 @d18:23 @pAaron @g?');
        $this->assertTodoValue('?', $todo, 'group');
    }
    public function testGetEstimatePercentage()
    {
        $todo = new Todo('- ensure the file exists @e20% @s07:48 @d18:23 @pAaron');
        $this->assertTodoValue('20%', $todo, 'estimate');
    }

    public function testGetTask()
    {
        $todo = new Todo('- [X] This is done. @e30 @s10:30 @d10:55');

        $this->assertSame('This is done.', $todo->getTask());

        $todo = new Todo();
        $this->assertSame('Default is returned.', $todo->getTask('Default is returned.'));
    }

    public function testSetTask()
    {
        $todo = new Todo('- [X] This is done. @e30 @s10:30 @d10:55');
        $this->assertSame('Do it again!', $todo->setTask('Do it again!')
                                                ->getTask());
    }

    public function testVariance()
    {
        $todo = new Todo('- [X] This is done @e30 @s10:30 @d10:55');
        $this->assertSame(-5, $todo->getVariance());
    }

    public function testSetNotDone()
    {
        $todo = new Todo('- [X] This is done @d15:27');
        $todo->setNotDone();
        $this->assertEquals('- [ ] This is done', (string) $todo, 'Uncompleting a completed todo works');

        $return = $todo->setNotDone();
        $this->assertEquals('- [ ] This is done', (string) $todo, 'Uncompleting an impcomplete todo does nothing');
        $this->assertEquals($todo, $return, '$this is returned');
    }


    public function testDoneIncreasesWeightByCustomSetting()
    {
        $todo = new Todo('- [ ] important task @w50', array('weight' => 100) + $this->config);
        $this->assertEquals('- [ ] important task @w50', (string) $todo);
        $todo->setDone();
        $this->assertEquals("- [x] important task @d2017-03-26T00:00-0700 @w150", (string) $todo);
    }

    public function testComplete()
    {
        $todo = new Todo('- [ ] Do css');
        $now = $todo->getDateTime();
        $todo->setDone(new \DateTime($now));
        $this->assertEquals("- [x] Do css @d$now @w1000", (string) $todo, 'Completing incomplete with date arg marks is done.');

        $todo->setDone(new \DateTime($todo->getTime()));
        $this->assertEquals("- [x] Do css @d$now @w1000", (string) $todo, 'Completing complete makes no change.');

        $todo = new Todo('- [ ] Do css');
        $return = $todo->setDone();
        $this->assertEquals("- [x] Do css @d$now @w1000", (string) $todo, 'Completing incomplete with no arg marks is done.');
        $this->assertEquals($todo, $return, '$this is returned');
    }

    public function testGetFlags()
    {
        $todo = new Todo('- my item to get done @pJoe @e210 @s2014-01-31T13:44+0000 @m2014-02-14 @bc123456 @w4 @d13:44');
        $this->assertEquals('@pJoe @bc123456 @e210 @s2014-01-31T13:44+0000 @m2014-02-14 @d2014-01-31T13:44+0000 @w1004', $todo->getFlags(), 'getFlags() returns all values as expected');
    }

    public function testMilestoneInTwoWeeks()
    {
        $todo = new Todo(null, $this->config);
        $inTwoWeeks = $todo->createDate('now');
        $inTwoWeeks = $inTwoWeeks->add(new DateInterval('P2W'))
                                 ->format('Y-m-d');

        $obj = new Todo("- [ ] todo @m", $this->config);
        $this->assertSame("- [ ] todo @m$inTwoWeeks", (string) $obj);
    }

    public function testCreateDateAutoDiscover()
    {
        $todo = new Todo();
        $a = $todo->createDate('2017-02-01T13:24-0700');
        $b = $todo->createDate('15:54', '2017-02-01T13:24-0700');
        $this->assertSame($a->format(Todo::DATE_FORMAT_DATE), $b->format(Todo::DATE_FORMAT_DATE));
        $this->assertNotSame($a->format(Todo::DATE_FORMAT_TIME), $b->format(Todo::DATE_FORMAT_TIME));
    }

    public function testAnotherTestOfDates()
    {
        $todo = new Todo('- do it @e30 @m2017-03-28 @f2017-04-01 @s12:27 @d14:27', $this->config);
        $this->assertTodoDateValue('2017-03-28', $todo, 'milestone');
        $this->assertTodoDateValue('2017-04-01', $todo, 'finish');
        $this->assertTodoDateValue('2017-03-26T12:27', $todo, 'start');
        $this->assertTodoDateValue('2017-03-26T14:27', $todo, 'done');
    }

    public function testGranularityOfDateTimesWithNoArguments()
    {
        $todo = new Todo(null, $this->config);
        $date = $todo->getDate();
        $datetime = $todo->getDateTime();

        $obj = new Todo("- [ ] todo @f", $this->config);
        $this->assertSame("- [ ] todo @f$date", (string) $obj);

        $obj = new Todo("- [ ] todo @s", $this->config);
        $this->assertSame("- [ ] todo @s$datetime", (string) $obj);

        $obj = new Todo("- [ ] todo @d", $this->config);
        $this->assertSame("- [x] todo @d$datetime @w1000", (string) $obj);
    }


    public function testCreateDate()
    {
        $obj = new Todo();
        $date = $obj->createDate('00:02', '2014-04-10T00:10+0200');
        $this->assertSame('2014', $date->format('Y'));
        $this->assertSame('04', $date->format('m'));
        $this->assertSame('10', $date->format('d'));
        $this->assertSame('00', $date->format('H'));
        $this->assertSame('02', $date->format('i'));
        $this->assertSame('+0200', $date->format('O'));
    }

    public function testDurationStartHasDateNotDone()
    {
        $todo = new Todo('- a long time ago @s2000-01-01T11:47+0000 @d12:47');
        $this->assertEquals(60, $todo->getDuration(), 'Assert done without date uses the date element from the start flag for duration');
    }

    function testDuration()
    {
        $todo = new Todo('- design the logo @s15:12');
        $this->assertEquals(null, $todo->getDuration());

        $todo = new Todo('- design the logo @s15:12 @d16:12');
        $this->assertEquals(60, $todo->getDuration());

        $todo = new Todo('- design the logo @s2014-01-01T15:12+0000 @d2014-01-02T16:12+0000');
        $this->assertEquals(60 + 1440, $todo->getDuration());
    }

    public function testParsingDone()
    {
        $todo = new Todo('- ensure the file exists @d18:23', $this->config);
        $this->assertTodoTimeValue('18:23', $todo, 'done');
    }

    public function testSetDone()
    {
        $todo = new Todo('- [ ] Do css');
        $now = $todo->getDateTime();
        $todo->setDone($todo->createDate());
        $this->assertEquals("- [x] Do css @d$now @w1000", (string) $todo, 'Completing incomplete with date arg marks is done.');
    }

    public function testGetStart()
    {
        $todo = new Todo('- ensure the file exists @e20 @s07:48 @d18:23 @pAaron', $this->config);
        $this->assertTodoTimeValue('07:48', $todo, 'start');
    }

    public function testGetTitle()
    {
        $todo = new Todo('- ensure the file exists @e20 @s07:48 @d18:23 @pAaron');
        $this->assertTodoValue('ensure the file exists', $todo, 'title');
    }

    function testIsDoneNoXShouldBeFalse()
    {
        $todo = new Todo('- [ ] Some todo item that is pending.');
        $this->assertFalse($todo->isDone(), 'A no-x todo is recognized as incomplete.');
    }

    function testIsDone()
    {
        $todo = new Todo('- Some todo item that is finished @d12:00');
        $this->assertTrue($todo->isDone(), 'A todo with @d12:00 is recognized as done.');

        $todo = new Todo('- Some todo item that is finished @d');
        $this->assertTrue($todo->isDone(), 'A todo with @d is recognized as done.');

        $todo = new Todo('- [X] Some todo item that is finished.');
        $this->assertTrue($todo->isDone(), 'A completed todo is recognized.');
    }

    public function testGetFinishWithoutDate()
    {
        $todo = new Todo('- ensure the file exists @f', $this->config);
        $this->assertSame('2017-03-26T00:00:00-0700', $todo->getFinish()
                                                           ->format(\DATE_ISO8601));
    }

    public function testGetPerson()
    {
        $todo = new Todo('- ensure the file exists @e20 @s07:48 @d18:23 @pAaron');
        $this->assertTodoValue('Aaron', $todo, 'person');
    }

    public function testStartDoneTimesWithoutDates()
    {
        $todo = new Todo('- ensure the file exists @e45 @s07:45 @d08:15', $this->config);
        $date = $todo->getStart();
        $this->assertSame('2017-03-26T07:45:00-0700', $todo->getStart()
                                                           ->format(\DATE_ISO8601));
        $this->assertSame('2017-03-26T08:15:00-0700', $todo->getDone()
                                                           ->format(\DATE_ISO8601));
        $this->assertSame(30, $todo->getDuration());
        $this->assertSame(-15, $todo->getVariance());
    }


    public function testGetEstimate()
    {
        $todo = new Todo('- ensure the file exists @e20 @s07:48 @d18:23 @pAaron');
        $this->assertTodoValue(20, $todo, 'estimate');
    }

    public function testMilestoneWithAltConfig()
    {
        $todo = new Todo('- launch homepage @m', array(

            // Let's set the milestone to 7 days from now.
            'milestone' => 86400 * 7,
            'timezone'  => 'America/Los_Angeles',
        ));

        $then = $todo->getDate('P7D');
        $this->assertEquals("- [ ] launch homepage @m$then", (string) $todo, 'Milestone default date is correct based on config.');
        $this->assertEquals("- [ ] launch homepage @m$then", $todo->getMarkdown(), 'Milestone default date is correct based on config.');
    }

    public function testMilestone()
    {
        $todo = new Todo('- decide on palette @m2014-03-15');
        $this->assertEquals('2014-03-15', $todo->getFlag('milestone'));
    }

    public function testRequireSpaceForFlags()
    {
        $obj = new Todo('- make sure this is not aaron@s12:34 time');
        $this->assertSame('- [ ] make sure this is not aaron@s12:34 time', (string) $obj);
    }

    public function testLongStringOfHyphens()
    {
        $subject = '-----BEGIN CERTIFICATE-----';
        $obj = new Todo($subject);
        $this->assertSame($subject, (string) $obj);
    }

    public function testDateIntervalPeriod()
    {
        $obj = new Todo('- this needs to be done in one week @mP7D', array('timezone' => 'America/Los_Angeles'));
        $now = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
        $milestone = date_add($now, new DateInterval('P7D'));
        $control = "- [ ] this needs to be done in one week @m" . $milestone->format('Y-m-d');
        $this->assertSame($control, (string) $obj);

        $obj = new Todo('- this needs to be done in one week @mP7D', array('timezone' => 'America/Los_Angeles'));
        $now = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
        $milestone = date_add($now, new DateInterval('P7D'));
        $control = "- [ ] this needs to be done in one week @m" . $milestone->format('Y-m-d');
        $this->assertSame($control, (string) $obj);

        $obj = new Todo('- this needs to be done in two months @mP2M', array('timezone' => 'America/Los_Angeles'));
        $now = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
        $milestone = date_add($now, new DateInterval('P2M'));
        $control = "- [ ] this needs to be done in two months @m" . $milestone->format('Y-m-d');
        $this->assertSame($control, (string) $obj);


    }

    public function testAtDoneNotX()
    {
        $obj = new Todo("- [ ] problem is that tinymce doesn't work on ipad @id3 @d");
        $this->assertTrue($obj->isDone());
    }

    public function testDateRegex()
    {
        $regex = '/' . Todo::dateRegex() . '/';

        preg_match($regex, 'hi @m2014-04-09 ho', $matches);
        $this->assertSame('2014-04-09', $matches[0]);

        preg_match($regex, 'hi @d2014-04-09T23:34-0700 ho', $matches);
        $this->assertSame('2014-04-09T23:34-0700', $matches[0]);

        preg_match($regex, 'hi @s23:34 ho', $matches);
        $this->assertSame('23:34', $matches[0]);

        preg_match($regex, 'hi @s23:34-0800 ho', $matches);
        $this->assertSame('23:34-0800', $matches[0]);
    }

    public function testSimplifyStartWithTimezone()
    {
        $obj = new Todo('- something important @s2014-04-09T22:42-0700 @d2014-04-09T23:10-0700');
        $this->assertSame('- [x] something important @s22:42 @d2014-04-09T23:10-0700 @w1000', (string) $obj);
    }

    public function testSloppyCompleted()
    {
        $control = '- [x] when creating a node, the original should be reprinted @id4';

        $subject = '- [            x] when creating a node, the original should be reprinted @id4';
        $obj = new Todo($subject);
        $this->assertSame($control, (string) $obj);

        $subject = '- [x            ] when creating a node, the original should be reprinted @id4';
        $obj = new Todo($subject);
        $this->assertSame($control, (string) $obj);

        $subject = '- [          x     ] when creating a node, the original should be reprinted @id4';
        $obj = new Todo($subject);
        $this->assertSame($control, (string) $obj);

        $subject = '- [x] when creating a node, the original should be reprinted @id4';
        $obj = new Todo($subject);
        $this->assertSame($control, (string) $obj);

        $subject = '- [ x] when creating a node, the original should be reprinted @id4';
        $obj = new Todo($subject);
        $this->assertSame($control, (string) $obj);

        $subject = '- [x ] when creating a node, the original should be reprinted @id4';
        $obj = new Todo($subject);
        $this->assertSame($control, (string) $obj);
    }

    public function testHR()
    {
        $obj = new Todo('---');
        $this->assertSame('---', (string) $obj);
    }

    public function testSimplify()
    {
        $obj = new Todo('- [x] some done todo @id5 @s2014-04-09T19:35+0000 @d2014-04-09T13:16+0000 @w1000');
        $control = '- [x] some done todo @id5 @s19:35 @d2014-04-09T13:16+0000 @w1000';
        $this->assertSame($control, (string) $obj);
    }


    public function testPrintingSAndDFlags()
    {
        $obj = new Todo('- [x] do this');
        $obj->setFlag('start', '2014-04-09T08:28');
        $obj->setFlag('done', '2014-04-09T08:37');
        $this->assertSame('- [x] do this @s08:28 @d2014-04-09T08:37', (string) $obj);

        $obj->setFlag('start', '2014-04-08T23:28');
        $obj->setFlag('done', '2014-04-09T08:37');
        $this->assertSame('- [x] do this @s2014-04-08T23:28 @d2014-04-09T08:37', (string) $obj);
    }

    function testGetFlagIdZero()
    {
        $obj = new Todo('- here it is @id0', array('show_ids' => true));
        $this->assertSame('0', $obj->getFlag('id'));
        $this->assertSame('- [ ] here it is @id0', (string) $obj);

        $obj = new Todo('- here it is @id1', array('show_ids' => true));
        $this->assertSame('1', $obj->getFlag('id'));
        $this->assertSame('- [ ] here it is @id1', (string) $obj);

        $obj = new Todo('- here it is', array('show_ids' => true));
        $obj->setFlag('id', 0);
        $this->assertSame(0, $obj->getFlag('id'));
        $this->assertSame('- [ ] here it is @id0', (string) $obj);

        $obj = new Todo('- here it is', array('show_ids' => true));
        $obj->setFlag('id', '0');
        $this->assertSame('0', $obj->getFlag('id'));
        $this->assertSame('- [ ] here it is @id0', (string) $obj);
    }

    function testEscapingAFlag()
    {
        $obj = new Todo('- Can we escape \@w300 a 300 weight');
        $this->assertSame('Can we escape \@w300 a 300 weight', $obj->getTitle());
    }

    function testId()
    {
        $obj = new Todo('- Do this @w-10', array('show_ids' => false));
        $obj->setFlag('id', 'apple');
        $this->assertSame('apple', $obj->getFlag('id'));
        $this->assertSame('- [ ] Do this @w-10', (string) $obj);

        $obj = new Todo('- Do this @w-10', array('show_ids' => true));
        $obj->setFlag('id', 'apple');
        $this->assertSame('apple', $obj->getFlag('id'));
        $this->assertSame('- [ ] Do this @idapple @w-10', (string) $obj);

        $obj->setFlag('id', '1234');
        $this->assertSame('1234', $obj->getFlag('id'));
        $this->assertSame('- [ ] Do this @id1234 @w-10', (string) $obj);

        $obj->setFlag('id', 'apple muffin');
        $this->assertSame('apple muffin', $obj->getFlag('id'));
        $this->assertSame('- [ ] Do this @id"apple muffin" @w-10', (string) $obj);

        $obj = new Todo('- Do this @idapple', array('show_ids' => true));
        $this->assertSame('apple', $obj->getFlag('id'));
        $this->assertSame('- [ ] Do this @idapple', (string) $obj);

        $obj = new Todo('- Do this @id1234', array('show_ids' => true));
        $this->assertSame('1234', $obj->getFlag('id'));
        $this->assertSame('- [ ] Do this @id1234', (string) $obj);

        $obj = new Todo('- Do this @id"apple muffin"', array('show_ids' => true));
        $this->assertSame('apple muffin', $obj->getFlag('id'));
        $this->assertSame('- [ ] Do this @id"apple muffin"', (string) $obj);
    }

    function testGetFlagDate()
    {
        $obj = new Todo('- [ ] finish this thing @s16:06 @d2014-04-13T12:54+0000', array('timezone' => 'UTC'));
        $control = new \DateTime('2014-04-13T12:54+0000', new \DateTimeZone('UTC'));
        $return = $obj->getFlag('done', true);
        $this->assertEquals($control, $return);

        $control = new \DateTime('now', new \DateTimeZone('UTC'));
        $control->setTime(16, 06, 00);
        $return = $obj->getFlag('start', true);
        $this->assertEquals($control, $return);

        $obj = new Todo('- [ ] finish this thing', array('timezone' => 'UTC'));
        $control = null;
        $return = $obj->getFlag('done', true);
        $this->assertEquals($control, $return);
    }

    function testStaticMethods()
    {
        $this->assertNotEmpty(Todo::dateRegex());
    }

    function testToStringInvalidTodo()
    {
        $control = '// Some comment';
        $todo = new Todo($control);
        $this->assertEquals($control, (string) $todo);
    }

    function testWeight()
    {
        $todo = new Todo('- my item to get done @pJoe @e210 @s2014-01-31T13:44+0000 @m2014-02-14 @bc123456 @w4');
        $this->assertTodoValue(4.0, $todo, 'weight');
        $todo = new Todo('- my item to get done @pJoe @e210 @s2014-01-31T13:44+0000 @m2014-02-14 @bc123456 @w4 @d');
        $this->assertTodoValue(1004.0, $todo, 'weight');

        $todo = new Todo('- something @w-10');
        $this->assertEquals(-10, $todo->getFlag('weight'));

        $todo = new Todo('- something @w10');
        $this->assertEquals(10, $todo->getFlag('weight'));
    }

    function testDateStrings()
    {
        $datetime = '2004-02-12T15:19:21';

        $todo = new Todo('', array('timezone' => 'UTC') + $this->config);
        $this->assertEquals('2004-02-12', $todo->getDate($datetime));
        $this->assertEquals('15:19+0000', $todo->getTime($datetime));
        $this->assertEquals('2004-02-12T15:19+0000', $todo->getDateTime($datetime));
        // When the timezone portion is here; it doesn't care the config timezone setting
        $this->assertEquals('2004-02-12T15:19-0800', $todo->getDateTime('2004-02-12T15:19-0800'));
        $this->assertEquals('2004-02-12T15:19+0000', $todo->getDateTime('2004-02-12T15:19+0000'));

        $todo->setConfig(array(
            'timezone'     => 'America/Los_Angeles',
            'date_default' => $datetime,
        ));
        $this->assertEquals('2004-02-12', $todo->getDate($datetime));
        $this->assertEquals('15:19-0800', $todo->getTime($datetime));
        $this->assertEquals('2004-02-12T15:19-0800', $todo->getDateTime($datetime));
        // When the timezone portion is here; it doesn't care the config timezone setting
        $this->assertEquals('2004-02-12T15:19-0800', $todo->getDateTime('2004-02-12T15:19-0800'));
        $this->assertEquals('2004-02-12T15:19+0000', $todo->getDateTime('2004-02-12T15:19+0000'));
    }

    function testGetVariance()
    {
        $todo = new Todo('- design the logo @s15:12 @d16:12');
        $this->assertSame(null, $todo->getVariance());

        $todo = new Todo('- design the logo @e60 @s15:12 @d16:12');
        $this->assertSame(0, $todo->getVariance());

        $todo = new Todo('- design the logo @e30 @s15:12 @d16:12');
        $this->assertEquals(30, $todo->getVariance());

        $todo = new Todo('- design the logo @e90 @s15:12 @d16:12');
        $this->assertEquals(-30, $todo->getVariance());
    }

    function testStart()
    {
        $todo = new Todo('- finish the design @s');
        $now = $todo->getDateTime();
        $this->assertEquals("- [ ] finish the design @s$now", (string) $todo, 'Assert time is appended to @s');
    }

    function testShorthand()
    {
        $variations = array(
            '- a todo item',
            '-[]a todo item',
            '-[ ]a todo item',
            '- []a todo item',
        );
        foreach ($variations as $variation) {
            $todo = new Todo($variation);
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
            $todo = new Todo($variation);
            $this->assertEquals('- [x] a todo item', (string) $todo, "'$variation' passes.");
        }

    }

    function testConfig()
    {
        $todo = new Todo();
        $control = (object) array(
            'timezone'     => 'UTC',
            'flag_prefix'  => '@',
            'weight'       => 1000,
            'milestone'    => 1209600,
            'show_ids'     => true,
            'date_default' => 'now',
        );
        $this->assertEquals($control, $todo->getConfig(), 'Default config is set correctly.');

        $todo = new Todo('', array(
            'timezone'   => 'America/Los_Angeles',
            'hair_color' => 'blonde',
        ));
        $control = (object) array(
            'timezone'     => 'America/Los_Angeles',
            'flag_prefix'  => '@',
            'weight'       => 1000,
            'hair_color'   => 'blonde',
            'milestone'    => 1209600,
            'show_ids'     => true,
            'date_default' => 'now',
        );
        $this->assertEquals($control, $todo->getConfig(), 'Setting config vars works correctly.');
    }

    function testToString()
    {
        $todo = new Todo('- Buy milk @d', array('weight' => 1000));
        $now = $todo->getDateTime();
        $this->assertEquals("- [x] Buy milk @d$now @w1000", (string) $todo);

        $todo = new Todo('- Buy milk');
        $this->assertEquals('- [ ] Buy milk', (string) $todo);
    }

    function testGetFlag()
    {
        $todo = new Todo('- my item to get done @pJoe @e210 @s2014-01-31T13:44+0000 @bc123456 @w4 @d13:44');
        $this->assertEquals('123456', $todo->getFlag('basecamp'), 'Basecamp is retrieved');
        $this->assertEquals(210, $todo->getFlag('estimate'), 'Estimate is retrieved');
        $this->assertEquals('Joe', $todo->getFlag('person'), 'Person is retrieved');
        $this->assertEquals('2014-01-31T13:44+0000', $todo->getFlag('start'), 'Start is retrieved');
        $this->assertEquals(1004, $todo->getFlag('weight'), 'Weight is retrieved');
        $this->assertEquals('2014-01-31T13:44+0000', $todo->getFlag('done'), 'Done is retrieved');

        $this->assertNull($todo->getFlag('valid_syntax'));
        $this->assertTrue($todo->getParsed('valid_syntax'));
    }

    function testTitle()
    {
        $todo = new Todo('- This is the title @e840 @w10 @pAaron');
        $this->assertEquals('This is the title', $todo->getTitle());
    }

    function testDescription()
    {
        $todo = new Todo('- This is the description @e840 @w10 @pAaron');
        $this->assertEquals('This is the description @pAaron @e840 @w10', $todo->getDescription());
    }


    public function setup()
    {
        $this->config = [
            'date_default' => '2017-03-26',
            'timezone'     => 'America/Los_Angeles',
        ];
    }
}

/** @} */ //end of group: taskcamp_objects
