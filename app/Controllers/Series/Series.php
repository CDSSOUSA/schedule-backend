<?php

namespace App\Controllers\Series;

use App\Libraries\Messages;
use App\Models\Schedule\ScheduleModel;
use App\Models\Series\SeriesModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;

class Series extends ResourceController
{
    private $series;
    private $schedule;
    private $messageError;


    public function __construct()
    {
        $this->series = new SeriesModel();
        $this->schedule = new ScheduleModel();
        $this->messageError = new Messages();
    }
    public function show($id = null)
    {
        try {

            $data = $this->series->getDescription($id);
            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
    public function listSeries()
    {
        try {

            $data = $this->series->orderBy('shift ASC, description ASC ,classification ASC')
                                 //->where('status','A')
                                 ->get()
                                 ->getResult();
                                 
            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
    public function list()
    {
        try {

            $data = $this->series->orderBy('shift ASC, description ASC ,classification ASC')
                                 ->where('status','A')
                                 ->get()
                                 ->getResult();
                                 
            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
    public function listSeriesByShift(string $shift)
    {
        $datas = [];
        try {

            $data = $this->series->where('shift',$shift)
                                ->where('status','A')
                                ->orderBy('shift ASC, description ASC,classification ASC')
                                ->findAll();

            foreach($data as $item) {

                $total = $this->schedule->getTotalOcupationSerie($item->id);

                $datas [] = [
                    'id' => $item->id,
                    'description' =>$item->description,
                    'classification' => $item->classification,
                    'total' => $total,
                    'shift' => $item->shift
                ];
                
            }

            return $this->response->setJSON($datas);
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    public function active()
    {
        try {
            if ($this->request->getMethod() !== 'post') {
                return redirect()->to('/admin/blog');
            }


            $data = $this->request->getPost();

            $update = $this->series->updateSeries($data);
            //$update = true;

            if ($update) {
                $response = [
                    'status' => 'OK',
                    'error' => false,
                    'code' => 200,
                    'msg' => '<p>Operação realizada com sucesso!</p>',
                    'idEnd' => $this->series->getEndSerie(),
                    //'data' => $this->list()
                ];
                return $this->response->setJSON($response);
            }
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    // public function list()
    // {
    //     $msgs = [
    //         'message' => '',
    //         'alert' => ''
    //     ];
    //     if (session()->has('erro')) {
    //         $this->erros = session('erro');
    //         $msgs = $this->messageErro;
    //     }
    //     if (session()->has('error')) {
    //         $this->error = session('error');
    //         $msgs = $this->messageErro;
    //     }
    //     if (session()->has('success')) {
    //         $msgs = $this->messageSuccess;
    //     }
    //     $newJs = [
    //         base_url() . "/assets/js/series.js",
    //     ];
    //     $js = array_merge($this->javascript, $newJs);
    //     $data = array(
    //         'title' => '<i class="fa fa-calendar-check"></i> Listar Séries :: ',
    //         'breadcrumb' => [
    //             '<li class="breadcrumb-item">' . anchor('/', 'Home') . '</li>',
    //             '<li class="breadcrumb-item active"> Listar </li>',
    //         ],
    //         'msgs' => $msgs,
    //         'erro' => $this->erros,
    //         'error' => $this->error,
    //         'css' => $this->style,
    //         'js' => $js,

    //     );
    //     return view('series/list', $data);
    // }

    public function create()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/blog');
        }
        $val = $this->validate(
            [
                'description' => 'required|max_length[1]|integer',
                'classification' => 'required|alpha',
                'shift' => 'required',

            ],
            [
                'description' => [
                    'required' => 'Preenchimento Obrigatório!',
                    'max_length' => 'Apenas um caracter!',
                    'integer' => 'Apenas número inteiro!'
                ],
                'classification' => [
                    'required' => 'Preenchimento Obrigatório!',
                    'alpha' => 'Apenas letras!',
                ],
                'shift' => [
                    'required' => 'Preenchimento Obrigatório!',
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

            return $this->response->setJSON($response);
        }

        $data = $this->request->getPost();
        $data['classification'] = mb_strtoupper($this->request->getPost('classification'));
        //$data['status'] = 'A';

        // if ($data['description'] > getenv('YEAR.END')) {
        //     return redirect()->back()->withInput()->with('error', 'Ano não permitido!');
        // }
        try {
            $save = $this->series->save($data);

            if ($save) {
                $response = [
                    'status' => 'OK',
                    'error' => false,
                    'code' => 200,
                    'msg' => '<p>Operação realizada com sucesso!</p>',
                    'id' =>  $this->series->getInsertID()
                    //'data' => $this->list()
                ];
                return $this->response->setJSON($response);
            }
        } catch (Exception $e) {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'error' => true,
                'code' => $e->getCode(),
                'msg' => $this->messageError,
                'msgs' => [
                    'series' => 'Série, turma e turno já cadastrados!'
                ]
            ]);
        }
    }
    public function update($id = null)
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/blog');
        }
        $val = $this->validate(
            [
                'description' => 'required|max_length[1]|integer',
                'classification' => 'required|alpha',
                'shift' => 'required',

            ],
            [
                'description' => [
                    'required' => 'Preenchimento Obrigatório!',
                    'max_length' => 'Apenas um caracter!',
                    'integer' => 'Apenas número inteiro!'
                ],
                'classification' => [
                    'required' => 'Preenchimento Obrigatório!',
                    'alpha' => 'Apenas letras!',
                ],
                'shift' => [
                    'required' => 'Preenchimento Obrigatório!',
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

            return $this->response->setJSON($response);
        }

        $data = $this->request->getPost();
        $data['classification'] = mb_strtoupper($this->request->getPost('classification'));
        //$data['status'] = 'A';

        // if ($data['description'] > getenv('YEAR.END')) {
        //     return redirect()->back()->withInput()->with('error', 'Ano não permitido!');
        // }
        try {
            $save = $this->series->save($data);

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
        } catch (Exception $e) {
            return $this->response->setJSON([
                'status' => 'ERROR',
                'error' => true,
                'code' => $e->getCode(),
                'msg' => $this->messageError,
                'msgs' => [
                    'series' => 'Série, turma e turno já cadastrados!'
                ]
            ]);
        }
    }
}
