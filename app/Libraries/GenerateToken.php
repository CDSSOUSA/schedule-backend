<?php

namespace App\Libraries;

class GenerateToken {

    private $key;
    private $time;
    private $header;
    private $payload;
    private $token; 

    public function setKey($key){
        $this->key = $key;
    }

    private static function base64url_encode($data)
    {
        return str_replace(['+','/','='], ['-','_',''], base64_encode($data));
    }
 
    private static function base64_decode_url($string) 
    {
        return base64_decode(str_replace(['-','_'], ['+','/'], $string));
    }

    public function generate()
    {
        

        //$key = Services::getSecretKey();
        $this->time = time();

        $this->header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $h = base64_encode(json_encode($this->header));

        $this->payload = [
            //'aud' => base_url(),
            'iat' => $this->time,
            'exp' => $this->time + 4800,
            // 'data'=> [
            //     'login' => $usuario['login'],
            //     'id_system' => $usuario['id_system']
            // ]
        ];

        //$jwt = JWT::encode($payload,$key, 'HS256');
        $p = $this->base64url_encode(json_encode($this->payload));

        $s = $this->base64url_encode(hash_hmac('sha256', $h . '.' . $p, $this->key, true));

        $this->token = $h . "." . $p . "." . $s;

        return $this->token;

    }

    public function validate(string $token): bool
    {
        $dados = explode('.', $token);

        $header = $dados[0];
        $payload = $dados[1];
        $signature = $dados[2];

        //$key = Services::getSecretKey();

        $validate = $this->base64url_encode(hash_hmac('sha256', $header . '.' . $payload, $this->key, true));
        $time_exp = json_decode($this->base64_decode_url($payload));      
        
        if ($signature == $validate && $time_exp->exp > time()) {            
            
            return true;
        }
        return false;
    }
    
}