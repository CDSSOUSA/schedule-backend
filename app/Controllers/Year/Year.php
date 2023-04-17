<?php

namespace App\Controllers\Year;

use App\Libraries\Messages;
use App\Models\Configuration\ConfigurationModel;
use App\Models\Series\SeriesModel;
use App\Models\TeacDisc\TeacDiscModel;
use App\Models\Year\YearModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;

class Year extends ResourceController
{
    private $yearModel;
    private $seriesModel;
    private $configurationModel;
    private $teacDiscModel;
    private $messageError;
    public function __construct()
    {
        $this->yearModel = new YearModel();
        $this->seriesModel = new SeriesModel();
        $this->configurationModel = new ConfigurationModel();
        $this->teacDiscModel = new TeacDiscModel();
        $this->messageError = new Messages();
    }

    public function create()
    {

        if ($this->request->getMethod() !== 'post') {
            throw new Exception("Internal Server Error");
        }

        $val = $this->validate(
            [
                'description' => 'required|is_unique[tb_year_school.description]',

            ],
            [
                'description' => [
                    'required' => 'Preenchimento obrigatório!',
                    'is_unique' => 'Ano já cadastrado!'
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

        $data = $this->request->getPost();
        $data['status'] = 'A';

        try {

            // $this->yearSchool->transStart();
            // $update = $this->yearSchool->disabledStatus();
            // $save = $this->yearSchool->save($data);
            // $this->yearSchool->transComplete();


            // if ($this->request->getPost('series') == 'S') {

            //     $this->yearSchool->transStart();

            //     $seriesMigration = $this->series->getSeriesByIdYear($data['id_active']);

            //     $this->series->set('status', 'I')
            //         ->where('id_year_school', $data['id_active'])
            //         ->update();

            //     $this->yearSchool->transComplete();


            //     foreach ($seriesMigration as $item) {

            //         $dataSerie['description'] = $item->description;
            //         $dataSerie['classification'] = $item->classification;
            //         $dataSerie['shift'] = $item->shift;
            //         $dataSerie['id_year_school'] = $this->yearSchool->getInsertID();
            //         $dataSerie['status'] = 'A';

            //         $this->series->save($dataSerie);
            //     }

            //     $data['series'] = $seriesMigration;
            // }

            // if ($this->request->getPost('configuration') == 'S') {

            //     $this->yearSchool->transStart();
            //     $configurationMigration = $this->configuration->getConfigurationByIdYear($data['id_active']);

            //     $this->configuration->set('status', 'I')
            //         ->where('id_year_school', $data['id_active'])
            //         ->update();
            //     $this->yearSchool->transComplete();

            //     foreach ($configurationMigration as $item) {

            //         $dataConfiguration['qtde_dayWeek'] = $item->qtde_dayWeek;
            //         $dataConfiguration['start_dayWeek'] = $item->start_dayWeek;
            //         $dataConfiguration['end_dayWeek'] = $item->end_dayWeek;
            //         $dataConfiguration['qtde_position'] = $item->qtde_position;
            //         $dataConfiguration['class_time'] = $item->class_time;
            //         $dataConfiguration['shift'] = $item->shift;
            //         $dataConfiguration['id_year_school'] = $this->yearSchool->getInsertID();
            //         $dataConfiguration['status'] = 'A';

            //         $this->configuration->save($dataConfiguration);
            //     }
            //     $data['configuration'] = $configurationMigration;
            // }

            // if ($this->request->getPost('teacDisc') == 'S') {

            //     $this->yearSchool->transStart();
            //     $teacDiscMigration = $this->teacDisc->getTeacDiscByIdYear($data['id_active']);

            //     $this->teacDisc->set('status', 'I')
            //         ->where('id_year_school', $data['id_active'])
            //         ->update();
            //     $this->yearSchool->transComplete();
            //     foreach ($teacDiscMigration as $item) {

            //         $dataTeacDisc['id_teacher'] = $item->id_teacher;
            //         $dataTeacDisc['id_discipline'] = $item->id_discipline;
            //         $dataTeacDisc['amount'] = $item->amount;
            //         $dataTeacDisc['color'] = $item->color;
            //         $dataTeacDisc['id_year_school'] = $this->yearSchool->getInsertID();
            //         $dataTeacDisc['status'] = 'A';

            //         $this->teacDisc->save($dataTeacDisc);
            //     }

            //     $data['teacDisc'] = $teacDiscMigration;
            // }

            $save = true;
            $update = true;

            if ($save && $update) {
                // $response = [
                //     'status' => 'OK',
                //     'error' => false,
                //     'code' => 200,
                //     'msg' => '<p>Operação realizada com sucesso!</p>',
                //     'data' => $data
                // ];
                return $this->respondCreated([
                    'success' => 'success',

                ], 'Operação realizada com sucesso!');
                //return $this->response->setJSON($response);
            }
        } catch (Exception $e) {
            return $this->failServerError('Ocorreu um erro inesperado, tente novamente!', $e->getCode(), $e->getMessage());
        }
        return $this->failServerError('Nenhum registro encontrado!');
    }

    public function active()
    {
        try {
            if ($this->request->getMethod() !== 'post') {
                return redirect()->to('/admin/blog');
            }


            $data = $this->request->getPost();

            $update = $this->yearModel->updateYearSchool($data);
            //$update = true;

            if ($update) {
                $response = [
                    'status' => 'OK',
                    'error' => false,
                    'code' => 200,
                    'msg' => '<p>Operação realizada com sucesso!</p>',
                    //'data' => $this->list()
                ];
                return $this->response->setJSON($response);
            }
        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => 'Não foi possível executar a operação',
                'error'    => $e->getMessage()
            ]);
        }
    }

    public function list()
    {
        try {

            $data = $this->yearModel->getAll();

            if ($data != null) {

                return $this->response->setJSON($data);
            }

            return $this->failNotFound();
        } catch (Exception $e) {

            return $this->failServerError($e->getMessage());
        }
    }

    public function getYearActive()
    {
        try {
            $data = $this->yearModel->getYearActive();

            if ($data != null) {
                return $this->response->setJSON($data);
            }
            return $this->failNotFound();
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
       
    }

    public function show($id = null)
    {         

        try {
            $data = $this->yearModel->find($id);  


            if ($data != null) {
                return $this->response->setJSON($data);
            }
            return $this->failNotFound();
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
        
    }

    public function update($id = null)
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/blog');
        }

        $val = $this->validate(
            [
                'description' => 'required|is_unique[tb_year_school.description,id,{id}]',

            ],
            [
                'description' => [
                    'required' => 'Preenchimento obrigatório!',
                    'is_unique' => 'Ano já cadastrado!'
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

            //return $this->response->setJSON($response);
            return $this->fail($response);
        }

        try {

            $data = $this->request->getPost();          

            $save = $this->yearModel->save($data);

            if ($save) {
               
                return $this->respondCreated([
                    'success' => 'success',

                ], 'Operação realizada com sucesso!');
                
            }
        } catch (Exception $e) {
            return $this->failServerError('Ocorreu um erro inesperado, tente novamente!', $e->getCode(), $e->getMessage());
        }
        return $this->failServerError('Nenhum registro encontrado!');
    }
}
