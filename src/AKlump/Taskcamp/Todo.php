<?php
/**
 * @file
 * Defines the todo object
 *
 * @ingroup taskcamp_objects
 * @{
 */
namespace AKlump\Taskcamp;

class Todo extends Object implements TodoInterface {

  public function __toString() {
    $output  = '- ';

    if (!$this->getParsed('valid_syntax')) {
      $output .= $this->source;
    }
    else {
      $output .= '[' . ($this->getParsed('complete') ? 'X' : ' ') . '] ';
      $output .= $this->getTitle();
      $output .= ' ' . $this->getFlags();      
    }
    
    return trim($output);    
  }

  public function isComplete() {
    return (bool) ($this->getFlag('done') || $this->getParsed('complete'));
  }

  public function getMarkdown() {
    return $this->__toString();
  }

  public function getHTML($parents = 0) {
    $output  = '';
    // @todo Need to build the js to support this
    $output .= '<span class="taskcamp-todo">' . PHP_EOL;
    $output .= '<input type="checkbox" onclick="Taskcamp.Todo.Toggle(); return false" /> ';
    $output .= '<a href="javascript:Taskcamp.Todo.Toggle(); return false">';
    $output .= $this->getTitle();
    $output .= '</a> ' . $this->getFlags();
    $output .= '</span>' . PHP_EOL;
    
    return trim($output);
  }

  public function complete($time = NULL) {
    if ($this->parsed->complete !== TRUE) {
      if ($time === NULL) {
        $time = $this->getDateTime();
      }
      $this->flags['done'] = $time;
      $this->parsed->complete = TRUE;      
      $this->flags['weight'] += $this->config->weight;
    }

    return $this;
  }

  public function unComplete() {
    if ($this->parsed->complete !== FALSE) {
      $this->flags['done'] = NULL;
      $this->parsed->complete = FALSE;
      if ($this->flags['weight'] != 0) {
        $this->flags['weight'] -= $this->config->weight;
      }
    }

    return $this;
  }

  public function getDuration() {
    $start = $this->getFlag('start');
    $done = $this->getFlag('done');
    if (empty($start) || empty($done)) {
      return FALSE;
    }

    $startObject  = $this->createDate($start, $done);
    $doneObject   = $this->createDate($done, $start);

    return $doneObject->format('U') - $startObject->format('U');
  }

  public function getCarryover() {
    $estimate = $this->getFlag('estimate');
    $duration = $this->getDuration();
    if (empty($estimate) || empty($duration)) {
      return FALSE;
    }

    // Convert hours to seconds
    $estimate *= 3600;

    return $estimate - $duration;
  }

  public function getAvailableFlags() {
    return array('w', 'p', 'bc', 'man', 'm', 'e', 's', 'd');;
  }

  /**
   * Parse the raw text
   *
   * Sets the value of $this->parsed
   *
   * @return bool
   *   FALSE means the todo couldn't be parsed
   */
  protected function parse() {
    $parsed = $this->source;

    // Expand lazy prefixes
    if (preg_match('/^--(X|x) ?(.*)/', $parsed, $matches)
      || preg_match('/^(?:(?:-{2} ?)|(?:- ?\[([ xX])?\] ?))(.*)/', $parsed, $matches)) {
      $parsed = '- [' . (trim($matches[1]) ? 'X' : ' ') . '] ' . $matches[2];
    }

    // Setup the defaults
    $this->parsed = array(
      'valid_syntax' => FALSE,
      'complete' => FALSE,
    );

    foreach ($this->getFlagSchema() as $data) {
      $this->parsed[$data->id] = NULL;
    }
    $this->parsed = (object) $this->parsed;

    // First parse to see if it's valid
    if (preg_match('/^- \[(X| )\]\s*(.*)/', $parsed, $found)) {
      $this->parsed->valid_syntax = TRUE;

      // Complete based on presence of an X or not; no date involved here.
      $this->parsed->complete = (bool) trim($found[1]);

      // Parse out flags, setting them and returning title.
      $title = $this->parseFlags($found[2]);
      $this->setTitle($title);

      $this->setDescription($title . ' ' . $this->getFlags());
    }

    return $this->parsed->valid_syntax;
  }
}