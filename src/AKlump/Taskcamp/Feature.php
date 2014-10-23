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

      // Define a default title, or set to blank for none
      'default_title' => "Feature Title",
    );

    return parent::setConfig($config);
  }

  public function __toString() {
    $output   = array();
    
    // (Optional) Title
    if (($title = $this->getTitle())) {
      $title = "# $title";
      if ($flags = $this->getFlags()) {
        $title .= " $flags";
      }
      $output[] = $title;
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
    $lines = (array) $this->getParsed('lines');
    foreach ((array) $this->getParsed('todos_by_line') as $line_todo_id => $line_numbers) {
      foreach ($line_numbers as $line_number) {
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
    }

    $output = array_merge($output, $lines);

    // Append todos not present in the parsed lines
    foreach ($missing_todo_list->getSorted() as $todo) {
      $output[] = (string) $todo;
    }
    
    return trim(implode(PHP_EOL, $output));
  }    

  public function getAvailableFlags() {
    return array('g', 'id', 'p', 'h', 'e', 's', 'm', 'f', 'd', 'qb', 'bc', 'mt', 'w');
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

    $this->setTitle('');
    $this->setDescription(''); 

    if (empty($source)) {
      return;
    }

    $this->urls   = array();
    $this->files  = new ObjectList();
    $this->todos  = new Priorities();

    // find h1's and title
    $title = '';
    if (preg_match_all('/^(#+)\s?([^#].*)/m', $source, $matches)) {
      $this->parsed->headings = array();
      foreach ($matches[2] as $key => $title_candidate) {
        $element = 'h' . strlen($matches[1][$key]);
        $find = $matches[0][$key];
        $this->parsed->headings[$element][$find] = $title_candidate;
      }
      ksort($this->parsed->headings);
      
      if (isset($this->parsed->headings['h1'])) {
        $title = reset($this->parsed->headings['h1']);

        // Remove the line that contains the title
        $find = key($this->parsed->headings['h1']);
        $remove = array_search($find, $this->parsed->lines);
        if ($remove == 0) {
          unset($this->parsed->lines[$remove]);        
        }
      }
    }

    $default_title = FALSE;
    if (empty($title) && ($t = $this->getConfig('default_title'))) {
      $title = $t;
      $default_title = TRUE;
    }

    $title = $this->parseFlags($title);
    $this->setTitle($title);

    // Find description
    if ($title && !$default_title) {
      $passed_title = FALSE;
      foreach ($this->parsed->p as $key => $value) {
        if (preg_match('/^#[^#]/', $value)) {
          $passed_title = TRUE;
          continue;
        }

        if ($passed_title && $value

          // Not a question
          && !$this->isQuestion($value)
          
          // Not an url
          && !$this->isUrl($value)
          
          // Not a file
          // @todo

          && preg_match('/^\w/', $value, $matches)) {
          $this->setDescription($value);

          // Strip it out of lines
          if ($this->parsed->p[1] === $this->getDescription()) {
            $haystack = implode(PHP_EOL, $this->parsed->lines);
            $needle = $this->getDescription();
            $result = trim(str_replace($needle, '', $haystack));
            $this->parsed->lines = explode(PHP_EOL, $result);
          }
          break;
        }
      }
    }

    if ($title
      && !$this->getDescription()
      && ($d = $this->getConfig('default_description'))) {
      $this->setDescription($d);
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
    
    // We need to determine the highest id before we start assigning
    $schema = $this->getFlagSchema();
    $regex = '/@' . $schema['id']->regex . '/';
    $highest_id = 0;
    if (preg_match_all($regex, $source, $matches)) {
      $highest_id = (int) max($matches[2]);
    }
    $highest_id = max($highest_id, ($this->getConfig('auto_increment') - 1));

    $auto_id = 0;
    if ($highest_id < $this->getConfig('auto_increment')) {
      $auto_id = $this->getConfig('auto_increment');
    }
    elseif ($highest_id > 0) {
      $auto_id = ++$highest_id;
    }

    $todos = $urls = array();
    $candidates = array();
    foreach ((array) $this->parsed->lines as $line_number => $line) {
      if (trim($line)) {
        $candidate = new Todo($line, $this->getConfig());
        if ($candidate->getParsed('valid_syntax')) {

          // Add the next available numeric id if we don't have one.
          $id = $candidate->getFlag('id');
          if (!$id && (string) $id !== '0') {
            // $auto_id = $this->todos->getList()->getNextId($auto_id);
            $candidate->setFlag('id', $auto_id++);
          }

          $this->todos->getList()->add($candidate);
          $this->parsed->todos_by_line[$candidate->getFlag('id')][] = $line_number;
        }
      }
    }
    $this->todos->getList()->generateIds($auto_id);
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
    foreach ($this->getParsed('todos_by_line') as $id => $line_numbers) {
      foreach ($this->getTodos()->getList()->get($id) as $todo) {
        if ($todo->isComplete()) {
          foreach ($line_numbers as $line_number) {
            $this->deleteLine($line_number);
          }
        }
      }
    }

    return $this;
  }  
}