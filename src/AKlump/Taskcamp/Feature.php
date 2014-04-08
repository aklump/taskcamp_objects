<?php
namespace AKlump\Taskcamp;

/**
 * Represents a Taskcamp Feature.
 */
class Feature extends Object implements ObjectInterface {
  protected $urls, $todos, $files;

  /**
   * Add in todo specific 'show_ids' config key.
   *
   * @param array||object $config
   */
  public function setConfig($config) {
    $config = (array) $config + array(

      // Set this to true and todos will be rewritten on print
      'rewrite_todos' => TRUE,
    );

    return parent::setConfig($config);
  }

  public function __toString() {

    // Explode all todos
    if ($this->getConfig('rewrite_todos') && !empty($this->parsed->todos_by_line)) {
      foreach ($this->parsed->todos_by_line as $key => $value) {
        $this->parsed->lines[$key] = (string) $this->parsed->todos_by_line[$key];
      }
    }

    return implode(PHP_EOL, $this->getParsed('lines'));
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
    // Trim front/back whitespace
    $source     = trim($this->getSource());
    if (empty($source)) {
      return;
    }

    $this->parsed = new \stdClass;
    $this->urls   = array();
    $this->files  = new ObjectList();
    $this->todos  = new Priorities();
    $this->parsed->lines = preg_split('/\n|\r\n?/', $source);

    // Grab all paragraphs and trim each
    $this->parsed->p   = array_values(array_filter(preg_split('/\n\n|\r\n\r\n?/', $source)));
    foreach ($this->parsed->p as $key => $value) {
      $this->parsed->p[$key] = trim($value);
    }

    // find h1's and title
    $title = '';
    if (preg_match_all('/(#+)\s?([^#].*)/', $source, $matches)) {
      $this->parsed->headings = array();
      foreach ($matches[2] as $key => $title) {
        $element = 'h' . strlen($matches[1][$key]);
        $this->parsed->headings[$element][] = $title;
      }
      ksort($this->parsed->headings);
      
      $title = reset(reset($this->parsed->headings));
    }
    else {
      $title = $this->parsed->lines[0];
    }

    $title = $this->parseFlags($title);
    $this->setTitle($title);

    // Find description
    if (isset($this->parsed->p[1]) && preg_match('/^\w/', $this->parsed->p[1], $matches)) {
      $this->setDescription($this->parsed->p[1]);
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
        $candidate = new Todo($line, array('show_ids' => TRUE));
        if ($candidate->getParsed('valid_syntax')) {
          $this->todos->getList()->add($candidate);
          $this->parsed->todos_by_line[$line_index] = $candidate;
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
}