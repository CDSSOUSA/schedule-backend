<?php

namespace App\Models\Schedule;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'tb_school_schedule';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields        = ['id_allocation', 'dayWeek', 'position', 'id_series', 'status', 'id_year_school'];


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

    public function getTimeDayWeek(int $diaSemana, int $idSerie, int $posicao)
    {

        $result = $this->select(
            'p.name, 
            h.id_allocation, 
            pd.color, 
            pd.id_teacher,
            d.abbreviation,
            d.icone,
            h.id'
        )
            ->from('tb_school_schedule h')
            ->join('tb_allocation ap', 'h.id_allocation = ap.id')
            ->join('tb_teacher_discipline pd', 'ap.id_teacher_discipline = pd.id')
            ->join('tb_discipline d', 'pd.id_discipline = d.id')
            ->join('tb_teacher p', 'pd.id_teacher = p.id')
            ->where('h.dayWeek', $diaSemana)
            ->where('h.id_series', $idSerie)
            ->where('h.position', $posicao)
            ->where('h.id_year_school', session('session_idYearSchool'))
            ->get()->getRowArray();
        return $result;
    }
    public function getTimeDayWeekOcupation(int $diaSemana, int $idSerie, int $posicao)
    {

        $result = $this->select(
            'p.name, 
            h.id_allocation, 
            pd.color, 
            pd.id_teacher,
            d.abbreviation,
            d.icone,
            h.id,
            h.position,
            h.dayWeek'
        )
            ->from('tb_school_schedule h')
            ->join('tb_allocation ap', 'h.id_allocation = ap.id')
            ->join('tb_teacher_discipline pd', 'ap.id_teacher_discipline = pd.id')
            ->join('tb_discipline d', 'pd.id_discipline = d.id')
            ->join('tb_teacher p', 'pd.id_teacher = p.id')
            ->where('h.dayWeek', $diaSemana)
            ->where('h.id_series', $idSerie)
            ->where('h.position', $posicao)
            ->where('h.id_year_school', session('session_idYearSchool'))
            ->get()->getResult();
        return $result;
    }

    public function getTimePosition(int $day, int $posicao, $shift)
    {

        $result = $this->select(
            '
            pd.id_teacher'
        )
            ->from('tb_school_schedule h')
            ->join('tb_allocation ap', 'h.id_allocation = ap.id')
            ->join('tb_teacher_discipline pd', 'ap.id_teacher_discipline = pd.id')
            ->join('tb_discipline d', 'pd.id_discipline = d.id')
            ->join('tb_teacher p', 'pd.id_teacher = p.id')
            //->where('h.dayWeek', $diaSemana)
            ->where('ap.shift', $shift)
            ->where('h.position', $posicao)
            ->where('h.dayWeek', $day)
            ->where('h.id_year_school', session('session_idYearSchool'))
            ->get()->getResult();
        return $result;
    }

    public function getScheduleByIdAllocation(int $idAllocation)
    {
        return $this->select('s.description, s.classification, s.shift')
            ->join('tb_series s', 's.id = ' . $this->table . '.id_series')
            ->where($this->table . '.id_allocation', $idAllocation)
            //->where('id_year_school', session('session_idYearSchool'))
            ->get()->getRow();
    }

    public function getDisciplineTeacher(int $idSerie)
    {
        return $this->select('td.id_teacher,td.id_discipline')
            ->join('tb_allocation a', $this->table . '.id_allocation = a.id')
            ->join('tb_teacher_discipline td', 'a.id_teacher_discipline = td.id')
            ->where('id_series', $idSerie)
            ->where('a.situation', 'O')
            ->get()->getResult();
    }
    public function getTotalOcupationSerie(int $idSerie)
    {
        return $this->where($this->table.'.id_series', $idSerie)
            ->where($this->table . '.status', 'A')
            ->where($this->table . '.id_year_school', session('session_idYearSchool'))
            //->where('a.situation', 'O')
            ->countAllResults();
    }
    public function getTotalScheduleByDiscipline(int $idDiscipline)
    {
        return $this->join('tb_allocation a', $this->table . '.id_allocation = a.id')
            ->join('tb_teacher_discipline td', 'a.id_teacher_discipline = td.id')
            ->join('tb_discipline d', 'td.id_discipline = d.id')
            ->where('d.id', $idDiscipline)
            ->where($this->table . '.status', 'A')
            ->where($this->table . '.id_year_school', session('session_idYearSchool'))
            ->where('a.situation', 'O')
            ->countAllResults();
    }

    public function getTotalDiscBySerie(int $idSerie)
    {
        return $this->select('count(*) as total, d.description, d.id, d.icone,' . $this->table . '.id_series')
            //->from('tb_school_schedule h')
            ->join('tb_allocation a', $this->table . '.id_allocation = a.id')
            ->join('tb_teacher_discipline td', 'a.id_teacher_discipline = td.id')
            ->join('tb_discipline d', 'td.id_discipline = d.id')
            ->where($this->table . '.id_series', $idSerie)
            ->where($this->table . '.status', 'A')
            ->where($this->table . '.id_year_school', session('session_idYearSchool'))
            ->groupBy('td.id_discipline')
            ->get()->getResult();


        // SELECT count(*) as total, td.description  FROM tb_school_schedule tss
        // JOIN tb_allocation ta ON tss.id_allocation = ta.id
        // JOIN tb_teacher_discipline ttd ON ta.id_teacher_discipline = ttd.id
        // JOIN tb_discipline td ON ttd.id_discipline = td.id 
        // WHERE tss.id_series = 1
        // GROUP BY ttd.id_discipline ; 


    }
    public function geSerieSchedule(int $idSerie)
    {
        return $this->select('d.icone, d.description, d.abbreviation, p.name, td.color, ' . $this->table . '.position, ' . $this->table . '.dayWeek')
            //->from('tb_school_schedule h')
            ->join('tb_allocation a', $this->table . '.id_allocation = a.id')
            ->join('tb_teacher_discipline td', 'a.id_teacher_discipline = td.id')
            ->join('tb_discipline d', 'td.id_discipline = d.id')
            //->join('tb_teacher_discipline pd', 'ap.id_teacher_discipline = pd.id')
            //->join('tb_discipline d', 'pd.id_discipline = d.id')
            ->join('tb_teacher p', 'td.id_teacher = p.id')
            ->where($this->table . '.id_series', $idSerie)
            ->where($this->table . '.status', 'A')
            ->where($this->table . '.id_year_school', session('session_idYearSchool'))
            //->groupBy('td.id_discipline')
            ->get()->getResult();


        // SELECT count(*) as total, td.description  FROM tb_school_schedule tss
        // JOIN tb_allocation ta ON tss.id_allocation = ta.id
        // JOIN tb_teacher_discipline ttd ON ta.id_teacher_discipline = ttd.id
        // JOIN tb_discipline td ON ttd.id_discipline = td.id 
        // WHERE tss.id_series = 1
        // GROUP BY ttd.id_discipline ; 


    }

    public function getDataForDelete(int $id)
    {

        return $this->select('d.abbreviation, d.icone, 
           ' . $this->table . '.id_series, 
           ' . $this->table . '.id, 
           ' . $this->table . '.position,
           ' . $this->table . '.dayWeek,           
           s.shift,
           t.name,
           t.id as id_teacher,
           td.color')
            ->join('tb_allocation a', $this->table . '.id_allocation = a.id')
            ->join('tb_series s', $this->table . '.id_series = s.id')
            ->join('tb_teacher_discipline td', 'a.id_teacher_discipline = td.id')
            ->join('tb_teacher t', 'td.id_teacher = t.id')
            ->join('tb_discipline d', 'td.id_discipline = d.id')
            ->find($id);
    }

    public function deleteScheduleForAllocation(int $idAllocation)
    {
        $this->where('id_allocation',$idAllocation)
             ->delete();   
    }

}
