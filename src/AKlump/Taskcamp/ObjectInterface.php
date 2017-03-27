<?php

namespace AKlump\Taskcamp;

/**
 * Interface TaskcampObject
 */
interface ObjectInterface {

    /**
     * @return array
     */
    public static function getAvailableFlags();

    /**
     * Set the config array.
     *
     * @param array||object $config
     *        - milestone The number of seconds from now for milestones
     *        - timezone string The timezone name
     *        - flag_prefix string e.g. '@'
     *        - weight int
     *        - show_ids bool
     *
     * @return $this
     */
    public function setConfig($config);

    /**
     * Adds a single config
     *
     * @param $key
     * @param $value
     *
     * @return $this
     * @internal param mixed $config
     */
    public function setConfigItem($key, $value);

    /**
     * Return the config object or a single item by key.
     *
     * @param  string $key Omit to return all config items.
     *
     * @return mixed||object
     */
    public function getConfig($key = null);

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
     * Set the questions array.
     *
     * @param array $questions
     *
     * @return $this
     */
    public function setQuestions($questions);

    /**
     * Adds a single question.
     *
     * @param array $question
     *
     * @return $this
     */
    public function addQuestion($question);

    /**
     * Return the questions array.
     *
     * @return array
     */
    public function getQuestions();

    /**
     * Set a flag.
     *
     * @param string $flag The machine name of the flag.
     * @param mixed  $value
     *
     * @return $this
     */
    public function setFlag($flag, $value);

    /**
     * Return an a flag value
     *
     * @param      string    The flag id
     * @param bool $typecast Set to true to typecase the flag value based on
     *                       schema
     *
     * @return mixed
     */
    public function getFlag($key, $typecast = false);

    /**
     * Return a string of flags and values
     *
     * The flag order is defined by the order of elements in getFlagSchema().
     *
     * @param  bool $implode Set to false to return an array, instead of a
     *                       string.
     *
     * @return string
     *
     * @see getFlagSchema();
     */
    public function getFlags($implode = true);

    /**
     * Return a key from $this->parsed
     *
     * @param $key
     *
     * @return mixed
     */
    public function getParsed($key);

    /**
     * Deletes a single line of content by line number
     *
     * @param $line_number
     *
     * @return string The string content of the deleted line.
     */
    public function deleteLine($line_number);
}
