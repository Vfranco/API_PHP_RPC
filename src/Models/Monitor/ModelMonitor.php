<?php

namespace Models\Monitor;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelMonitor
{
    private $formData;

    public function __construct($formData)
    {
        $this->formData = $formData;
        return $this;
    }

    public function miPersonal()
    {
        $mipersonal = Database::storeProcedure("CALL getMiPersonalRegistros('". $this->formData['uid'] ."')")->records()->resultToArray();

        if(!ModelGeneral::hasRows($mipersonal))
            return ['status' => false, 'message' => 'no hay registros'];

        return ['status' => true, 'rows' => $mipersonal ];
    }

    public function misVisitantes()
    {

    }

    public function misContratistas()
    {

    }
}