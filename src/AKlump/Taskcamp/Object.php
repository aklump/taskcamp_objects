<?php
namespace AKlump\Taskcamp;

/**
 * Class Object
 */
abstract class Object implements ObjectInterface {
  protected $parsed, $flags;

  protected $source = '';
  protected $title = '';
  protected $description = '';

  protected $data = array(
    'source' => '',
    'config' => array(),
  );
  
  /**
   * Constructor
   * @param mixed $source
   * @param  array $config key/value pairs
   */
  public function __construct($source = '', $config = array()) {
    $this->parsed = new \stdClass;
    $this->flags = array();
    $this->setConfig($config);
    $this->setSource($source);
  }
  
  public function setConfig($config) {
    $this->data['config'] = new \stdClass;
    $config = (array) $config + array(
      'timezone' => 'America/Los_Angeles', 
      'milestone' => 14 * 86400, 
      'flag_prefix' => '@', 
      'weight' => 1000, 
    );    
    foreach($config as $key => $value) {
      $this->setConfigItem($key, $value);
    }
  
    return $this;
  }
  
  public function setConfigItem($key, $value) {
    $this->data['config']->{$key} = $value;
  
    return $this;
  }
  
  public function getConfig($key = NULL) {
    if ($key === NULL) {
      return $this->data['config'];
    }

    return isset($this->data['config']->{$key}) ? $this->data['config']->{$key} : NULL;
  }

  public static function dateRegex() {
    return '((\d{4})\-?(\d{1,2})\-?(\d{1,2}))T(\d{1,2}:\d{2})|(\d{4})\-?(\d{1,2})\-?(\d{1,2})|(\d{1,2}:\d{2})';
  }

  public function setSource($source) {
    $this->data['source'] = (string) $source;
    $this->parse();
  
    return $this;
  }
  
