<?php
use AKlump\Taskcamp\Todo as Todo;
use AKlump\Taskcamp\ObjectList as ObjectList;
use AKlump\Taskcamp\Feature as Feature;

require_once dirname(__FILE__) . '/vendor/autoload.php';

$todo = new Todo('-- layout the bread');
print $todo->getTitle();