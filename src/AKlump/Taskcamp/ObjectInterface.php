<?php
namespace AKlump\Taskcamp;

/**
 * Interface TaskcampObject
 */
interface ObjectInterface {

  /**
   * Create an object from a string
   *
   * @param   string $string
   *
   * @return $this
   */
  public function set($string);

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
   * Return an a flag value
   *
   * @param  string The flag id
   *
   * @return object
   */
  public function getFlag($key);

  /**
   * Return a string of flags and values
   *
   * The flag order is defined by the order of elements in getFlagSchema().
   *
   * @return string
   *
   * @see getFlagSchema();
   */
  public function getFlags();

  /**
   * Return a key from $this->parsed
   *
   * @return mixed
   */
  public function getParsed($key);
}