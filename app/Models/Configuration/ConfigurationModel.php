<?php

namespace App\Models\Configuration;

use CodeIgniter\Model;

class ConfigurationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'tb_config';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields        = ['qtde_dayWeek', 'start_dayWeek', 'end_dayWeek', 'qtde_position','id_year_school','class_time','shift','status'];


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

    public function getConfiguration()
    {
        return $this->where('id_year_school', session('session_idYearSchool'))
                    ->where('status','A')
                    ->get()
                    ->getResultObject();
    }

    public function getConfigurationByIdYear(int $idYear)
    {
        return $this->select('qtde_dayWeek, start_dayWeek, qtde_position, end_dayWeek, class_time, shift')
            ->where('id_year_school',$idYear)
            ->where('status','A')
            ->get()
            ->getResult();

    }
}
