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
      $flag = $item->getFlag('id');
      if ((string) $flag === (string) $id) {
        $results[$index] = $item;
      }
    }

    return $results;
  }

  /**
   * Generate numeric ids for all items missing them starting with the the
   * next highest number of 0 if no numeric ids exist.
   *
   * @return [type] [description]
   */
  public function generateIds() {
    $start = 0;
    $missing = array();
    foreach ($this->items as $todo) {
      if (($id = $todo->getFlag('id')) === NULL) {
        $missing[] = $todo;
      }
      elseif (is_numeric($id)) {
        $ids[] = $id * 1;
      }
    }
    $primary = empty($ids) ? 0 : max($ids) + 1;
    foreach ($missing as $todo) {
      $todo->setFlag('id', (string) $primary++);
    }

    return $this;
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
      $remove = $this->get($id);
      $this->items = array_diff_key($this->items, $remove);
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