<?php

namespace App\Models\TeacDisc;

use CodeIgniter\Model;

class TeacDiscModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'tb_teacher_discipline';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields        = ['id_teacher', 'id_discipline', 'amount', 'color', 'id_year_school', 'status'];


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

    public function getTeacherDiscipline()
    {
        return $this->select(
            't.name, 
            d.description,
            ' . $this->table . '.id,
            ' . $this->table . '.id_teacher,
            ' . $this->table . '.amount,
            ' . $this->table . '.color,
            d.abbreviation'
        )
            ->join('tb_teacher t', 't.id =' . $this->table . '.id_teacher')
            ->join('tb_discipline d', 'd.id =' . $this->table . '.id_discipline')
            ->orderBy('t.name')
            ->where($this->table . '.id_year_school', session('session_idYearSchool'))
            ->findAll();
    }

    public function getTeacherDisciplineByIdTeacher(int $idTeacher)
    {
        return $this->select( 'd.icone, '.$this->table . '.color, ' . $this->table . '.id_teacher, d.description, d.abbreviation, ' . $this->table . '.id,' .$this->table . '.id_discipline,' . $this->table . '.amount')
            ->join('tb_discipline d', 'd.id =' . $this->table . '.id_discipline')
            ->where($this->table . '.id_teacher', $idTeacher)
            ->where($this->table . '.id_year_school', session('session_idYearSchool')) 
            ->orderBy('d.abbreviation','ASC')           
            ->findAll();
    }
    public function getTeacherWithoutDisciplineByIdTeacher(int $idTeacher)
    {
        return $this->select($this->table . '.id_discipline ,'.$this->table . '.color, ' . $this->table . '.id_teacher, d.description, d.abbreviation, ' . $this->table . '.id,' . $this->table . '.amount')
            ->join('tb_discipline d', $this->table . '.id_discipline = d.id','RIGHT')
            ->where($this->table . '.id_teacher', $idTeacher)
            ->where($this->table . '.id_year_school', session('session_idYearSchool'))
            ->findAll();
            //->join('tb_operador_permissao OP', 'P.idPermissao = OP.idPermissao AND (OP.idOperador) =' . $idOperador, 'LEFT')
    }

    public function getTeacherDisciplineById(int $id)
    {
        $a = $this->select(
            't.name, 
            d.description,
            ' . $this->table . '.id,
            ' . $this->table . '.id_teacher,
            ' . $this->table . '.amount,
            ' . $this->table . '.color,
            ' . $this->table . '.id_year_school,           
            d.abbreviation,
            d.icone'
        )
            ->join('tb_teacher t', 't.id =' . $this->table . '.id_teacher')
            ->join('tb_discipline d', 'd.id =' . $this->table . '.id_discipline')           
            ->where($this->table . '.id_teacher', $id)
            ->orderBy('t.name')
            ->get()->getResultObject();
        //dd($a);
        return $a;
    }
    public function getByIdTeacherDiscipline(int $id)
    {
        $a = $this->select(
            't.name, 
            d.description,
            ' . $this->table . '.id,
            ' . $this->table . '.id_teacher,
            ' . $this->table . '.amount,
            ' . $this->table . '.color,
            ' . $this->table . '.id_year_school,           
            d.abbreviation,
            d.icone'
        )
            ->join('tb_teacher t', 't.id =' . $this->table . '.id_teacher')
            ->join('tb_discipline d', 'd.id =' . $this->table . '.id_discipline')           
            ->where($this->table . '.id', $id)
            ->orderBy('t.name')
            ->get()->getResultObject();
        //dd($a);
        return $a;
    }

    public function saveTeacherDiscipline(array $data)
    {

        // $professor['amount'] = $data['amount'];
        // $professor['color'] = $data['color'];
        // $professor['status'] = $data['status'];
        // $professor['id_teacher'] = $data['id_teacher'];

        //foreach ($data['disciplines'] as $item) {
            $teacDisc = new TeacDiscModel();
            $teacherDiscipline['id_teacher'] =  $data['id_teacher'];
            $teacherDiscipline['id_discipline'] =$data['disciplines'];
            $teacherDiscipline['amount'] = $data['amount'];
            $teacherDiscipline['status'] = 'A';
            $teacherDiscipline['id_year_school'] = session('session_idYearSchool');
            $teacherDiscipline['color'] = $data['color'] == '#000000' ? generationColor() : $data['color'] ;
            $teacDisc->save($teacherDiscipline);
        //}
        return true;
        //dd($result);
    }

    public function getTeacherDisciplineAll()
    {
        $a = $this->select(
            't.name, 
            d.description,
            ' . $this->table . '.id,
            ' . $this->table . '.id_teacher,
            ' . $this->table . '.amount,
            ' . $this->table . '.color,
            d.abbreviation'
        )
            ->join('tb_teacher t', 't.id =' . $this->table . '.id_teacher','left')
            ->join('tb_discipline d', 'd.id =' . $this->table . '.id_discipline')
            //->where($this->table . '.id', $id)
            ->orderBy('t.name')
            ->get()->getResultObject();
        //dd($a);
        return $a;
    }

    public function getDisciplineValidationDuplicate($id_discipline) : int
    {
         $a = $this->where('id_discipline', $id_discipline)
                    //->where('id_discipline', $id_discipline)
                    //->where('id_year_school', session('session_yearSchool'))
                   // ->where('status', $status)
                    ->countAll();
                    //dd($a);
                    return $a;
    }

    public function getTeacDiscByIdYear($idYear)
    {
      return $this->select('id_teacher,id_discipline, amount, color')
            ->where('id_year_school',$idYear)
            ->where('status','A')
            ->get()
            ->getResult(); 

    }
}
