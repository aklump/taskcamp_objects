<?php
namespace AKlump\Taskcamp;

/**
 * Represents a Taskcamp Feature.
 */
class Feature extends Object implements ObjectInterface {
  protected $urls, $todos, $files;

  public function __construct($source = '', $config = array()) {
    $this->todos = new Priorities;
    parent::__construct($source, $config);
  }

  /**
   * Add in todo specific 'show_ids' config key.
   *
   * @param array||object $config
   */
  public function setConfig($config) {
    $config = (array) $config + array(

      // Set this to true and todos will be rewritten on print
      'rewrite_todos' => TRUE,

      // Define the first id to be auto assigned.
      'auto_increment' => 0,
    );

    return parent::setConfig($config);
  }

  public function __toString() {
    $output   = array();
    
    // (Optional) Title
    if (($title = $this->getTitle())) {
      $output[] = "# $title";
      $output[] = '';
    }

    // (Optional) Description
    if (($description = $this->getDescription())) {
      $output[] = $description;
      $output[] = '';
    }

    // These are all todos in the object, we're going to remove from this array
    // based on any in parsed lines.
    $missing_todo_list = clone $this->getTodos()->getList();
    $lines = $this->getParsed('lines');
    foreach ($this->getParsed('todos_by_line') as $line_number => $line_todo_id) {
      foreach ($this->getTodos()->getList()->get($line_todo_id) as $line_todo) {
        // Remove from the missing list
        $id = $line_todo->getFlag('id');
        $missing_todo_list->remove($id);

        // Now rewrite line_todo if config calls for it
        if ($this->getConfig('rewrite_todos')) {
          $lines[$line_number] = (string) $line_todo;
        }      
      }    
    }

    $output = array_merge($output, $lines);

    // Append todos not present in the parsed lines
    foreach ($missing_todo_list->getSorted() as $todo) {
      $output[] = (string) $todo;
    }
    
    return trim(implode(PHP_EOL, $output));








    // $lines = $this->getParsed('lines');
    
    // // Look for todo items not in the parsed array
    // $missing_todos = clone $this->getTodos()->getList();

    // if (!empty($this->parsed->todos_by_line)) {
    //   foreach ($this->parsed->todos_by_line as $line_number => $todo) {

    //     // Remove this from our additional since it's already present in our parsed lines array.
    //     $id = $todo->getFlag('id');
    //     $missing_todos->remove($id);


    //   }
    // }

    // // Append any missing todo items not in the parsed array
    // foreach ($missing_todos->get() as $todo) {
    //   $lines[] = (string) $todo;
    // }

    // $header = array();
    // $header[] = '# ' . $this->getTitle();

    // // The description
    // if (($d = $this->getDescription())) {
    //   $header[] = '';
    //   $header[] = $d;
    // }

    // $header[] = '';    

    // return implode(PHP_EOL, $header) . PHP_EOL . trim(implode(PHP_EOL, $lines));
  }    

  public function getAvailableFlags() {
    return array('g', 'w', 'p', 'id', 'qb', 'bc', 'mt', 'm', 'f', 'e', 's', 'd', 'h');
  }

  public function getUrls() {
    return (array) $this->urls;
  }

  public function getFiles() {
    return $this->files;
  }

  /**
   * Returns a priorities object of all todos
   *
   * @return \AKlump\Taskcamp\Priorities
   */
  public function getTodos() {
    $this->todos->setTitle($this->getTitle());
    $this->todos->setDescription($this->getDescription());

    return $this->todos;
  }

