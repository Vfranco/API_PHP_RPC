<?php

namespace Models\Reportes;

use Database\Database;
use Core\{Validate};
use Models\General\ModelGeneral;

class ModelReportes
{
    private $formData;
    
    public function __construct($formData)
    {
        $this->formData = $formData;
        return $this;
    }

    public function ReportePorSedes()
    {
        $result = Database::query([
            'fields'    => "ss.`nombre_sede` as sede, (SELECT COUNT(`id_sg_registro`) FROM `sg_registros_mi_personal` WHERE id_sg_sede = ss.`id_sg_sede`) AS visitas",
            'table'     => "sg_sedes ss",
            'arguments' => "ss.id_sg_empresa = '". ModelGeneral::getIdEmpresaByUser($this->formData['id_cms_empresa']) ."'"
        ])->records()->resultToArray();

        if(isset($result[0]['empty']) && $result[0]['empty'] == true)
            return ['status' => false, 'message' => 'no se encontraron datos'];

        return [
            'status'    => true,
            'rows'      => $result
        ];
    }
}