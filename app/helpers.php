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
            $client = Client::where(['documento' => $request['documento']])->first();
            if ($client) {
                return [
                    'success'       => 'false',
                    'cod_error'     => '400',
                    'message_error' => 'the client already exists in the database'
                ];
            }else{
                Client::create([
                    'documento' => $request['documento'],
                    'nombres'   => $request['nombres'],
                    'email'     => $request['email'],
                    'celular'   => $request['celular']
                ]);
                return [
                    'success'       => 'true',
                    'cod_error'     => '200',
                    'message_error' => 'successfully registered client'
                ];
            }
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

//Metodo para recargar la billetera
function RechargeWallet($request){
    $rules= [
        'documento' => 'required',
        'celular'   => 'required',
        'valor'     => 'required|numeric|gt:0',
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
            //Busco el documento
            $client = Client::where(['documento' => $request['documento']])->first();
            if ($client) {
                $saldo  = $client->saldo + $request['valor'];
                Client::where('id',$client->id)->update(['saldo' => $saldo]);
                return [
                    'success'       => 'true',
                    'cod_error'     => '200',
                    'message_error' => 'The wallet has been successfully recharged'
                ];
            }else{
                return [
                    'success'       => 'false',
                    'cod_error'     => '400',
                    'message_error' => 'The document number cannot be found in the database'
                ];
            }
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

//Metodo para pagar compras
function PayPurchase($request){
    $rules= [
        'documento' => 'required',
        'valor'     => 'required|numeric|gt:0',
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
            //Busco el documento
            $client = Client::where(['documento' => $request['documento']])->first();
            if ($client) {
                $saldo  = $client->saldo - $request['valor'];
                if ($saldo >= 0) {
                    $token = substr(mb_strtoupper(md5(time())), 0, 6);
                    Client::where('id',$client->id)->update([
                        'token'   => $token,
                        'payment' => $request['valor']
                    ]);
                    return [
                        'success'       => 'true',
                        'cod_error'     => '200',
                        'message_error' => 'A payment confirmation email has been sent to the email : '.$client->email
                    ];
                }else{
                    return [
                        'success'       => 'false',
                        'cod_error'     => '400',
                        'message_error' => 'insufficient balance'
                    ];
                }
            }else{
                return [
                    'success'       => 'false',
                    'cod_error'     => '400',
                    'message_error' => 'The document number cannot be found in the database'
                ];
            }
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


//Metodo para confirmar pago
function ConfirmPayment($request){
    $rules= [
        'id'    => 'required|numeric|gt:0',
        'token' => 'required',
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
            //Busco el id
            $client = Client::where(['id' => $request['id']])->first();
            if ($client) {
                //Comparo el token generado
                if ($client->token == $request['token']) {

                    $saldo  = $client->saldo - $client->payment;
                    if ($saldo >= 0) {
                        Client::where('id',$client->id)->update([
                            'saldo'   => $saldo,
                            'token'   => null,
                            'payment' => 0
                        ]);
                        return [
                            'success'       => 'true',
                            'cod_error'     => '200',
                            'message_error' => 'payment confirmed, purchase done'
                        ];
                    }else{
                        return [
                            'success'       => 'false',
                            'cod_error'     => '400',
                            'message_error' => 'insufficient balance'
                        ];
                    }
                }else{
                    return [
                        'success'       => 'false',
                        'cod_error'     => '400',
                        'message_error' => 'incorrect token value'
                    ];
                }
            }else{
                return [
                    'success'       => 'false',
                    'cod_error'     => '400',
                    'message_error' => 'the id number does not exist in the database'
                ];
            }
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
