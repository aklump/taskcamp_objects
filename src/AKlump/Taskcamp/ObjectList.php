<?php
namespace AKlump\Taskcamp;

class ObjectList {

  protected $items = array();

  public function add($id, ObjectInterface $item) {
    $this->items[(string) $id] = $item;

    return $this;
  }

  public function get($id = NULL, $sorted = TRUE) {
    if ($id === NULL) {
      return $this->items;
    }

    return isset($this->items[$id]) ? $this->items[$id] : NULL;
  }

  public function remove($id = NULL) {
    if ($id === NULL) {
      $this->items = array();
    }
    else {
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