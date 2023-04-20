<?php

namespace App\Models\Year;

use App\Models\Allocation\AllocationModel;
use App\Models\Allocation\AloccationModel;
use App\Models\Schedule\ScheduleModel;
use App\Models\Series\SeriesModel;
use App\Models\TeacDisc\TeacDiscModel;
use CodeIgniter\Model;

class YearModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'tb_year_school';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields        = ['description', 'status'];


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

    public function updateYearSchool(array $data)
    {
        $updateAllocation = new AllocationModel();
        $updateSeries = new SeriesModel();
        $updateSchedule = new ScheduleModel();
        $updateTeacDisc = new TeacDiscModel();

        $updateDisabled = $this->set('status', 'I')
            ->whereNotIn('id', $data)
            ->update();

        $updateAllocation->set('status', 'I')
            ->whereNotIn('id_year_school', $data)
            ->update();

        $updateSeries->set('status', 'I')
            ->whereNotIn('id_year_school', $data)
            ->update();

        $updateSchedule->set('status', 'I')
            ->whereNotIn('id_year_school', $data)
            ->update();

        $updateTeacDisc->set('status', 'I')
            ->whereNotIn('id_year_school', $data)
            ->update();


        //ATIVA TODOS OS CAMPOS 
        $updateActive = $this->set('status', 'A')
            ->where('id', $data['id'])
            ->update();

        $updateAllocation->set('status', 'A')
            ->whereIn('id_year_school', $data)
            ->update();

        $updateSeries->set('status', 'A')
            ->whereIn('id_year_school', $data)
            ->update();

        $updateSchedule->set('status', 'A')
            ->whereIn('id_year_school', $data)
            ->update();

        $updateTeacDisc->set('status', 'A')
            ->whereIn('id_year_school', $data)
            ->update();


        if ($updateDisabled && $updateActive) {
            return true;
        }
        return false;
    }

    public function getYearById(int $id)
    {

        return $this->where('id', $id)->find()[0];
    }

    public function disabledStatus()
    {
        $update = $this->set('status', 'I')
            ->where('status', 'A')
            ->update();

        if ($update) {
            return true;
        }
        return false;
    }

    public function getYearActive()
    {
        return $this->select('id')
            ->where('status', 'A')
            ->get()
            ->getResult();
    }

    public function getAll()
    {
        return $this->orderBy('description', 'DESC')
            ->get()
            ->getResult();
    }
}