  public function getSource() {
    return $this->data['source'];
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
    $date = new \DateTime($string, new \DateTimeZone($this->getConfig('timezone')));

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

  public function complete($time = NULL) {
    return $this;
  }

  public function unComplete() {
    return $this;
  }

  public function setFlag($flag, $value) {
    $this->flags[$flag] = $value;
  
    return $this;
  }  

  public function getFlag($flag, $typecast = FALSE) {
    static $valid_flags, $types = NULL;
    if ($valid_flags === NULL) {
      $valid_flags = array();
      $types = array();
      foreach ($this->getFlagSchema() as $key => $value) {
        $valid_flags[$key] = $value->id;
        $types[$value->id] = $value->type;
      }
    }
    if (!in_array($flag, $valid_flags)) {
      return NULL;
    }
    $value = isset($this->flags[$flag]) ? $this->flags[$flag] : NULL;


    if ($typecast) {
      switch ($types[$flag]) {
        case 'datetime':
          $value = $value ? $this->createDate($value) : $value;
          break;

        case 'string':
          $value = (string) $value;
          break;
      }
    }

    return $value;
  }

  public function getFlags($implode = TRUE) {
    $flags = array();
    foreach ($this->getFlagSchema() as $data) {
      if ($value = $this->getFlag($data->id, FALSE)) {
        if ($value === TRUE) {
          $value = '';
        }
        if (strpos($value, ' ') !== FALSE) {
          $value = '"' . $value . '"';
        }        
        $flags[$data->id] = $this->getConfig('flag_prefix') . $data->flag . $value;
      }
    }

    return $implode ? implode(' ', $flags) : $flags;
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
          'flag' => 'id', 
          'type' => 'string',
          'description' => 'A unique ID.',
          'id' => 'id',
          'name' => 'Id',
          'regex' => '(id)("[^"]+"|[^\s]+)',
        ),
        (object) array(
          'flag' => 'w', 
          'type' => 'float',
          'description' => 'Lower numbers are ranked higher in priority.',
          'id' => 'weight',
          'name' => 'Priority',
          'regex' => '(w)(\-?[\d]+)',
        ),      
        (object) array(
          'flag' => 'p', 
          'type' => 'string',
          'description' => 'The name of the person responsible.',
          'id' => 'person',
          'name' => 'Assigned To',
          'regex' => '(p)("[^"]+"|[^\s]+)',
        ),
        (object) array(
          'flag' => 'e', 
          'type' => 'float',
          'description' => 'The estimated hours to done.',
          'id' => 'estimate',
          'name' => 'Estimate',
          'regex' => '(e)([.\d]+)',
        ),
        (object) array(
          'flag' => 'g', 
          'type' => 'string',
          'description' => 'The name of a group this belongs to, e.g. Wednesday.',
          'id' => 'group',
          'name' => 'Group',
          'regex' => '(g)("[^"]+"|[^\s]+)',
        ),
        (object) array(
          'flag' => 's', 
          'type' => 'datetime',
          'description' => 'The date to start work.',
          'id' => 'start',
          'name' => 'Start Time',
          'regex' => '(s)(' . $this->dateRegex() . ')?',
        ),
        (object) array(
          'flag' => 'm', 
          'type' => 'datetime',
          'description' => 'The next milestone date.',
          'id' => 'milestone',
          'name' => 'Milestone',
          'regex' => '(m)(' . $this->dateRegex() . ')?',
        ),  
        (object) array(
          'flag' => 'f', 
          'type' => 'datetime',
          'description' => 'The date it needs to be finished.',
          'id' => 'finish',
          'name' => 'Finish',
          'regex' => '(f)(' . $this->dateRegex() . ')?',
        ),  
        (object) array(
          'flag' => 'd', 
          'type' => 'datetime',
          'description' => 'The date it was done and complete.',
          'id' => 'done',
          'name' => 'Completed',
          'regex' => '(d)(' . $this->dateRegex() . ')?',
        ),
        (object) array(
          'flag' => 'h', 
          'type' => 'float',
          'description' => 'Actual hours from start to finish.',
          'id' => 'hours',
          'name' => 'Hours',
          'regex' => '(h)([.\d]+)',
        ),        

        // Third party integration
        (object) array(
          'flag' => 'bc', 
          'type' => 'string',
          'description' => 'The Basecamp unique ID.',
          'id' => 'basecamp',
          'name' => 'Basecamp Id',
          'regex' => '(bc)(\d{6,})',
        ),
        (object) array(
          'flag' => 'mt', 
          'type' => 'string',
          'description' => 'The Mantis unique ID.',
          'id' => 'mantis',
          'name' => 'Mantis Id',
          'regex' => '(man)(\d+)',
        ),        
        (object) array(
          'flag' => 'qb', 
          'type' => 'string',
          'description' => 'The Quickbooks job.',
          'id' => 'quickbooks',
          'name' => 'Quickbooks Job',
          'regex' => '(qb)("[^"]+"|[^\s]+)',
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
      $this->flags[$flag->id] = NULL;
      $regex = '/(?<!\\\)\\' . $this->getConfig('flag_prefix') . $flag->regex . '/';
      if (preg_match($regex, $text, $matches)) {
        $text = str_replace($matches[0], '', $text);
        $value = array_key_exists(2, $matches) ? trim($matches[2], ' "') : TRUE;
        $this->flags[$flag->id] = $value;
      }
    }

    // Append start time
    if ($this->flags['start'] === TRUE) {
      $this->flags['start'] = $this->getTime();
    }

    // Adjust a boolean done to complete
    if ($this->flags['done']) {
      $time = $this->flags['done'] === TRUE ? $this->getDateTime() : $this->flags['done'];
      $this->complete($time);
    }

    // @todo move to getFlag?
    if ($this->flags['milestone'] === TRUE) {
      $this->flags['milestone'] = $this->getDateTime("+ {$this->getConfig('milestone')} seconds");
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