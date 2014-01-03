<?php
require_once 'vendor/autoload.php';

use AKlump\Taskcamp\Todo as Todo;
use AKlump\Taskcamp\ObjectList as ObjectList;
use AKlump\Taskcamp\Feature as Feature;

$todo = new Todo('-- layout the bread');
$todo->getTitle();