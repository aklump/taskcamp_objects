<?php
namespace AKlump\Taskcamp;

class ObjectList implements ObjectListInterface {

  protected $items = array();

  public function replace($id, ObjectInterface $item, $add_if_not_found = TRUE) {
    $item->setFlag('id', $id);
    
    $replaced = FALSE;
    if ($id = $item->getFlag('id')) {
      foreach ($this->items as $key => $value) {
        if ((string) $value->getFlag('id') === (string) $id) {
          $this->items[$key] = $item;
          $replaced = TRUE;
        }
      }
    }

    if ($replaced) {
      return 1;
    }
    elseif ($add_if_not_found) {
      $before = $this->count();
      $this->add($item);
      return $this->count() === $before + 1 ? 2 : 0;
    }
    
    return 0;
  }

  public function add(ObjectInterface $item) {
    if ($id = $item->getFlag('id')) {
      foreach ($this->items as $key => $value) {
        if ((string) $value->getFlag('id') === (string) $id) {
          return $this;
        }
      }
    }

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
   * @param  int $auto_increment  Ids will be generated starting with this
   * number UNLESS there are numberic ids higher, in which case the highest
   * is used.
   *
   * @return int The highest numerical id. -1 means no numerical ids.
   */
  public function generateIds($auto_increment = 0) {
    $start = 0;
    $missing = array();

    if (empty($this->items)) {
      return -1;
    }

    foreach ($this->items as $todo) {
      if (($id = $todo->getFlag('id')) === NULL) {
        $missing[] = $todo;
      }
      elseif (is_numeric($id)) {
        $ids[] = $id * 1;
      }
    }

    $primary = empty($ids) ? 0 : max($ids) + 1;
    $primary = max($auto_increment, $primary);

    foreach ($missing as $todo) {
      $todo->setFlag('id', (string) $primary++);
    }

    return (int) $primary - 1;
  }

  /**
   * Return the next numerica id that is not yet used.
   *
   * @param  int $minimum_id  The minimum value to return.  This value is
   * passed as the auto_increment value to self::generateIds().
   *
   * @return int
   */
  public function getNextId($minimum_id = 0) {
    $temp = clone $this;
    $highest = $temp->generateIds($minimum_id);
    unset($temp);

    if ($highest === -1) {
      return (int) max(0, $minimum_id);
    }

    return (int) max($highest + 1, $minimum_id);
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
    $output   = array();
    foreach ($this->getSorted() as $value) {
      $output[] = (string) $value;
    }
    
    return implode(PHP_EOL, $output);
  }

}