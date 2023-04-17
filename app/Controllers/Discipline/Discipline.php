<?php

namespace App\Controllers\Discipline;

use App\Libraries\Messages;
use App\Models\Discipline\DisciplineModel;
use App\Models\TeacDisc\TeacDiscModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;

class Discipline extends ResourceController
{
    private $disciplineModel;
    private $teacDiscModel;
    //private $validateToken;
    private $messageError;

    public function __construct()
    {
        $this->disciplineModel = new DisciplineModel ();
        $this->teacDiscModel = new TeacDiscModel();
        $this->messageError = new Messages();
        //$this->validateToken = new Services();
    }   
   
    public function show($id = null) {
        try {

            $data = $this->disciplineModel->find($id);

            if ($data != null) {
                return $this->response->setJSON($data);
            }
            return $this->failNotFound();

            //return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
    public function list()
    {
        try {

            $data = $this->disciplineModel->orderBy('description', 'ASC')->findAll();

            foreach ($data as $key => $item) {

                $teacDisc = $this->teacDiscModel->where('id_discipline', $item->id)->get()->getResult();

                if ($teacDisc) {
                    $data[$key]->teacDisc = true;
                }
            }

            return $this->response->setJSON($data);
        } catch (Exception $e) {
            // return $this->response->setJSON([
            //     'response' => 'Erros',
            //     'msg'      => 'Não foi possível executar a operação',
            //     'error'    => $e->getMessage()
            // ]);
            return $this->failServerError($e->getMessage());
        }
    }

    public function edit_verificar(int $id)
    {
        try {

            $data = $this->disciplineModel->find($id);

            // $teacDisc = $this->teacDiscModel->where('id_discipline', $id)->get()->getResult();

            // if($teacDisc) {
            //     $data->teacDisc = true;
            // }

            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => 'Não foi possível executar a operação',
                'error'    => $e->getMessage()
            ]);
        }
    }

