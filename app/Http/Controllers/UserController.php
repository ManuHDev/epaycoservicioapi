<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;

class UserController extends Controller
{

    /**
     * recharge credit transaction
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recharge(Request $request)
    {
        $user = auth()->user();

        if(!$user->validateDataUser($request->document, $request->cellphone)||!isset($request->amount)||is_null($request->amount)) {
            return response()->json([
                'message' => 'Invalid request'
            ], Response::HTTP_CONFLICT);
        }

        $data = ['type'  =>  'credit',
                'amount' => $request->amount,
                'status' => 1,
        ];

        $result = $user->transactions()->create($data);

        if(empty($result)) {
            return response()->json([
                'message' => 'Invalid request'
            ], Response::HTTP_SERVICE_UNAVAILABLE);
           
        }

        return response()->json([
            'message' => 'ok'
        ], Response::HTTP_OK);
    }

    /**
     * payment purchase
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function payment(Request $request)
    {
        $user = auth()->user();
        
        if(!$user->allowPurchase($request->amount)) {
            return response()->json([
                'message' => 'Invalid request'
            ], Response::HTTP_CONFLICT);
        }

        $token = Str::random(6);
        $data = [
            'type' => 'debit',  
            'amount' => $request->amount,
            'status' => 0,
            'token' => $token,
        ];

        $result = $user->transactions()->create($data);

        if(empty($result)) {
            return response()->json([
                'message' => 'Invalid request'
            ], Response::HTTP_SERVICE_UNAVAILABLE);
           
        }

        $dataEmail = new \stdClass();
        $dataEmail->token = $token;
        $dataEmail->amount = $request->amount;
        $dataEmail->status = 'PENDING';
        $dataEmail->name = auth()->user()->name;
        //Mail::to(auth()->user()->email)->send(new SendMail($dataEmail));
        /*Mail::send('emails.payment',$dataEmail, function($msj){
            $msj->subject('Correo de Prueba');
            $msj->to('preirers@hotmail.com');
        });*/

        return response()->json([
            'status' => 'pending',
            'token' => $token,
            'amount' => $result->amount
        ], Response::HTTP_OK);
    }

    /**
     * confirmPayment confirmation of payment request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function confirmPayment(Request $request)
    {
        $user = auth()->user();

        $payment = $user->confirmPayment($request->token); 

        if(null == $payment) {
            return response()->json([
                'message' => 'Invalid request'
            ], Response::HTTP_CONFLICT);
        } 

        if($payment->token == $request->token) {
            if(!$user->allowPurchase($payment->amount)) {
                return response()->json([
                    'message' => 'Low balance'
                ], Response::HTTP_CONFLICT);
            }

            $data = [
                'type' => 'debit',  
                'amount' => $payment->amount,
                'status' => 1,
            ];
    
            $result = $user->transactions()->create($data);

            if(empty($result)) {
                return response()->json([
                    'message' => 'Invalid request'
                ], Response::HTTP_SERVICE_UNAVAILABLE);
               
            }
    
            return response()->json([
                'message' => 'ok'
            ], Response::HTTP_OK);
        }
    
    }

    /**
     * balance: check amount avaliable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function balance(Request  $request) 
    {
        $user = auth()->user();

        if(!$user->validateDataUser($request->document, $request->cellphone)) {
            return response()->json([
                'message' => 'Invalid request'
            ], Response::HTTP_CONFLICT);
        }

        $result = $user->balance();

        return response()->json([
            'balance' => $result
        ], Response::HTTP_OK);

    }
}
