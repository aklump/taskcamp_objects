<?php


namespace AKlump\Taskcamp;


trait SortableTrait {

    public function getWeight($default = null)
    {
        return $this->get('weight', $default);
    }

    public function setWeight($weight)
    {
        return $this->set('weight', $weight);
    }

    public function getGroup($default = null)
    {
        return $this->get('group', $default);
    }

    public function setGroup($group)
    {
        return $this->set('group', $group);
    }
}
