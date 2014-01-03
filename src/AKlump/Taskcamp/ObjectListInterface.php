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
   * @param mixed $id   A unique id for this item
   * @param mixed $item
   *
   * @return  $this
   */
  public function add($id, $item);
  
  /**
   * Return one or all items
   *
   * @param  mixed $id Optional. Omit to return all items.
   *
   * @return array|mixed
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
   * Remove one or all items
   *
   * @param  mixed $id Optional.  Omit to clear list.
   *
   * @return $this
   */
  public function remove($id = NULL);

  /**
   * Return the total items in the list
   *
   * @return int
   */
  public function count();
}