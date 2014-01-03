<?php
namespace AKlump\Taskcamp;

/**
 * Represents a Taskcamp Feature.
 */
class Feature extends Object implements ObjectInterface {
  protected $urls, $todos;

  public function getAvailableFlags() {
    return array('g', 'w', 'p', 'bc', 'man', 'm', 'f', 'e', 's', 'd');
  }

  public function getUrls() {
    return (array) $this->urls;
  }

  public function getTodos() {
    $this->todos->setTitle($this->getTitle());
    $this->todos->setDescription($this->getDescription());

    return $this->todos;
  }

  public function parse() {
    if (strlen($this->source) < 1) {
      return;
    }

    $lines = explode(PHP_EOL, $this->source);
    $todos = $urls = array();
    $candidates = array();
    $this->urls = array();
    $this->todos = new PriorityList();
    foreach ($lines as $key => $line) {

      if (trim($line)) {
        $candidate = new Todo($line);
        if ($candidate->getParsed('valid_syntax')) {
          $this->todos->getList()->add($this->todos->getList()->count(), $candidate);
        }
      }

      if (preg_match('/^(#+) (.*)/', $line, $matches)) {

        // Find the next non_blank line
        $description = '';
        for ($i=$key + 1; $i < count($lines); $i++) { 
          if (trim($lines[$i])) {
            $description .= $lines[$i] . PHP_EOL;
          }
          elseif ($description) {
            break;
          }
        }

        $candidates[] = array(
          strlen($matches[1]),
          count($candidates),
          $matches[2],
          (string) $description,
        );
      }
    }

    // Grab the Urls
    if (preg_match_all('/http:\/\/[^\s]+/', $this->source, $matches)) {
      $this->urls = array_values(array_unique($matches[0]));
    }

    // Set the title
    if (!count($candidates)) {
      $temp = $lines;
      $title = array_shift($temp);
      $description = array_shift($temp);
    }
    else {
      uasort($candidates, array($this, 'sort'));
      $title = reset($candidates);      
      $description = $title[3];
      $title = $title[2];
    }

    $title = $this->parseFlags($title);
    $this->setTitle($title);

    $this->setDescription($description);
  }

  /**
   * Helper for uasort
   *
   * @param  array $a
   *   Must contain three elements: 
   *   0: value
   *   1: first sort int
   *   2: tie breaker sort int, should be auto increment
   * @param  array $b
   *   See $a
   *
   * @return int
   *
   * @see  uasort().
   */
  protected static function sort($a, $b) {

    if ($a[0] === $b[0]) {
      
      // Tie Breaker... check based on order added to array
      if ($a[1] === $b[1]) {
        return 0;
      }
      return $a[1] < $b[1] ? -1 : 1;
    }
    return $a[0] < $b[0] ? -1 : 1;
  }  
}