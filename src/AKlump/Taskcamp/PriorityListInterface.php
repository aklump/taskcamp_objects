<?php
namespace AKlump\Taskcamp;

interface PriorityListInterface extends ObjectInterface {

  /**
   * Return a sorted list object
   *
   * @return ObjectList
   */
  public function getList();
}