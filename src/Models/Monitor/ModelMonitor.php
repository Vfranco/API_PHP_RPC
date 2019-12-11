<?php

namespace Models\Monitor;

use Database\Database;
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
        $misvisitantes = Database::storeProcedure("CALL getMisVisitantesRegistros('". $this->formData['uid'] ."')")->records()->resultToArray();

        if(!ModelGeneral::hasRows($misvisitantes))
            return ['status' => false, 'message' => 'no hay registros'];

        return ['status' => true, 'rows' => $misvisitantes ];
    }

    public function misContratistas()
    {
        $miscontratistas = Database::storeProcedure("CALL getMisContratistasRegistros('". $this->formData['uid'] ."')")->records()->resultToArray();

        if(!ModelGeneral::hasRows($miscontratistas))
            return ['status' => false, 'message' => 'no hay registros'];

        return ['status' => true, 'rows' => $miscontratistas ];
    }
}