<?php

namespace AKlump\Taskcamp;

interface TaskInterface {

    public function getTask($default = null);

    public function setTask($task);

    public function getStart($default = null);

    public function setStart(\DateTime $start = null);

    public function getDone($default = null);

    public function setDone(\DateTime $done = null);

    public function setNotDone();

    public function getFinish($default = null);

    public function setFinish(\DateTime $finish = null);

    public function getMilestone($default = null);

    public function setMilestone(\DateTime $milestone);

    public function getEstimate($default = null);

    public function setEstimate($estimate);

    public function getActual($default = null);

    public function isDone($default = null);

    /**
     * Returns the difference between the estimate and the actual.  Negative means under budget.
     *
     * @return int|null
     */
    public function getVariance();

    /**
     * The actual duration in minutes.
     *
     * @return int|null
     */
    public function getDuration();

    public function getPerson($default = null);

    public function setPerson($person);
}
