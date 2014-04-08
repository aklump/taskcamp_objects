<?php
namespace AKlump\Taskcamp;

interface PrioritiesInterface extends ObjectInterface {

  /**
   * Return a sorted list object
   *
   * @return ObjectList
   */
  public function getList();
}