<?php

namespace AKlump\Taskcamp;

interface SortableInterface {

    public function getWeight($default = null);

    public function setWeight($weight);

    public function getGroup($default = null);

    public function setGroup($group);
}
