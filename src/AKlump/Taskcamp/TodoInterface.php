<?php
namespace AKlump\Taskcamp;

interface TodoInterface extends ObjectInterface {

  /**
   * Mark a todo complete and add a done timestamp
   *
   * @param  string $time The datetime string or NULL for now
   *
   * @return $this
   */
  public function complete($time = NULL);
  
  /**
   * Remove the complete status and done timestamp from an object
   *
   * @return $this
   */
  public function unComplete();

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