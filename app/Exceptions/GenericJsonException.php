<?php

namespace App\Exceptions;

use Exception;

class GenericJsonException extends Exception
{
    //
    public function report(){

    }
    public function render($request){
        return response()->json(['message' => $this->getMessage()], $this->code);
    }
}
