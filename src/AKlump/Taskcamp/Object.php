<?php
namespace AKlump\Taskcamp;

/**
 * Class Object
 */
abstract class Object implements ObjectInterface {
  protected $source, $config, $parsed, $title, $description;

  /**
   * Constructor
   * @param mixed $source
   */
  public function __construct($source = '', $config = array()) {
    $this->parsed = new \stdClass;
    $this->config = new \stdClass;
    $this->config($config);
    $this->set($source);
  }

  public static function dateRegex() {
    return '((\d{4})\-?(\d{1,2})\-?(\d{1,2}))T(\d{1,2}:\d{2})|(\d{4})\-?(\d{1,2})\-?(\d{1,2})|(\d{1,2}:\d{2})';
  }

  /**
   * Get/Set configuration variables
   *
   * @param  object $config
   *   Optional.  Omit this to retrieve current configuration.  NOTE:
   *   Subsequent calls to this function will overwrite, current values, but
   *   include current values not provided in $config.  The only way to have
   *   a clean config is to instantiate a new object.
   *
   * @return object
   *   The current configuration object
   */
  public function config($config = NULL) {
    $defaults = array(
      'timezone' => 'America/Los_Angeles', 
      'milestone' => 14 * 86400, 
      'flag_prefix' => '@', 
      'weight' => 1000, 
    );
    if ($config !== NULL) {
      $this->config = (object) ((array) $config + (array) $this->config + (array) $defaults);
    }

    return $this->config;
  }

  public function set($source) {
    $this->source = (string) $source;
    $this->parse();
  }

  /**
   * Create a DateTime object from a string using config timezone
   *
   * @param  string $string DateTime string
   *
   * @return DateTime
   */
  public function createDate($string = 'now', $context = NULL) {
    preg_match('/' . $this->dateRegex() . '/', $string, $matches);
    preg_match('/' . $this->dateRegex() . '/', $context, $context);
    $missing_date = strpos($matches[0], 'T') === FALSE;
    if ($missing_date && !empty($context[1])) {
      $string = $context[1] . 'T' . $string;
    }
    $date = new \DateTime($string, new \DateTimeZone($this->config->timezone));

    return $date;
  }
  
  /**
   * Return a formated DATE string of now (or from string)
   *
   * @param  string $string Optional. Defaults to now
   *
   * @return string         
   *
   * @see  ObjectInterface::createDate()
   */
  public function getDate($string = 'now') {
    return $this->createDate($string)->format('Y-m-d');
  }

  /**
   * Return a formated TIME string of now (or from string)
   *
   * @param  string $string Optional. Defaults to now
   *
   * @return string         
   *
   * @see  ObjectInterface::createDate()
   */
  public function getTime($string = 'now') {
    return $this->createDate($string)->format('G:i');
  }

  /**
   * Return a formated DATETIME string of now (or from string)
   *
   * @param  string $string Optional. Defaults to now
   *
   * @return string         
   *
   * @see  ObjectInterface::createDate()
   */
  public function getDateTime($string = 'now') {
    return $this->createDate($string)->format('Y-m-d\TH:i');
  }
  
  public function getParsed($key) {
    return isset($this->parsed->{$key}) ? $this->parsed->{$key} : NULL;
  }

  public function getFlag($flag) {
    static $valid_flags = NULL;
    if ($valid_flags === NULL) {
      $valid_flags = array();
      foreach ($this->getFlagSchema() as $key => $value) {
        $valid_flags[$key] = $value->id;
      }
    }
    if (!in_array($flag, $valid_flags)) {
      return NULL;
    }
    return isset($this->parsed->{$flag}) ? $this->parsed->{$flag} : NULL;
  }

  public function getFlags() {
    $flags = array();
    foreach ($this->getFlagSchema() as $data) {
      if ($value = $this->getFlag($data->id)) {
        if ($value === TRUE) {
          $value = '';
        }
        $flags[] = $this->config->flag_prefix . $data->flag . $value;
      }
    }

    return implode(' ', $flags);
  }

  public function setTitle($title) {
    $this->title = (string) trim($title);
  }
  
  public function getTitle() {
    return $this->title;
  }
  
  public function setDescription($description) {
    $this->description = (string) trim($description);
  }
  
  public function getDescription() {
    return $this->description;
  }

  public function __toString() {
    $build = array();
    $build[] = $this->getTitle();
    $build[] = $this->getDescription();
    $build[] = '';

    return implode(PHP_EOL, $build);
  }  

  public function getMarkdown(){
    return (string) $this;
  }
  
