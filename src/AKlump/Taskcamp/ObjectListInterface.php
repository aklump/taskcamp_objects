<?php
namespace AKlump\Taskcamp;

interface ObjectListInterface {

  /**
   * Return the text representation of list in sorted order
   *
   * @return string
   */
  public function __toString();
  
  /**
   * Add an item to the list
   *
   * @param mixed $item
   *
   * @return  $this
   */
  public function add(ObjectInterface $item);
  
  /**
   * Return all items or by id, or index.
   *
   * @param  mixed $id Optional. Omit to return all items.  Otherwise all items
   * having this id will be returned.
   *
   * @return array
   *   - When $id is provided the keys will match $this->items.
   */
  public function get($id = NULL);

  /**
   * Return an array of items in correct weight order
   *
   * @return array
   * - keys are the item ids
   * - values are the objects
   */
  public function getSorted();

  /**
   * Return all completed todos from the list
   *
   * @return array
   */
  public function getCompleted();

  /**
   * Return all incomplete todos from the list
   *
   * @return array
   */
  public function getIncomplete();

  /**
   * Remove one or all items
   *
   * @param  mixed $id Optional. Refer to get().
   *
   * @return $this
   *
   * @see  get();
   */
  public function remove($id = NULL);

  /**
   * Return the total items in the list
   *
   * @return int
   */
  public function count();
}