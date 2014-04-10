<?php
namespace AKlump\Taskcamp;

/**
 * Class Object
 */
abstract class Object implements ObjectInterface {
  protected $parsed, $flags;

  protected $title = '';
  protected $description = '';

  protected $data = array(
    'source' => '',
    'config' => array(),
    'questions' => array(),
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

  public function setQuestions($questions) {
    $this->data['questions'] = array();
    foreach($questions as $question) {
      $this->addQuestion($question);
    }
  
    return $this;
  }
  
  public function addQuestion($question) {
    $this->data['questions'][] = $question;
  
    return $this;
  }
  
  public function getQuestions() {
    return $this->data['questions'];
  }  

  public function setSource($source) {
    $this->data['source'] = (string) $source;   
    $this->parse();

    return $this;
  }
  
  public function getSource() {
    return $this->data['source'];
  }  
  
  public function setConfig($config) {
    $this->data['config'] = new \stdClass;
    $config = (array) $config + array(
      'timezone' => 'UTC', 
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
    return '(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}[+-]\d{4})|(\d{4}-\d{2}-\d{2})|(\d{2}:\d{2}[+-]\d{4})|(\d{2}:\d{2})';
  }

  /**
   * Create a DateTime object from a string using config timezone
   *
   * @param  string $string DateTime string
   * @param  string $context DateTime string The date and timezone will be
   * pulled from this string if missing from $string.
   *
   * @return DateTime
   */
  public function createDate($string = 'now', $context = NULL) {
    $date_parts = '(?:(\d{4}-\d{2}-\d{2}))?T?(?:(\d{2}:\d{2}))?(?:([+-]\d{4}))?';
    preg_match("/$date_parts/", $string, $matches);
    array_shift($matches);

    if (preg_match("/$date_parts/", $context, $context)) {
      array_shift($context);
    }

    if (empty($matches[0]) && !empty($context[0])) {
      $matches[0] = $context[0];
    }

    if (empty($matches[2]) && !empty($context[2])) {
      $matches[2] = $context[2];
    }

    $matches[0] .= 'T';
    $string = implode('', $matches);

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
    return $this->createDate($string)->format('G:iO');
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
    return $this->createDate($string)->format('Y-m-d\TH:iO');
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
      $value = $this->getFlag($data->id, FALSE);
      $show_zero = is_numeric($value) && !$value && !$data->hide_empty;

      if (!$value && !$show_zero) {
        continue;
      }
      if (!$value) {
        $value = $this->getFlag($data->id, TRUE);
      }
      if ($value === TRUE) {
        $value = '';
      }
      if (strpos($value, ' ') !== FALSE) {
        $value = '"' . $value . '"';
      }        
      $flags[$data->id] = $this->getConfig('flag_prefix') . $data->flag . $value;
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
   *   - type string for typecasting
   *   - id
   *   - name
   *   - regex
   *   - hide_empty bool Should we hide an empty flag, say for @id0 or @w0
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
          'hide_empty' => FALSE,
        ),
        (object) array(
          'flag' => 'w', 
          'type' => 'float',
          'description' => 'Lower numbers are ranked higher in priority.',
          'id' => 'weight',
          'name' => 'Priority',
          'regex' => '(w)(\-?[\d]+)',
          'hide_empty' => TRUE,
        ),      
        (object) array(
          'flag' => 'p', 
          'type' => 'string',
          'description' => 'The name of the person responsible.',
          'id' => 'person',
          'name' => 'Assigned To',
          'regex' => '(p)("[^"]+"|[^\s]+)',
          'hide_empty' => TRUE,
        ),
        (object) array(
          'flag' => 'e', 
          'type' => 'float',
          'description' => 'The estimated hours to done.',
          'id' => 'estimate',
          'name' => 'Estimate',
          'regex' => '(e)([.\d]+)',
          'hide_empty' => TRUE,
        ),
        (object) array(
          'flag' => 'g', 
          'type' => 'string',
          'description' => 'The name of a group this belongs to, e.g. Wednesday.',
          'id' => 'group',
          'name' => 'Group',
          'regex' => '(g)("[^"]+"|[^\s]+)',
          'hide_empty' => TRUE,
        ),
        (object) array(
          'flag' => 's', 
          'type' => 'datetime',
          'description' => 'The date to start work.',
          'id' => 'start',
          'name' => 'Start Time',
          'regex' => '(s)(' . $this->dateRegex() . ')?',
          'hide_empty' => TRUE,
        ),
        (object) array(
          'flag' => 'm', 
          'type' => 'datetime',
          'description' => 'The next milestone date.',
          'id' => 'milestone',
          'name' => 'Milestone',
          'regex' => '(m)(' . $this->dateRegex() . ')?',
          'hide_empty' => TRUE,
        ),  
        (object) array(
          'flag' => 'f', 
          'type' => 'datetime',
          'description' => 'The date it needs to be finished.',
          'id' => 'finish',
          'name' => 'Finish',
          'regex' => '(f)(' . $this->dateRegex() . ')?',
          'hide_empty' => TRUE,
        ),  
        (object) array(
          'flag' => 'd', 
          'type' => 'datetime',
          'description' => 'The date it was done and complete.',
          'id' => 'done',
          'name' => 'Completed',
          'regex' => '(d)(' . $this->dateRegex() . ')?',
          'hide_empty' => TRUE,
        ),
        (object) array(
          'flag' => 'h', 
          'type' => 'float',
          'description' => 'Actual hours from start to finish.',
          'id' => 'hours',
          'name' => 'Hours',
          'regex' => '(h)([.\d]+)',
          'hide_empty' => TRUE,
        ),        

        // Third party integration
        (object) array(
          'flag' => 'bc', 
          'type' => 'string',
          'description' => 'The Basecamp unique ID.',
          'id' => 'basecamp',
          'name' => 'Basecamp Id',
          'regex' => '(bc)(\d{6,})',
          'hide_empty' => TRUE,
        ),
        (object) array(
          'flag' => 'mt', 
          'type' => 'string',
          'description' => 'The Mantis unique ID.',
          'id' => 'mantis',
          'name' => 'Mantis Id',
          'regex' => '(man)(\d+)',
          'hide_empty' => TRUE,
        ),        
        (object) array(
          'flag' => 'qb', 
          'type' => 'string',
          'description' => 'The Quickbooks job.',
          'id' => 'quickbooks',
          'name' => 'Quickbooks Job',
          'regex' => '(qb)("[^"]+"|[^\s]+)',
          'hide_empty' => TRUE,
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
  protected function parse() {
    // Trim front/back whitespace
    $source     = trim($this->getSource());

    $this->parsed = new \stdClass;
    $this->parsed->source = $source;

    $this->setQuestions(array()); 

    if (empty($source)) {
      return;
    }
    
    $this->parsed->lines = preg_split('/\n|\r\n?/', $source);

    // Grab all paragraphs and trim each
    $this->parsed->p   = array_values(array_filter(preg_split('/\n\n|\r\n\r\n?/', $source)));
    foreach ($this->parsed->p as $key => $value) {
      $this->parsed->p[$key] = trim(str_replace("\r\n", "\n", $value));
    }

    // Extract the questions
    foreach ($this->parsed->p as $p) {
      if (preg_match('/^\?\s*(.*)/s', $p, $matches)) {
        $this->addQuestion($matches[1]);
      }
    }    
  }

  public function deleteLine($line_number) {
    $return = $this->parsed->lines[$line_number];

    // Remove todos if found
    $id = $this->parsed->todos_by_line[$line_number];
    $this->getTodos()->getList()->remove($id);
    unset($this->parsed->todos_by_line[$line_number]);

    // Remove the line
    unset($this->parsed->lines[$line_number]);

    return $return;
  }

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