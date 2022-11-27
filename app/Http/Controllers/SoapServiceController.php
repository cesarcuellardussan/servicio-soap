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

        //Estructura RegisterClient
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

        //Estructura RechargeWallet
        $server->wsdl->addComplexType(
            'Recharge',
            'complexType',
            'struct',
            'all',
            '',
            array(
                'documento' => array('name' => 'documento', 'type' => 'xsd:string'),
                'celular' => array('name' => 'celular', 'type' => 'xsd:string'),
                'valor'   => array('name' => 'valor', 'type' => 'xsd:decimal'),
            )
        );

        //Registro el metodo RechargeWallet
        $server->register(
            'RechargeWallet',
            array('request' => 'tns:Recharge'),
            $Response,
            $nameSpace,
            'urn:server#RechargeWalletServer',
            'rpc',
            'encoded',
            'Metodo para recargar la billetera'
        );

        //Estructura PayPurchase
        $server->wsdl->addComplexType(
            'Pay',
            'complexType',
            'struct',
            'all',
            '',
            array(
                'documento' => array('name' => 'documento', 'type' => 'xsd:string'),
                'valor'   => array('name' => 'valor', 'type' => 'xsd:decimal'),
            )
        );

        //Registro el metodo PayPurchase
        $server->register(
            'PayPurchase',
            array('request' => 'tns:Pay'),
            $Response,
            $nameSpace,
            'urn:server#PayPurchaseServer',
            'rpc',
            'encoded',
            'Metodo para pagar una compra'
        );

        //Estructura ConfirmPayment
        $server->wsdl->addComplexType(
            'Confirm',
            'complexType',
            'struct',
            'all',
            '',
            array(
                'id'      => array('name' => 'id', 'type' => 'xsd:integer'),
                'token'   => array('name' => 'token', 'type' => 'xsd:string'),
            )
        );

        //Registro el metodo ConfirmPayment
        $server->register(
            'ConfirmPayment',
            array('request' => 'tns:Confirm'),
            $Response,
            $nameSpace,
            'urn:server#ConfirmPaymentServer',
            'rpc',
            'encoded',
            'Metodo para confirmar pago'
        );

        //Estructura CheckBalance
        $server->wsdl->addComplexType(
            'Check',
            'complexType',
            'struct',
            'all',
            '',
            array(
                'documento' => array('name' => 'documento', 'type' => 'xsd:string'),
                'celular'   => array('name' => 'celular', 'type' => 'xsd:string'),
            )
        );

        //Registro el metodo CheckBalance
        $server->register(
            'CheckBalance',
            array('request' => 'tns:Check'),
            $Response,
            $nameSpace,
            'urn:server#CheckBalanceServer',
            'rpc',
            'encoded',
            'Metodo para consultar el saldo'
        );

        $rawPostData = file_get_contents("php://input");
        return Response::make($server->service($rawPostData), 200, array('Content-Type' => 'text/xml; charset=ISO-8859-1'));
        // return Response::make($server->service($rawPostData), 200,[]);
    }
}
