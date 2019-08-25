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
            'fields'    => "distinct cs.nombre_sede as sede, (select count(id_cms_registro_actividad) from cms_registro_actividad where cms_sedes_id_cms_sede = cs.id_cms_sede) as visitas",
            'table'     => "cms_sedes cs join cms_registro_actividad cra ON cs.id_cms_sede = cra.cms_sedes_id_cms_sede",
            'arguments' => "cs.cms_empresas_id_cms_empresas = '". ModelGeneral::getIdEmpresaByUser($this->formData['id_cms_empresa']) ."'"
        ])->records()->resultToArray();

        if(isset($result[0]['empty']) && $result[0]['empty'] == true)
            return ['status' => false, 'message' => 'no se encontraron datos'];

        return [
            'status'    => true,
            'rows'      => $result
        ];
    }
}