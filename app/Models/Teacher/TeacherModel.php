<?php

namespace App\Models\Teacher;

use App\Models\TeacDisc\TeacDiscModel;
use CodeIgniter\Model;

class TeacherModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'tb_teacher';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields        = ['name', 'amount', 'status', 'color'];


    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function saveProfessor(array $data)
    {
        $professor['name'] = $data['name'];
        $professor['amount'] = $data['amount'];
        $professor['color'] = $data['color'];
        $professor['status'] = $data['status'];        

        $this->save($professor);
        $lastId = $this->getInsertID();
        //foreach ($data['disciplines'] as $item) {
            $teacDisc = new TeacDiscModel();
            $teacherDiscipline['id_teacher'] = $lastId;
            //$teacherDiscipline['id_discipline'] = $item;
            $teacherDiscipline['id_discipline'] = $data['disciplines'];
            $teacherDiscipline['amount'] = $data['amount'];
            $teacherDiscipline['status'] = $data['status'];
            $teacherDiscipline['id_year_school'] = $data['id_year_school'];
            $teacherDiscipline['color'] = $data['color'] == '#000000' ? generationColor() : $data['color'] ;
            $teacDisc->save($teacherDiscipline);
        //}
        //if(count($data['disciplines']) > 1){
            
            //return false;
        //}
        return true;
        //dd($result);
    }
}