  public function getHTML($parents = 0){
    $build = array();
    $parents += 1;
    $tag = "h{$parents}";
    $build[] = "<$tag>" . $this->getTitle() . "</$tag>";
    $build[] = '<p>' . $this->getDescription() . '</p>';
    $build[] = '';

    return implode(PHP_EOL, $build);
  }

  /**
   * Return an array of flag definitions
   *
   * Note: ids and names, if verbs, should be present tense.
   *
   * @return array
   *   Keys are the flags.
   *   Each element is an object with these properties:
   *   - flag
   *   - id
   *   - name
   *   - regex
   */  
  public function getFlagSchema() {
    static $schema = NULL;

    if ($schema === NULL) {
      
      $all_flags = array(
        (object) array(
          'flag' => 'w', 
          'description' => 'Lower numbers are ranked higher in priority.',
          'id' => 'weight',
          'name' => 'Priority',
          'regex' => '(w)(\-?[\d]+)',
        ),      
        (object) array(
          'flag' => 'p', 
          'description' => 'The name of the person responsible.',
          'id' => 'person',
          'name' => 'Assigned To',
          'regex' => '(p)([^ ]+)',
        ),
        (object) array(
          'flag' => 'bc', 
          'description' => 'The Basecamp unique ID.',
          'id' => 'basecamp',
          'name' => 'Basecamp Id',
          'regex' => '(bc)(\d{6,})',
        ),
        (object) array(
          'flag' => 'man', 
          'description' => 'The Mantis unique ID.',
          'id' => 'mantis',
          'name' => 'Mantis Id',
          'regex' => '(man)(\d+)',
        ),
        (object) array(
          'flag' => 'e', 
          'description' => 'The estimated hours to done.',
          'id' => 'estimate',
          'name' => 'Estimate',
          'regex' => '(e)([.\d]+)',
        ),
        (object) array(
          'flag' => 'g', 
          'description' => 'The name of a group this belongs to, e.g. Wednesday.',
          'id' => 'group',
          'name' => 'Group',
          'regex' => '(g)([^\s]+)',
        ),
        (object) array(
          'flag' => 's', 
          'description' => 'The date to start work.',
          'id' => 'start',
          'name' => 'Start Time',
          'regex' => '(s)(' . $this->dateRegex() . ')?',
        ),
        (object) array(
          'flag' => 'm', 
          'description' => 'The next milestone date.',
          'id' => 'milestone',
          'name' => 'Milestone',
          'regex' => '(m)(' . $this->dateRegex() . ')?',
        ),  
        (object) array(
          'flag' => 'f', 
          'description' => 'The date it needs to be finished.',
          'id' => 'finish',
          'name' => 'Finish',
          'regex' => '(f)(' . $this->dateRegex() . ')?',
        ),  
        (object) array(
          'flag' => 'd', 
          'description' => 'The date it was done and complete.',
          'id' => 'done',
          'name' => 'Completed',
          'regex' => '(d)(' . $this->dateRegex() . ')?',
        ),
      );

      $filtered = array();
      $available = $this->getAvailableFlags();
      foreach ($all_flags as $data) {
        if (in_array($data->flag, $available)) {
          $filtered[$data->flag] = $data;
        }
      }

      $filtered2 = array();
      foreach ($available as $flag) {
        $filtered2[$flag] = $filtered[$flag];
      }

      $schema = $filtered2;
    }

    return $schema;
  }

  /**
   * Transforms Object::source into Object::parsed
   */
  abstract protected function parse();

  /**
   * Given a string of text parse out and set flags
   *
   * @param  string $text
   *
   * @return  string The trimmed string with flags removed.
   */
  protected function parseFlags($text) {
    $flags = $this->getFlagSchema();
    foreach ($flags as $flag) {
      $this->parsed->{$flag->id} = NULL;
      $regex = '/\\' . $this->config->flag_prefix . $flag->regex . '/';
      if (preg_match($regex, $text, $found)) {
        $text = str_replace($found[0], '', $text);
        $value = array_key_exists(2, $found) ? $found[2] : TRUE;
        $this->parsed->{$flag->id} = $value;
      }
    }

    // Append start time
    if ($this->parsed->start === TRUE) {
      $this->parsed->start = $this->getTime();
    }

    // Adjust a boolean done to complete
    if ($this->parsed->done) {
      $time = $this->parsed->done === TRUE ? $this->getDateTime() : $this->parsed->done;
      $this->complete($time);
    }

    // @todo move to getFlag?
    if ($this->parsed->milestone === TRUE) {
      $this->parsed->milestone = $this->getDateTime("+ {$this->config->milestone} seconds");
    }

    return trim($text);
  }

  /**
   * Return an array of flags usable in this object
   *
   * @return array e.g. array('e', 'm');
   */
  abstract public function getAvailableFlags();
}