    public function create()
    {
        $tokenHeader = $this->request->getHeaderLine('Authorization');     

        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/blog');
        }
        $val = $this->validate(
            [
                'description' => 'required|min_length[3]|is_unique[tb_discipline.description]',
                'amount' => 'required',
                'icone' => 'required',
                'abbreviation' => 'required|min_length[3]|max_length[6]|is_unique[tb_discipline.abbreviation]',

            ],
            [
                'description' => [
                    'required' => 'Preenchimento obrigatório!',
                    'min_length' => 'Mínimo permitido 3 caracteres!',
                    'is_unique' => 'Disciplina já utilizada!'
                ],
                'amount' => [
                    'required' => 'Preenchimento obrigatório!',
                ],
                'icone' => [
                    'required' => 'Preenchimento obrigatório!',
                ],
                'abbreviation' => [
                    'required' => 'Preenchimento obrigatório!',
                    'max_length' => 'Mínimo permitido 6 caracteres!',
                    'min_length' => 'Mínimo permitido 3 caracteres!',
                    'is_unique' => 'Abreviação já utilizada!'
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
        
        // if(!$this->validateToken->validateToken($tokenHeader)){
        //     $response = [
        //         'status' => 'ERROR',
        //         'error' => true,
        //         'code' => 500,
        //         'msg' => '',
        //         //'msgs' => $this->validator->getErrors()
        //     ];

        //     return $this->response->setJSON($response);
            
        // }

        $data['abbreviation'] = mb_strtoupper($this->request->getPost('abbreviation'));
        $data['description'] = mb_strtoupper($this->request->getPost('description'));
        $data['amount'] = mb_strtoupper($this->request->getPost('amount'));
        $data['icone'] = $this->request->getPost('icone');
        try {

            $save = $this->disciplineModel->save($data);
            if ($save) {

                $response = [
                    'status' => 'OK',
                    'error' => false,
                    'code' => 200,
                    'msg' => '<p>Operação realizada com sucesso!</p>',
                    'id' =>  $this->disciplineModel->getInsertID()
                    //'data' => $this->list()
                ];
                return $this->response->setJSON($response);
            }
        } catch (Exception $e) {

            // return $this->response->setJSON([
            //     'response' => 'Erros',
            //     'msg'      => 'Não foi possível executar a operação',
            //     'error'    => $e->getMessage(),
            //     'data'     => $data
            // ]);
            return $this->failServerError($e->getMessage());
        }
    }

    public function update($id = null)
    {
        $tokenHeader = $this->request->getHeaderLine('Authorization');

        // if(!$this->validateToken->validateToken($tokenHeader)){
        //     $response = [
        //         'status' => 'ERROR',
        //         'error' => true,
        //         'code' => 500,
        //         'msg' => '',
        //         //'msgs' => $this->validator->getErrors()
        //     ];

        //     return $this->response->setJSON($response);
            
        // }

        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/blog');
        }
        $val = $this->validate(
            [
                'description' => 'required|min_length[3]',
                'amount' => 'required',
                'abbreviation' => 'required|min_length[3]|max_length[6]',

            ],
            [
                'description' => [
                    'required' => 'Preenchimento obrigatório!',
                    'min_length' => 'Mínimo permitido 3 caracteres!',

                ],
                'amount' => [
                    'required' => 'Preenchimento obrigatório!',
                ],
                'abbreviation' => [
                    'required' => 'Preenchimento obrigatório!',
                    'max_length' => 'Mínimo permitido 6 caracteres!',
                    'min_length' => 'Mínimo permitido 3 caracteres!',

                ],

            ]
        );

        if (!$val) {

            $response = [
                'status' => 'ERROR',
                'error' => true,
                'code' => 400,
                'msg' => $this->messageError,
                'msgs' => $this->validator->getErrors()
            ];
            return $this->fail($response);
            //return $this->response->setJSON($response);
        }

        $data['abbreviation'] = mb_strtoupper($this->request->getPost('abbreviation'));
        $data['description'] = mb_strtoupper($this->request->getPost('description'));
        $data['amount'] = mb_strtoupper($this->request->getPost('amount'));
        $data['id'] = mb_strtoupper($this->request->getPost('id'));
        try {

            $save = $this->disciplineModel->save($data);
            if ($save) {

                $response = [
                    'status' => 'OK',
                    'error' => false,
                    'code' => 200,
                    'msg' => '<p>Operação realizada com sucesso!</p>',
                    'token' => $tokenHeader,
                    //'data' => $this->list()
                ];
                //return $this->response->setJSON($response);
                return $this->respondCreated([
                    'success' => 'success',

                ], 'Operação realizada com sucesso!');
            }
        } catch (Exception $e) {

            // return $this->response->setJSON([
            //     'response' => 'Erros',
            //     'msg'      => 'Não foi possível executar a operação',
            //     'error'    => $e->getMessage(),
            //     'data'     => $data
            // ]);

            return $this->failServerError($e->getMessage());
        }
    }

    public function delete($id = null)
    {
        $id = $this->request->getPost('id');

        try {

            $delete = $this->disciplineModel->where('id', $id)->delete();

            $last = $this->disciplineModel->select('id')->orderBy('id','desc')->limit(1)->get()->getRow();

            if ($delete) {
                $response = [
                    'status' => 'OK',
                    'error' => false,
                    'code' => 200,
                    'msg' => '<p>Operação realizada com sucesso!</p>',
                    'idEnd' =>  $last->id 
                ];
                return $this->response->setJSON($response);
            }
        } catch (Exception $e) {
            // return $this->response->setJSON([
            //     'response' => 'Erros',
            //     'msg'      => '<div class="alert alert-danger alert-close alert-dismissible fade show" role="alert">
            //     <strong> <i class="fa fa-exclamation-triangle"></i>  Ops! </strong>Não foi possível executar a operação! 
            //     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            //     <span aria-hidden="true">&times;</span>
            //     </button>
            // </div>',
            //     'error'    => $e->getMessage(),
            // ]);
            return $this->failServerError($e->getMessage());
        }
    }
}
