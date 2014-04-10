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
   * Add an item to the list.
   *
   * Be aware that when adding an item with an id flag, if an existing item with
   * the same id is present the method will fail and return FALSE.
   *
   * @param mixed $item
   *
   * @return $this
   */
  public function add(ObjectInterface $item);
  
  /**
   * Replace (or add) the item of $id with $item
   *
   * Note $item's id flag will be replaced with $id
   *
   * @param  mixed          $id
   * @param  ObjectInterface $item
   * @param  bool $add_if_not_found  Set this to false to avoid having items
   * that can't be replaced, added.
   *
   * @return int
   *   - 0: method failed
   *   - 1: item was replaced
   *   - 2: item was added
   */
  public function replace($id, ObjectInterface $item, $add_if_not_found = TRUE);

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