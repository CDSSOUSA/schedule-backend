<?php

namespace App\Libraries;

class Messages
{


    private string $messageErrorLogin;

    private string $messageError;

    public function __construct()
    {
        $this->messageError = '<div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                                <span class="alert-icon"><i class="fa fa-thumbs-down"></i></span>
                                <span class="alert-text"><strong>Ops! </strong>Erro(s) no preenchimento do formulário!</span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';

        $this->messageErrorLogin = '<div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                                        <span class="alert-icon"><i class="fa fa-thumbs-down"></i></span>
                                        <span class="alert-text"><strong>Ops! </strong>Dados não conferem!</span>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>';
    }

    public function getMessageError()
    {
        return $this->messageError;
    }
    public function getMessageErrorLogin()
    {
        return $this->messageErrorLogin;
    }
}
