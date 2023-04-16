<?php

namespace App\Controllers\Login;

use App\Libraries\GenerateToken;
use App\Libraries\Messages;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use Exception;

class Login extends ResourceController
{
    private $tokenJWT;
    private $service;

    private $messageError;
    public function __construct()
    {
        $this->service = new Services();       
        $this->tokenJWT = new GenerateToken();
        $this->tokenJWT->setKey($this->service->getSecretKey());
        $this->messageError = new Messages();
    }   
    use ResponseTrait;
    public function login()
    {       
        
        try {

            if ($this->request->getMethod() !== 'post') {
                throw new Exception("Internal Server Error"); 
               
            }

            $val = $this->validate(
                [
                    'token' => 'required',
                ],
                [
                    'token' => [
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

            $token = $this->request->getPost('token');
            $tokenAccess = getenv('TOKEN.ACCESS');

            $validateToken = base64_encode(hash_hmac('sha256', $token, true));
            $validateTokenAccess = base64_encode(hash_hmac('sha256', $tokenAccess, true));         


            if($validateToken != $validateTokenAccess){
                throw new Exception('Dados não conferem!!');               
            }                  
          
            $response = [
                'status' => 'OK',
                'error' => false,
                'code' => 200,
                'msg' => '<p>Operação realizada com sucesso!</p>',
                'tokenNew' => $this->tokenJWT->generate(),                
            ];

            session()->set('token', $response['tokenNew']);
            
            return $this->response->setJSON($response);

        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    public function validateToken () 
    {

        $tokenHeader = $this->request->getHeaderLine('Authorization');       
       
        if(!$this->tokenJWT->validate($tokenHeader)){
            throw new Exception('Ocorreu um erro inesperado!!');
        }
       
        $response = [
            'status' => 'OK',
            'error' => false,
            'code' => 200,
            'msg' => '<p>Operação realizada com sucesso!</p>',
            'msgs' => 'token válido',
            'token' => $tokenHeader,
            'tokenvalidate' => $tokenHeader
                          
        ];
       
        return $this->response->setJSON($response);  

    }
}
