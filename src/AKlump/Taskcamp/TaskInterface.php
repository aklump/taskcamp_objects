<?php

namespace AKlump\Taskcamp;

interface TaskInterface {

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

    public function getOverUnder($default = null);

    public function getPerson($default = null);

    public function setPerson($person);
}
