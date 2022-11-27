<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SoapServiceController extends Controller
{
    public function soap_service(){
        $server = new \nusoap_server();
        $nameSpace = 'urn:server';
        $server->configureWSDL('server', $nameSpace);
        $server->wsdl->schemaTargetNamespace = $nameSpace;

        //Lineamiento de estructura de respuesta
        $Response =array(
            'success'       => 'xsd:string',
            'cod_error'     => 'xsd:string',
            'message_error' => 'xsd:string',
        );

        //Validacion Register client
        $server->wsdl->addComplexType(
            'Register',
            'complexType',
            'struct',
            'all',
            '',
            array('documento' => array('name' => 'documento', 'type' => 'xsd:string'),
                    'nombres' => array('name' => 'nombres', 'type' => 'xsd:string'),
                    'email'   => array('name' => 'email', 'type' => 'xsd:string'),
                    'celular' => array('name' => 'celular', 'type' => 'xsd:string'),
            )
        );

        //Registro el metodo RegisterClient
        $server->register(
            'RegisterClient',
            array('request' => 'tns:Register'),
            $Response,
            $nameSpace,
            'urn:server#RegisterClientServer',
            'rpc',
            'encoded',
            'Metodo para registrar un cliente'
        );
        $rawPostData = file_get_contents("php://input");
        return Response::make($server->service($rawPostData), 200, array('Content-Type' => 'text/xml; charset=ISO-8859-1'));
    }
}
