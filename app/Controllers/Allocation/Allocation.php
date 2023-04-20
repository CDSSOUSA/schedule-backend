<?php

namespace App\Controllers\Allocation;

use App\Libraries\Messages;
use App\Models\Allocation\AllocationModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;

class Allocation extends ResourceController
{
    private $allocationModel;
    private $messageError;
    public function __construct()
    {
        $this->allocationModel = new AllocationModel();
        $this->messageError = new Messages();
       
    }
    public function create()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/blog');
        }
        $val = $this->validate(
            [
                'id_teacher' => 'required',
                'disciplinesTeacher' => 'required',
                // 'nPosition' => 'required',
                'nDayWeek' => 'required',
                'nShift' => 'required',
            ],
            [
                'id_teacher' => [
                    'required' => 'Preenchimento obrigatório!',
                ],
                // 'nPosition' => [
                //     'required' => 'Preenchimento obrigatório!',
                // ],
                'nDayWeek' => [
                    'required' => 'Preenchimento obrigatório!',

                ],
                'nShift' => [
                    'required' => 'Preenchimento obrigatório!',

                ],
                'disciplinesTeacher' => [
                    'required' => 'Preenchimento obrigatório!',
                ],
            ]
        );

        if (!$val) {
           
            $response = [
                'status' => 'ERROR',
                'error' => true,
                'code' => 400,
                'msg' => $this->messageError->getMessageError(),
                'msgs' => $this->validator->getErrors()
            ];

            return $this->fail($response);
        }

        $idTeacher = $this->request->getPost('id_teacher');
        $data['dayWeek'] = $this->request->getPost('nDayWeek[]');
        $data['disciplines'] = $this->request->getPost('disciplinesTeacher');
        // $data['position'] = $this->request->getPost('nPosition[]');
        $data['shift'] = $this->request->getPost('nShift[]');

        

        
        
        // if ($data['description'] > getenv('YEAR.END')) {
            //     return redirect()->back()->withInput()->with('error', 'Ano não permitido!');
            // }
            
            try {
                
                $save = $this->allocationModel->saveAllocation($data);

            if ($save) {
                // $response = [
                //     'status' => 'OK',
                //     'error' => false,
                //     'code' => 200,
                //     'msg' => '<p>Operação realizada com sucesso!</p>',
                //     //'data' => $this->list()
                // ];
                // return $this->response->setJSON($response);
                return $this->respondCreated([
                    'success' => 'success',

                ], 'Operação realizada com sucesso!');

            } else {
                return $this->response->setJSON([
                    'status' => 'ERROR',
                    'error' => true,
                    'code' => '',
                    'msg' => $this->messageError->getMessageErrorAvailability(),
               
                ]);
            }
        } catch (Exception $e) {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'error' => true,
                'code' => $e->getCode(),
                'msg' => '<div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                <span class="alert-text"><strong>Ops! </strong>Erro(s) no preenchimento do formulário! catc </span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>',
                'msgs' => $e->getMessage()
            ]);
        }
        //return $this->response->setJSON($response);
    }

    public function showTeacherOcupation(int $id)
    {
        try {

            $data = $this->allocationModel->getAllocationTeacherOcupation($id);  
            // atencao apra o metodo  getAllocationTeacher        

            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }

    }
    public function showTeacherChecked(int $id)
    {
        try {

            $data = $this->allocationModel->getAllocationTeacher($id);  
            // atencao apra o metodo  getAllocationTeacher        

            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }

    }
    public function getTotalAllocationTeacher(int $id)
    {
        try {

            $data = $this->allocationModel->getTotalAllocationTeacherAll($id);  
            // atencao apra o metodo  getAllocationTeacher        

            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }

    }
    public function show($id = null)
    {
        try {

            $data = $this->allocationModel->getTeacherByIdAllocation($id);            

            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }

    }

    public function delete($id = null)
    {
        //$idAlocacao = $this->request->getPost('id_teacher"');
        $data['id'] = $this->request->getPost('id_teacher');
        $data['nIdsAllocation'] = $this->request->getPost('nIdsAllocation[]');

        $allocationProtected = [];

        if($data['nIdsAllocation'] ) {
            
            foreach ($data['nIdsAllocation'] as $item){
                array_push($allocationProtected, $item);
            }
        }

        try {

            $allocationFree = $this->allocationModel->getAllocationTeacherFree($data['id']);

            foreach($allocationFree as $idAllocation ) {

                if(!in_array($idAllocation->id,$allocationProtected)){
                    
                    $delete = $this->allocationModel->where('id', $idAllocation->id)->delete();
                }

            }

            if ($delete) {
                $response = [
                    'status' => 'OK',
                    'error' => false,
                    'code' => 200,
                    'msg' => '<p>Operação realizada com sucesso!</p>',
                    //'data' => $this->list()
                ];
                return $this->response->setJSON($data);
            }     

            
        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => '<div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                <span class="alert-text"><strong>Ops! </strong> Precisa desmarcar pelo menos uma opção!!</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>',
                'error'    => $e->getMessage()
            ]);
        }
       

    }
}
