<?php

namespace App\Controllers\TeacDisc;

use App\Libraries\Messages;
use App\Models\Allocation\AlloccationModel;
use App\Models\Discipline\DisciplineModel;
use App\Models\TeacDisc\TeacDiscModel;
use App\Models\Teacher\TeacherModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;

class TeacDisc extends ResourceController
{
    private $teacDiscModel;
    public $erros = '';
    private $teacherModel;
    private $disciplineModel;
    private $allocationModel;
    private $messageError; 
    public function __construct()
    {
        $this->teacDiscModel = new TeacDiscModel();
        $this->teacherModel = new TeacherModel();
        $this->disciplineModel = new DisciplineModel();
        $this->allocationModel = new AlloccationModel();
        $this->messageError = new Messages();
        helper('utils');
    }
    public function create()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/blog');
        }
        $val = $this->validate(
            [
                'id_teacher' => 'required',
                'amount' => 'required|numeric',
                'color' => 'required|is_unique[tb_teacher_discipline.color]',
                'disciplinesTeacher' => 'required',
            ],
            [
                'id_teacher' => [
                    'required' => 'Preenchimento obrigatório!',
                   
                ],
                'amount' => [
                    'required' => 'Preenchimento obrigatório!',
                    'numeric' => ' Apenas número!'
                ],
                'color' => [
                    'required' => 'Preenchimento obrigatório!',
                    'is_unique' => 'Cor utilizada por outro (a) professor (a)!',
                ],
                'disciplinesTeacher' => [
                    'required' => 'Escolha uma opção!',                    
                ],
            ]
        );

        if (!$val) {

            $response = [
                'status' => 'ERROR',
                'error' => true,
                'code' => 400,
                'msg' => '<div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                <span class="alert-text"><strong>Ops! </strong>Erro(s) no preenchimento do formulário! </span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>',
                'msgs' => $this->validator->getErrors()
            ];

            return $this->response->setJSON($response);
        }

       

        $teacher['id_teacher'] = mb_strtoupper($this->request->getPost('id_teacher'));
        $teacher['amount'] = $this->request->getPost('amount');
        $teacher['color'] = $this->request->getPost('color') == '#000000' ? generationColor() : $this->request->getPost('color') ;
        $teacher['disciplines'] = $this->request->getPost('disciplinesTeacher');
        $teacher['status'] = 'A';       
        //$data['status'] = 'A';

        // if ($data['description'] > getenv('YEAR.END')) {
        //     return redirect()->back()->withInput()->with('error', 'Ano não permitido!');
        // }
        
        try{
            $save = $this->teacDiscModel->saveTeacherDiscipline($teacher);

            if ($save) {
                $response = [
                    'status' => 'OK',
                    'error' => false,
                    'code' => 200,
                    'msg' => '<p>Operação realizada com sucesso!</p>',
                    //'data' => $this->list()
                ];
                return $this->response->setJSON($response);
        }
        }catch (Exception $e) {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'error' => true,
                'code' => $e->getCode(),
                'msg' => '<div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                <span class="alert-text"><strong>Ops! </strong>Erro(s) no preenchimento do formulário! </span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>', 
                'msgs' => [
                    'disciplinesTeacher' => 'Disciplina já cadastrada!'  
                ]            
            ]);          
        }
        //return $this->response->setJSON($response);
    }

    public function show($id = null)
    {

        $data = $this->teacDiscModel->getByIdTeacherDiscipline($id);

        $amountAllocation = $this->allocationModel->getCountByIdTeacDisc($id);
        $data[0]->amount_allocation = $amountAllocation;
        //dd($data);
        return $this->response->setJSON($data);
    }
    public function delete_remover($id = null)
    {
        // testar se a exite alocação para esta teacDisc

        $data = $this->teacDiscModel->getByIdTeacherDiscipline($id);
        // dd($id);
        return $this->response->setJSON($data);
    }

    public function delete($id = null)
    {
        $id = $this->request->getPost('id');

       
        $delete = $this->teacDiscModel->where('id', $id)
            ->delete();

        if ($delete) {
            $response = [
                'status' => 'OK',
                'error' => false,
                'code' => 200,
                'msg' => '<p>Operação realizada com sucesso!</p>'
            ];
            return $this->response->setJSON($response);
        }
        
        $response = [
            'status' => 'ERROR',
            'error' => true,
            'code' => 400,
            'msg' => 'Erro, não foi possível realizar a operação!'
        ];
        return $this->response->setJSON($response);
    }

    public function update($id = null)
    {

        $msgs = [
            'message' => '',
            'alert' => ''
        ];
        if (session()->has('erro')) {
            $this->erros = session('erro');
            $msgs = $this->messageError->getMessageError();
        }

        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/blog');
        }

        $val = $this->validate(
            [
                'nNumeroAulas' => 'required|numeric',
                'nCorDestaque' => 'required',

            ],
            [
                'nNumeroAulas' => [
                    'required' => 'Preenchimento obrigatório!',
                    'numeric' => ' Apenas número!',
                ],
                'nCorDestaque' => [
                    'required' => 'Preenchimento obrigatório!',
                ],
            ]
        );


        if (!$val) {
            //return redirect()->back()->withInput()->with('erro', $this->validator);
            $response = [
                'status' => 'ERROR',
                'error' => true,
                'code' => 400,
                'msg' => '<div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                <span class="alert-text"><strong>Ops! </strong>Erro(s) no preenchimento do formulário! </span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>',
            'msgs' => $this->validator->getErrors()
            ];

            return $this->response->setJSON($response);
            //return redirect()->to('/admin/blog');
        } else {

            $color = $this->request->getPost('nCorDestaque');
            $amount = $this->request->getPost('nNumeroAulas');
            $idTeacher = $this->request->getPost('id_teacher');
            $id = $this->request->getPost('id');

            $update = $this->teacDiscModel->set(['color' => $color, 'amount' => $amount])
                ->where('id', $id)
                ->update();

            if ($update) {
                $totalAllocationOcupation = $this->allocationModel->getCountByIdTeacDiscOcupation($id);
                
                if($amount > $totalAllocationOcupation) {
                
                    $this->allocationModel->set('situation', 'L')
                    ->where('id_teacher_discipline', $id)
                    ->where('situation', 'B')
                    ->update();                        
                }

                $response = [
                    'status' => 'OK',
                    'error' => false,
                    'code' => 200,
                    'msg' => '<p>Operação realizada com sucesso!</p>'
                ];
                return $this->response->setJSON($response);
            }

            $response = [
                'status' => 'ERROR',
                'error' => true,
                'code' => 400,
                'msg' => 'Erro, não foi possível realizar a operação!'
            ];
            return $this->response->setJSON($response);
        }
    }

}
