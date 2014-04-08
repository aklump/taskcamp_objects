<?php
namespace AKlump\Taskcamp;

interface TodoInterface extends ObjectInterface {

  /**
   * Tells if a todo has been completed
   *
   * @return boolean
   */
  public function isComplete();

  /**
   * Return the total seconds this todo item took from start to finish
   *
   * @return FALSE||float
   *   FALSE is returned if there is missing start or done values
   */
  public function getDuration();

  /**
   * Returns the difference of the estimated hours and the actual hours
   *
   * A postive number means under budget.
   *
   * @return FALSE||float
   *   FALSE is returned if there is missing data to compute this.
   */
  public function getCarryover();
}