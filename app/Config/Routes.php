<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

$routes->group('/login',['namespace'=>'App\Controllers\Login'],function ($routes){
    //$routes->get('/', 'Login::index');
    $routes->post('/', 'Login::login');
    $routes->post('validate', 'Login::validateToken');
});

$routes->group('/config',['namespace'=>'App\Controllers\Configuration'],function ($routes){
    $routes->get('show','Configuration::getConfiguration');
    $routes->post('create','Configuration::createOrUpdate');
});

$routes->group('/year',['namespace'=>'App\Controllers\Year',/*'filter'=>'accessFilter'*/],function ($routes){
     
    $routes->get('show/(:any)', 'Year::show/$1');  
    $routes->get('/', 'Year::list'); 
    $routes->get('active', 'Year::getYearActive'); 
    $routes->post('create', 'Year::create');
    $routes->post('update', 'Year::update');  
   
});

$routes->group('/discipline',['namespace'=>'App\Controllers\Discipline'],function ($routes){
    //$routes->get('/','Discipline::show');
    $routes->get('/','Discipline::list');    
    $routes->get('show/(:any)','Discipline::show/$1');    
    $routes->post('create', 'Discipline::create'); 
    $routes->post('update', 'Discipline::update'); 
    $routes->post('delete', 'Discipline::delete'); 
});

$routes->group('/series',['namespace'=>'App\Controllers\Series',/*'filter'=>'accessFilter'*/],function ($routes){
   
    $routes->get('show/(:any)', 'Series::show/$1');
    $routes->get('/', 'Series::list');
   // $routes->get('list', 'Series::listSeries');
    $routes->get('list/shift/(:any)', 'Series::listSeriesByShift/$1');
    $routes->get('edit/(:any)', 'Series::show/$1');
    $routes->post('active', 'Series::active');
    $routes->post('create', 'Series::create');
    $routes->post('update', 'Series::update');
    $routes->get('sendEmail/(:any)', 'Series::sendEmail/$1');
    $routes->post('send', 'Series::send');
   
});

$routes->group('/teacher',['namespace'=>'App\Controllers\Teacher'],function ($routes){
    // $routes->get('/','Professor::add');
    $routes->get('list','Teacher::list');
    $routes->get('listOff/(:any)','Teacher::listOff/$1');
    $routes->get('show/(:any)','Teacher::show/$1');
    $routes->get('listTeacDisc/(:any)','Teacher::listTeacDisc/$1');
    $routes->get('listDisciplinesByTeacher/(:any)','Teacher::listDisciplinesByTeacher/$1');
    //$routes->get('add_profissional_horario/(:any)/(:any)/(:any)','Horario::addProfissionalHorario/$1/$2/$3');   
    $routes->post('create', 'Teacher::create'); 
    $routes->post('update', 'Teacher::update'); 
    $routes->post('delete', 'Teacher::delete'); 
    //$routes->get('edit/(:any)', 'Teacher::show/$1'); 
    
});

$routes->group('/allocation',['namespace'=>'App\Controllers\Allocation',/*'filter'=>'accessFilter'*/],function ($routes){
   
    //$routes->get('/', 'YearSchool::index');
    //$routes->get('list', 'YearSchool::list');
    $routes->post('create', 'Allocation::create');
    $routes->get('showTeacherOcupation/(:any)', 'Allocation::showTeacherOcupation/$1');
    $routes->get('showTeacherChecked/(:any)', 'Allocation::showTeacherChecked/$1');
    $routes->get('getTotalAllocationTeacher/(:any)', 'Allocation::getTotalAllocationTeacher/$1');
    $routes->get('show/(:any)', 'Allocation::show/$1');
    $routes->post('delete', 'Allocation::delete');
    //$routes->post('active', 'YearSchool::active');
   
});

$routes->group('/teacDisc',['namespace'=>'App\Controllers\TeacDisc'],function ($routes){
    $routes->get('list/(:any)','TeacDisc::list/$1');
    $routes->get('show/(:any)','TeacDisc::show/$1');
    //$routes->get('edit/(:any)','TeacDisc::edit/$1');
    //$routes->get('delete/(:any)','TeacDisc::delete/$1');
    $routes->post('create','TeacDisc::create');
    //$routes->get('add_profissional_horario/(:any)/(:any)/(:any)','Horario::addProfissionalHorario/$1/$2/$3');   
    //$routes->post('create', 'Professor::create'); 
    $routes->post('update', 'TeacDisc::update'); 
    $routes->post('delete', 'TeacDisc::delete'); 
});

$routes->group('/schedule',['namespace'=>'App\Controllers\Schedule'],function ($routes){
    // $routes->get('/','Horario::index');
    // $routes->get('add_profissional_horario/(:any)/(:any)/(:any)','Horario::addProfissionalHorario/$1/$2/$3');   
    // $routes->post('add', 'Horario::add'); 
    $routes->get('getAllocation/(:any)','Schedule::getAllocationDisponivel/$1');    
    $routes->get('getOcupationSchedule/(:any)','Schedule::getOcupationSchedule/$1');    
    $routes->post('create','Schedule::create');    
    $routes->get('delete/(:any)','Schedule::deleteSchedule/$1');    
    $routes->get('show/(:any)','Schedule::show/$1');    
    $routes->post('del','Schedule::del');    
    $routes->get('list/(:any)','Schedule::list/$1');    
    $routes->get('listDPS/(:any)/(:any)/(:any)/(:any)','Schedule::listDPS/$1/$2/$3/$4');    
    $routes->get('listSeries/(:any)','Schedule::listSeries/$1');    
    $routes->get('listDisciplines/(:any)','Schedule::getTotalScheduleByDiscipline/$1');    
    $routes->post('replace','Schedule::replace');    
});

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
//$routes->get('/', 'Home::index');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