  public function parse() {
    parent::parse();
    $source = $this->parsed->source;

    $this->urls   = array();
    $this->files  = new ObjectList();
    $this->todos  = new Priorities();

    // find h1's and title
    $title = '';
    if (preg_match_all('/(#+)\s?([^#].*)/', $source, $matches)) {
      $this->parsed->headings = array();
      foreach ($matches[2] as $key => $title) {
        $element = 'h' . strlen($matches[1][$key]);
        $find = $matches[0][$key];
        $this->parsed->headings[$element][$find] = $title;
      }
      ksort($this->parsed->headings);
      
      $top_level = reset($this->parsed->headings);
      $title = reset($top_level);
      
      // Remove the line that contains the title
      $find = key($top_level);
      $remove = array_search($find, $this->parsed->lines);
      unset($this->parsed->lines[$remove]);

    }
    elseif (preg_match('/^\w/', $this->parsed->lines[0])) {
      $title = $this->parsed->lines[0];
      unset($this->parsed->lines[0]);
    }

    $title = $this->parseFlags($title);
    $this->setTitle($title);

    // Find description
    if (isset($this->parsed->p[1]) && preg_match('/^\w/', $this->parsed->p[1], $matches)) {
      $this->setDescription($this->parsed->p[1]);

      // Strip it out of lines
      $haystack = implode(PHP_EOL, $this->parsed->lines);
      $needle = $this->getDescription();
      $result = trim(str_replace($needle, '', $haystack));
      $this->parsed->lines = explode(PHP_EOL, $result);
    }

    // Trim empty front and back lines so we don't have title troubles of 
    // extra spacing.
    while (count($this->parsed->lines) && !trim(reset($this->parsed->lines))) {
      array_shift($this->parsed->lines);
    }

    // Grab the files
    if (preg_match_all('/#+\s*Files(.*?)\n\n/si', $source, $matches)
      && ($files = explode(PHP_EOL, trim($matches[1][0])))) {
      foreach ($files as $path) {
        $this->files->add(new File($path));
      }
    }

    // Grab the Urls
    if (preg_match_all('/https?:\/\/[^\s]+/', $source, $matches)) {
      $this->urls = array_values(array_unique($matches[0]));
    }

    // Grab all todos and assign ids if none.
    $todos = $urls = array();
    $candidates = array();
    foreach ($this->parsed->lines as $line_index => $line) {

      if (trim($line)) {
        $candidate = new Todo($line);
        if ($candidate->getParsed('valid_syntax')) {

          // Add the next available numeric id if we don't have one.
          $id = $candidate->getFlag('id');
          if (!$id && (string) $id !== '0') {
            $auto_id = $this->todos->getList()->getNextId($this->getConfig('auto_increment'));
            $candidate->setFlag('id', $auto_id);
          }

          $this->todos->getList()->add($candidate);
          $this->parsed->todos_by_line[$line_index] = $candidate->getFlag('id');
        }
      }

      // if (preg_match('/^(#+) (.*)/', $line, $matches)) {

      //   // Find the next non_blank line
      //   $description = '';
      //   for ($i=$key + 1; $i < count($lines); $i++) { 
      //     if (trim($lines[$i])) {
      //       $description .= $lines[$i] . PHP_EOL;
      //     }
      //     elseif ($description) {
      //       break;
      //     }
      //   }

      //   $candidates[] = array(
      //     strlen($matches[1]),
      //     count($candidates),
      //     $matches[2],
      //     (string) $description,
      //   );
      // }
    }
    $this->todos->getList()->generateIds($this->getConfig('auto_increment'));


    // // Set the title
    // if (!count($candidates)) {
    //   $temp = $lines;
    //   $title = array_shift($temp);
    //   $description = array_shift($temp);
    // }
    // else {
    //   uasort($candidates, array($this, 'sort'));
    //   $title = reset($candidates);      
    //   $description = $title[3];
    //   $title = $title[2];
    // }


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

  public function purgeCompleted() {
    foreach ($this->getParsed('todos_by_line') as $line_number => $id) {
      foreach ($this->getTodos()->getList()->get($id) as $todo) {
        if ($todo->isComplete()) {
          $this->deleteLine($line_number);
          unset($this->parsed_todos_by_line[$line_number]);
        }
      }
    }

    return $this;
  }  
}