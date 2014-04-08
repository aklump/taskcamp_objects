<?php
namespace AKlump\Taskcamp;

class ObjectList {

  protected $items = array();

  public function add(ObjectInterface $item) {
    // Do not try to add a key here.
    $this->items[] = $item;
    
    return $this;
  }

  public function get($id = NULL, $sorted = TRUE) {
    if ($id === NULL) {
      return (array) $this->items;
    }

    // Locate by id
    $results = array();
    foreach ($this->items as $index => $item) {
      if ($item->getFlag('id') == $id) {
        $results[$index] = $item;
      }
    }
    if (!empty($results)) {
      /// @todo how to handle multiple ids?
      return $results;
    }

    // Locate by array index
    return is_numeric($id) && array_key_exists($id, $this->items)
    ? array($id => $this->items[$id])
    : array();
  }

  /**
   * Return all completed todos
   *
   * @return array
   */
  public function getCompleted() {
    $array = $this->get();
    foreach ($array as $id => $todo) {
      if (!$todo->isComplete()) {
        unset($array[$id]);
      }
    }

    return array_values($array);
  }  

  /**
   * Returns all incomplete todos
   *
   * @return array
   */
  public function getIncomplete() {
    $array = $this->get();
    foreach ($array as $id => $todo) {
      if ($todo->isComplete()) {
        unset($array[$id]);
      }
    }

    return array_values($array);
  }    

  public function remove($id = NULL) {
    if ($id === NULL) {
      $this->items = array();
    }
    else {
      $items = $this->get($id);
      foreach (array_keys($items) as $index) {
        unset($this->items[$index]);
      }
      unset($this->items[$id]);
    }

    return $this;
  }

  public function count() {
    return count($this->items);
  }

  public function getSorted() {
    $items = $this->items;

    // Thanks! http://notmysock.org/blog/php/schwartzian-transform.html
    array_walk($items, array('self', 'dec'));

    uasort($items, array('self', 'sort'));
    array_walk($items, array('self', 'undec'));

    return $items;
  } 

  protected static function dec(&$v, $k) {
    static $counter = 0;
    $v = array($v, $k, $counter++);
  }

  protected static function undec(&$v, $k) {
    $v = $v[0];
  }

  protected static function sort($a, $b) {
    $aw = 1 * $a[0]->getFlag('weight');
    $bw = 1 * $b[0]->getFlag('weight');

    if ($aw == $bw) {
      
      // Tie Breaker... check based on order added to class
      if ($a[2] === $b[2]) {
        return 0;
      }
      return $a[2] < $b[2] ? -1 : 1;
    }
    return $aw < $bw ? -1 : 1;
  }

  public function __toString() {
    $output  = '';
    foreach ($this->getSorted() as $value) {
      $output .= (string) $value . PHP_EOL;
    }
    
    return $output;
  }

}