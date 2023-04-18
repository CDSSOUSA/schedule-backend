<?php

namespace App\Controllers\Schedule;


use App\Models\Allocation\AlloccationModel;
use App\Models\Discipline\DisciplineModel;
use App\Models\Schedule\ScheduleModel;
use App\Models\Series\SeriesModel;
use App\Models\TeacDisc\TeacDiscModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;

class Schedule extends ResourceController
{
    private $allocationModel;
    private $scheduleModel;
    private $teacDiscModel;
    private $disciplineModel;
    private $seriesModel;
    public function __construct()
    {
        $this->allocationModel = new AlloccationModel();
        $this->scheduleModel = new ScheduleModel();
        $this->teacDiscModel = new TeacDiscModel();
        $this->disciplineModel = new DisciplineModel();
        $this->seriesModel = new SeriesModel();
    }

    public function listDPS($idSerie, $dw, $ps, $shift)
    {
        try {
            //define array limites
            $limits = [];
            // buscou as series
            $dataSerie = $this->scheduleModel->getTotalDiscBySerie($idSerie);

            $horario = $this->scheduleModel->getTimeDayWeek($dw, $idSerie, $ps);

            // if(!empty($horario)) {

            //     $id_teacher = [$horario['id_teacher']];
            // } else {
            //     $id_teacher = ['0'];
            // }
            // if exitir
            if ($dataSerie != null) {
                //
                foreach ($dataSerie as $d) {
                    //busca o limite por serie da disciplina
                    $limit = $this->disciplineModel->getLimitClassroom($d->id);
                    //if total limite da disciplina for menor igual a total de alocacao na 
                    // $disciplineTeacher = $this->scheduleModel->getDisciplineTeacher($d->id_series);

                    // $dis = [];
                    // $tea = [];
                    // if($disciplineTeacher != null){

                    //     foreach ($disciplineTeacher as $is) {    
                    //         $dis[] = $is->id_discipline;
                    //         $tea[] = $is->id_teacher;
                    //     }
                    // }

                    if ($limit->amount <= $d->total) {
                        $limits[] = $d->id;
                    }
                }
                if ($limits != null) {
                    //$allocationDisponivel = $this->allocationModel->getAllocationByDayWeek($idSerie, $dw, $ps, $shift, $limits,$dis,$tea);
                    $allocationDisponivel = $this->allocationModel->getAllocationByDayWeek($idSerie, $dw, $ps, $shift, $limits);
                }
                // else if($disciplineTeacher != null){

                //     $data = $this->allocationModel->getAllocationByDayWeekABC($idSerie, $dw, $ps, $shift, $limits, $dis, $tea);


                // }
                else {

                    $allocationDisponivel = $this->allocationModel->getAllocationByDayWeekA($idSerie, $dw, $ps, $shift);
                }
            } else {

                $allocationDisponivel = $this->allocationModel->getAllocationByDayWeekA($idSerie, $dw, $ps, $shift);
            }

            $horario2 = $this->scheduleModel->getTimePosition($dw, $ps, $shift);

            $allocationDisponivel2 = 'vago';
            if ($horario2 != null) {
                foreach ($horario2 as $h) {
                    $hor[] = $h->id_teacher;
                }

                $allocationDisponivel2 = $this->allocationModel->getAllocationByDayWeekAB($idSerie, $dw, $ps, $shift, $hor, $qual = null);

                //dd($allocationDisponivel2);
            }



            //  if($horario2['id_teacher']) {

            //      $allocationDisponivel = $this->allocationModel->getAllocationByDayWeekAB($idSerie, $dw, $ps, $shift, $horario2);
            //  }  



            $data = 'ocupada';

            if ($allocationDisponivel != null && empty($horario) && !empty($allocationDisponivel2)) {
                $data = 'livre';
            } else if (empty($horario)) {
                $data = 'vago';
            } else {
                $data = $horario;
            }

            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => 'Não foi possível executar a operação',
                'error'    => $e->getMessage()
            ]);
        }
    }
    public function list(string $shift)
    {
        try {


            $dataSerie = $this->seriesModel->getSeries($shift);



            // foreach ($data as $key => $item) {

            //     $teacDisc = $this->teacDiscModel->where('id_discipline', $item->id)->get()->getResult();

            //     if ($teacDisc) {
            //         $data[$key]->teacDisc = true;
            //     }
            // }

            return $this->response->setJSON($dataSerie);
        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => 'Não foi possível executar a operação',
                'error'    => $e->getMessage()
            ]);
        }
    }
    public function listSeries(int $idSerie)
    {
        try {

            //$data = $this->scheduleModel->getTimeDayWeek($dw, $idSerie, $ps);

            $data = $this->scheduleModel->geSerieSchedule($idSerie);

            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => 'Não foi possível executar a operação',
                'error'    => $e->getMessage()
            ]);
        }
    }

    public function getAllocation(int $idSerie, int $dayWeek, int $position, string $shift)
    {
        try {
            $datas = $this->scheduleModel->getTotalDiscBySerie($idSerie);
            $ar = 0;
            $limits = [];

            $dis = [];
            $tea = [];
            $hor = [];
            if ($datas != null) {

                foreach ($datas as $d) {

                    $limit = $this->disciplineModel->getLimitClassroom($d->id);
                    $disciplineTeacherOcupados = $this->scheduleModel->getDisciplineTeacher($d->id_series);

                    $profPermitida = [];
                    // if($disciplineTeacher != null) {
                    $disciplina = new DisciplineModel();
                    $disci = $disciplina->findAll();

                    foreach ($disciplineTeacherOcupados as $is) {

                        $dis[] = $is->id_discipline;
                        $tea[] = $is->id_teacher;
                        //$tea[] = $is->id_teacher;                      

                    }

                    // busca professores de outras disciplinas
                    $profDisciplinaPermitida = $this->allocationModel->getAllocationByDayWeek($idSerie, $dayWeek, $position, $shift, $dis,);

                    foreach ($profDisciplinaPermitida as $itenPermitido) {

                        $tea[] = $itenPermitido['id_teacher'];
                    }

                    // foreach ($disci as $discipl) {

                    //     if (in_array($discipl->id, $dis)) {

                    //         $profPermitida[] = $tea;                               

                    //     }
                    //     break;

                    // }       


                    //var_dump($tea);

                    //exit();
                    //}

                    if ($limit->amount <= $d->total) {
                        $limits[] = $d->id;
                    }
                }

                // if($tea != null) {
                //     $data = $this->allocationModel->getAllocationByDayWeekABCDE($idSerie, $dayWeek, $position, $shift, $tea);
                // } else 
                if ($limits != null) {
                    //var_dump('aqui no limits');
                    if ($tea != null) {
                        //var_dump('aqui no tea');
                        $data = $this->allocationModel->getAllocationByDayWeek($idSerie, $dayWeek, $position, $shift, $limits);
                    } else {
                        $data = $this->allocationModel->getAllocationByDayWeekABCDE($idSerie, $dayWeek, $position, $shift, $tea);
                    }
                }
                // else if($disciplineTeacher != null){

                //     $data = $this->allocationModel->getAllocationByDayWeekABC($idSerie, $dayWeek, $position, $shift, $limits, $dis, $tea);


                // }
                else {
                    //var_dump('aqui sem limite');
                    $horario = $this->scheduleModel->getTimePosition($dayWeek, $position, $shift);
                    //dd($horario);
                    if ($horario) {

                        foreach ($horario as $h) {
                            $hor[] = $h->id_teacher;
                        }
                        $ar = array_merge($tea, $hor);
                        //$hor[] = $tea;
                        //var_dump($hor);
                        //exit();
                        //var_dump('tem horario');

                        $data = $this->allocationModel->getAllocationByDayWeekAB($idSerie, $dayWeek, $position, $shift, $hor, $qual = null);
                    } else {
                        //var_dump('sem horario');
                        //$data = $this->allocationModel->getAllocationByDayWeekA($idSerie, $dayWeek, $position, $shift);
                        $data = $this->allocationModel->getAllocationByDayWeekABCDE($idSerie, $dayWeek, $position, $shift, $tea);
                    }
                }
            } else {

                $data = $this->allocationModel->getAllocationByDayWeekA($idSerie, $dayWeek, $position, $shift);
            }



            if ($data != null) {
                return $this->response->setJSON($data);
            } else {
                $data = [[
                    'id' => "0",
                    'name' => "SEM PROFESSOR",
                    'abbreviation' => "SH",
                    'color' => "#000000",
                    'id_teacher' => "0"
                ]];
                //$data = 'vago';
                return $this->response->setJSON($data);
            }
        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => 'Não foi possível executar a operação',
                'error'    => $e->getMessage()
            ]);
        }
        return $this->response->setJSON([
            'response' => 'Warning',
            'msg'      => 'Nenhum usuário encontrado para essa pesquisa!',
        ]);
    }
    public function getAllocationDisponivel(int $idSerie, int $dayWeek, int $position, string $shift)
    {


        try {

            $disciplinesNaoPermitidas = [];
            $teacherPermitidos = [];
            $dis = [];

            $datas = $this->scheduleModel->getTotalDiscBySerie($idSerie);



            if ($datas == null) {

                $response = $this->allocationModel->getAllocationFree($dayWeek, $position, $shift);

                return $this->response->setJSON($response);
            } else {
                //return $this->response->setJSON($datas);
                foreach ($datas as $item) {

                    $limitAulasDisciplina = $this->disciplineModel->getLimitClassroom($item->id);

                    if ($limitAulasDisciplina->amount <= $item->total) {
                        $disciplinesNaoPermitidas[] = $item->id;
                    }

                    $teacherPermitidos[] = $item->id_teacher;
                }


                

                $disciplineTeacherOcupados = $this->scheduleModel->getDisciplineTeacher($idSerie);

                
                foreach ($disciplineTeacherOcupados as $is) {
                    
                    $dis[] = $is->id_discipline;
                }
                
                $profDisciplinaPermitida = $this->allocationModel->getAllocationByDayWeek($idSerie, $dayWeek, $position, $shift, $dis,);
                
                
                foreach ($profDisciplinaPermitida as $itenPermitido) {
                    
                    $teacherPermitidos[] = $itenPermitido['id_teacher'];
                }

                if ($disciplinesNaoPermitidas != null) {
                    $response = $this->allocationModel->getAllocationFreeSemAsDisciplinesNãoPermitidas($dayWeek, $position, $shift, $disciplinesNaoPermitidas, $teacherPermitidos);
                    //$response['origem'] = 'disciplinas nao permitida';
                    return $this->response->setJSON($response);
                }
                
                $response = $this->allocationModel->getAllocationByDayWeekABCDE($idSerie, $dayWeek, $position, $shift, $teacherPermitidos);
                               
                return $this->response->setJSON($response);
            }

            //return $this->response->setJSON($datas);


            // if ($datas != null) {

            //     foreach ($datas as $d) {

            //         $limit = $this->disciplineModel->getLimitClassroom($d->id);

            //         if ($limit->amount <= $d->total) {
            //             $limits[] = $d->id;
            //         }
            //     }

            //     if($limits != null) {

            //         $data = $this->allocationModel->getAllocationByDayWeek($idSerie, $dayWeek, $position, $shift, $limits);
            //     }

            // } else {

            //     $data = $this->allocationModel->getAllocationByDayWeekA($idSerie, $dayWeek, $position, $shift);
            // }

            // return $this->response->setJSON($data);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => 'Não foi possível executar a operação',
                'error'    => $e->getMessage()
            ]);
        }
    }
    public function getOcupationSchedule(int $idAllocation)
    {
        try {

            $data = $this->scheduleModel->getScheduleByIdAllocation($idAllocation);

            return $this->response->setJSON($data);
        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => 'Não foi possível executar a operação',
                'error'    => $e->getMessage()
            ]);
        }
    }

    public function getTotalScheduleByDiscipline($idDiscipline)
    {
        try {

            $data = $this->scheduleModel->getTotalScheduleByDiscipline($idDiscipline);

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
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/admin/blog');
        }

        $val = $this->validate(
            [
                'nIdAlocacao' => 'required',
            ],
            [
                'nIdAlocacao' => [
                    'required' => 'Preenchimento Obrigatório!',
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
            </div>'
            ];

            return $this->response->setJSON($response);
        }

        $horario['id_allocation'] = $this->request->getPost('nIdAlocacao');
        $horario['dayWeek'] = $this->request->getPost('nDayWeek');
        $horario['position'] = $this->request->getPost('nPosition');
        $horario['id_series'] = $this->request->getPost('nSerie');
        //$horario['id_ano_letivo'] = 1;
        $horario['status'] = 'A';
        $horario['id_year_school'] = session('session_idYearSchool');

        $save = $this->scheduleModel->save($horario);

        if ($save) {

            $allocationId = $horario['id_allocation'];
            //RECUPERA OS ID_TEACHER_DISCIPLINE
            $teacherDiscipline = $this->allocationModel->getTeacherByIdAllocation($allocationId);

            //RECUPERA OS ID
            $teacDisc = $this->teacDiscModel->find($teacherDiscipline[0]->id_teacher_discipline);

            //TOTAL 
            $total = $teacDisc->amount;

            $this->allocationModel->set('situation', 'O')
                ->where('id', $allocationId)
                ->update();

            //TOTAL DE ALOCACAO
            $totalAllocation = $this->allocationModel->getCountByIdTeacDiscOcupation($teacherDiscipline[0]->id_teacher_discipline);
            if ($total <= $totalAllocation) {

                $this->allocationModel->set('situation', 'B')
                    ->where('id_teacher_discipline', $teacherDiscipline[0]->id_teacher_discipline)
                    ->where('situation', 'L')
                    ->where('id_year_school', session('session_idYearSchool'))
                    ->update();
            }

            $response = [
                'status' => 'OK',
                'error' => false,
                'code' => 200,
                'msg' => '<p>Operação realizada com sucesso!</p>',
                'total' => $total,
                'totalAll' => $totalAllocation,
            ];
            return $this->response->setJSON($response);
        }
    }

    public function del()
    {
        $id = $this->request->getPost('id');

        try {
            $data = $this->scheduleModel->find($id);

            if ($data != null) {

                $id_allocation = $data->id_allocation;
                $allocation = $this->allocationModel->set('situation', 'L')
                    ->where('id', $id_allocation)
                    ->update();

                if ($allocation) {

                    //RECUPERA OS ID_TEACHER_DISCIPLINE
                    $teacherDiscipline = $this->allocationModel->getTeacherByIdAllocation($id_allocation);

                    // //RECUPERA OS ID
                    // $teacDisc = $this->teacDiscModelModel->find($teacherDiscipline[0]->id_teacher_discipline);

                    // //TOTAL 
                    // $total = $teacDisc->amount;

                    // //TOTAL DE ALOCACAO
                    // $totalAllocation = $this->allocationModel->getCountByIdTeacDiscOcupation($teacherDiscipline[0]->id_teacher_discipline);
                    // if ($total <= $totalAllocation) {

                    $this->allocationModel->set('situation', 'L')
                        ->where('id_teacher_discipline', $teacherDiscipline[0]->id_teacher_discipline)
                        ->where('situation', 'B')
                        ->where('id_year_school', session('session_idYearSchool'))
                        ->update();
                    //}


                    $delete = $this->scheduleModel->where('id', $data->id)
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
                }
            }
        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => 'Não foi possível executar a operação',
                'error'    => $e->getMessage()
            ]);
        }
        return $this->response->setJSON([
            'response' => 'Warning',
            'msg'      => 'Nenhum registro encontrado para essa pesquisa!',
        ]);
    }

    public function show($id = null)
    {

        try {
            $data = $this->scheduleModel->getDataForDelete($id);
            // definir nova consuta para todos os dados da schedule

            if ($data != null) {
                return $this->response->setJSON($data);
            }
        } catch (Exception $e) {
            return $this->response->setJSON([
                'response' => 'Erros',
                'msg'      => 'Não foi possível executar a operação',
                'error'    => $e->getMessage()
            ]);
        }

        return $this->response->setJSON([
            'response' => 'Warning',
            'msg'      => 'Nenhum registro encontrado para essa pesquisa!',
        ]);

        // $delete = $this->scheduleModel->where('id', $id)
        //     ->delete();
        // if ($delete) {
        //     $response = [
        //         'status' => 'OK',
        //         'error' => false,
        //         'code' => 200,
        //         'msg' => '<p>Operação realizada com sucesso!</p>'
        //     ];
        //     return $this->response->setJSON($response);
        // }
    }

    public function replace()
    {
        $val = $this->validate(
            [
                'idTeacher' => 'required',
                'newTeacher' => 'required',
            ],
            [
                'idTeacher' => [
                    'required' => 'Preenchimento Obrigatório!',
                ],
                'newTeacher' => [
                    'required' => 'Preenchimento Obrigatório!',
                ],
            ]
        );

        if (!$val) {

            $response = [
                'status' => 'ERROR',
                'error' => true,
                'code' => 400,
                'msg' => '<div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                <span class="alert-icon"><i class="fa fa-thumbs-down"></i></span>
                <span class="alert-text"><strong>Ops! </strong>Erro(s) no preenchimento do formulário!</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>',
            ];

            return $this->response->setJSON($response);
        }

        $horario['idTeacher'] = $this->request->getPost('idTeacher');
        $horario['idNewTeacher'] = $this->request->getPost('newTeacher');

        $allocationTeacher = $this->allocationModel->getAllocationTeacherOcupationReplace($horario['idTeacher']);

        foreach ($allocationTeacher as $item) {

            $this->allocationModel->set('situation', 'L')
                ->where('id', $item->id)
                ->where('situation', 'O')
                ->where('id_year_school', session('session_idYearSchool'))
                ->update();

            $this->scheduleModel->deleteScheduleForAllocation($item->id);
        }

        $response = [
            'status' => 'OK',
            'error' => false,
            'code' => 200,
            'msg' => '<p>Operação realizada com sucesso!</p>',
            'idTeacher' => $horario['idTeacher'],
            'idNewTeacher' => $horario['idNewTeacher'],
        ];
        return $this->response->setJSON($allocationTeacher);
    }
}
