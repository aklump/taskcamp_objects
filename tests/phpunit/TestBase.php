<?php


class TestBase extends \PHPUnit_Framework_TestCase {

    function assertTodoDateValue($dateTimeString, $todo, $id)
    {
        $control = new \DateTime($dateTimeString, new \DateTimeZone($this->config['timezone']));
        $method = "get{$id}";
        $this->assertEquals($control, $todo->{$method}());
        $this->assertEquals($control, $todo->getExportData()
                                           ->getValue($id));
    }

    function assertTodoTimeValue($control, $todo, $id)
    {
        list($hours, $minutes) = explode(':', $control);
        $control = new \DateTime($this->config['date_default'], new \DateTimeZone($this->config['timezone']));
        $control->setTime($hours, $minutes, 0);

        $method = "get{$id}";
        $this->assertEquals($control, $todo->{$method}());
        $this->assertEquals($control, $todo->getExportData()
                                           ->getValue($id));
    }

    function assertTodoValue($control, $todo, $id)
    {
        // Test the getter
        $method = "get{$id}";
        $value = $todo->{$method}();
        $this->assertSame($control, $value);
        $this->assertSame($control, $todo->getExportData()
                                         ->getValue($id));

        // Test the setter
        $method = "set{$id}";
        $return = $todo->{$method}($value);
        $this->assertSame($todo, $return);

        // Test the getter is affected by the setter.
        $method = "get{$id}";
        $after = $todo->{$method}();
        $this->assertSame($value, $after);
    }

}
