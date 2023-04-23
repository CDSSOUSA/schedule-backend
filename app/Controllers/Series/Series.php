<?php

namespace App\Controllers\Series;

use App\Libraries\Messages;
use App\Models\Schedule\ScheduleModel;
use App\Models\Series\SeriesModel;
use App\Models\Year\YearModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;
use FilesystemIterator;

class Series extends ResourceController
{
    private $series;
    private $schedule;
    private $messageError;

    private $year;


    public function __construct()
    {
        $this->series = new SeriesModel();
        $this->schedule = new ScheduleModel();
        $this->messageError = new Messages();
        $this->year = new YearModel();
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
                ->where('status', 'A')
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

            $data = $this->series->where('shift', $shift)
                ->where('status', 'A')
                ->orderBy('shift ASC, description ASC,classification ASC')
                ->findAll();

            foreach ($data as $item) {

                $total = $this->schedule->getTotalOcupationSerie($item->id);

                $datas[] = [
                    'id' => $item->id,
                    'description' => $item->description,
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

    public function send()
    {
        $serie = $this->request->getPost('serie');

        $idSerie = $this->request->getPost('id');

        $dirSeries = str_replace(['º', 'MANHÃ', ' '], ['-', 'MANHA', ''], $serie);

        $dirYear = $this->year->getYearActive()[0]->description;

        $urlArchive = '././assets/docs/' . $dirYear . '/' . $dirSeries;

        //dd($urlArchive);
        if (!is_dir($urlArchive)) {
            $h = mkdir($urlArchive, 0777, true);
            chmod($urlArchive, 0777);
        }

        chdir($urlArchive);

        //touch('jose.pdf');
        // // atençao
        // gerar o pdf na pasta da serie
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://localhost/schedule-report/public/report/series/' . $idSerie);
        curl_exec($curl);
        var_dump(curl_getinfo($curl));
        curl_close($curl);

        // depois buscalo na pasta e envir

   

        $arch = new FilesystemIterator('/var/www/html/schedule-backend/public/assets/docs/' . $dirYear . '/' . $dirSeries);
        $nameFile = '';

        foreach ($arch as $file) {
            $nameFile = $file->getFilename();
        }

        var_dump($nameFile);


        $val = $this->validate(
            [
                'description' => 'required',
            ],
            [
                'description' => [
                    'required' => 'Preenchimento obrigatório!',
                ]
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


        $email = \Config\Services::email();

        $config = [
            'protocol' => 'smtp',
            'SMTPHost' => 'smtp.gmail.com',
            'SMTPUser' => getenv('LOGIN.EMAIL'),
            'SMTPPass' => getenv('PASSWORD.EMAIL'),
            'SMTPPort' => '587',
            'mailType' => 'html'
        ];
        $email->initialize($config);

        $email->setFrom('sistema@gmail.com', 'Sistema Gerenciador de Horário Escolar');
        $email->setTo($this->request->getPost('description'));

        $template = view('template/email/series', ['nameTeacher'=>'Arnaldo Augusto Nora Antunes Filho','series'=> $serie.' - '. $dirYear]);

        //$serie = $this->request->getPost('id');

        $email->setSubject('Quadro de horário!');
        //$email->setMessage('Olá professor, segue o horário da turma :: ' . $serie.' - '. $dirYear);
        $email->setMessage($template);

        $email->attach(base_url() . '/assets/docs/' . $dirYear . '/' . $dirSeries . '/'.$nameFile, 'attachment', $nameFile);
        $sent = $email->send();
        //$sent = true;
        if ($sent) {
            $response = [
                'status' => 'OK',
                'error' => false,
                'code' => 200,
                'msg' => '<p>Operação realizada com sucesso!</p>',
                //'data' => $this->list()
            ];
            return $this->response->setJSON($response);
        } else {
            $response = [
                'status' => 'ERROR',
                'error' => true,
                'code' => 500,
                //'msg' => $this->messageError->getMessageError(),
                'msgs' => 'Erro no envio de email '
            ];

            return $this->fail($response);
            //throw new Exception($email->printDebugger()); 
        }
    }
}
