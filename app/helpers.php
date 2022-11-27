<?php

use App\Models\Client;
use Illuminate\Support\Facades\Validator;

//Metodo para registrar un cliente
function RegisterClient($request){
    $rules= [
        'documento' => 'required',
        'nombres'   => 'required',
        'email'     => 'required',
        'celular'   => 'required',
    ];

    $validator = Validator::make($request, $rules);

    try{
        //Errores de validacion
        if ($validator->fails()){
            return [
                'success'       => 'false',
                'cod_error'     => '400',
                'message_error' => $validator->errors()->first(),
            ];
        }else{
            //Respuesta exitosa
            Client::create($request);
            return [
                'success'       => 'true',
                'cod_error'     => '200',
                'message_error' => 'Cliente registrado exitosamente'
            ];
        }
    } catch (\Throwable $th) {
        //Errores de fallo de servidor
        return [
            'success'       => 'false',
            'cod_error'     => '500',
            'message_error' => $th->getMessage()
        ];
    }
}
