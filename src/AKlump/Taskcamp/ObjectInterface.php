<?php
namespace AKlump\Taskcamp;

/**
 * Interface TaskcampObject
 */
interface ObjectInterface {

  /**
   * Mark an object complete and add a done timestamp
   *
   * This will only work if the object is not already completed.  If you want
   * to update the @d flag use setFlag('done', ...) instead.
   *
   * @param  string $time The datetime string or NULL for now
   *
   * @return $this
   */
  public function complete($time = NULL);
  
  /**
   * Remove the complete status and done timestamp from an object
   *
   * @return $this
   */
  public function unComplete();  

  /**
   * Set the config array.
   *
   * @param array||object $config
   *
   * @return $this
   */
  public function setConfig($config);
  
  /**
   * Adds a single config
   *
   * @param mixed $config   
   *  
   * @return $this
   */
  public function setConfigItem($key, $value);
  
  /**
   * Return the config object or a single item by key.
   *
   * @param  string $key  Omit to return all config items.
   *
   * @return mixed||object
   */
  public function getConfig($key = NULL);

  /**
   * Set the source.
   *
   * @param string $source
   *
   * @return $this
   */
  public function setSource($source);
  
  /**
   * Return the source.
   *
   * @return string
   */
  public function getSource();

  /**
   * Return the Markdown text for the object
   *
   * @return string
   */
  public function getMarkdown();

  /**
   * Return the HTML markup for the object
   *
   * @param int $parents
   *   Optional, defaults to 0. The number of parents.  This is used to
   *   deteremine for example, the correct h tag based on context.
   *
   * @return string
   */
  public function getHTML($parents = 0);

  /**
   * Set Title
   *
   * @param string $title
   *
   * @return  $this
   */
  public function setTitle($title);

  /**
   * Set Description
   *
   * @param string $description
   *
   * @return  $this
   */
  public function setDescription($description);

  /**
   * Return the title of the object
   *
   * @return string
   */
  public function getTitle();

  /**
   * Return the description of the object
   *
   * @return string
   */
  public function getDescription();

  /**
   * Set a flag.
   *
   * @param string $flag The machine name of the flag.
   * @param mixed $value
   *
   * @return $this
   */
  public function setFlag($flag, $value);

  /**
   * Return an a flag value
   *
   * @param  string The flag id
   * @param bool $typecast Set to true to typecase the flag value based on schema
   *
   * @return mixed
   */
  public function getFlag($key, $typecast = FALSE);

  /**
   * Return a string of flags and values
   *
   * The flag order is defined by the order of elements in getFlagSchema().
   *
   * @param  bool $implode Set to false to return an array, instead of a string.
   *
   * @return string
   *
   * @see getFlagSchema();
   */
  public function getFlags($implode = TRUE);

  /**
   * Return a key from $this->parsed
   *
   * @return mixed
   */
  public function getParsed($key);

  /**
   * Deletes a single line of content by line number
   *
   * @return string  The string content of the deleted line.
   */
  public function deleteLine($line_number);
}