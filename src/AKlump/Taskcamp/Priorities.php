<?php
namespace AKlump\Taskcamp;

/**
 * Class List
 */
class Priorities extends Object implements ObjectInterface {
  protected $list;

  public function __construct($title = '', $description = '', $config = array()) {
    parent::__construct('', $config);
    $this->setTitle($title);
    $this->setDescription($description);
    $this->list = new ObjectList();
  }

  public function getAvailableFlags(){
    return array('w', 'e', 'm', 'p');
  }

  public function __toString() {
    $output = parent::__toString();
    if ($this->getList()->count()) {
      $output .= PHP_EOL . (string) $this->list;
    }

    return $output;
  }  

  /**
   * Return the sorted list object
   *
   * @return ObjectList
   */
  public function getList() {
    return $this->list;
  }  

  protected function parse() {

  }
}