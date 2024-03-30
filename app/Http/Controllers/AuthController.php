<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function login(Request $request){
		$credenciais = $request->all(['email', 'password']);
		//temos que autenticar o usuário
		//ele retorna um token de autorização
		$token = auth('api')->attempt($credenciais);

		if($token){ //autenticação bem sucedida
			return response()->json(['token' => $token]);
		}else{ //erro de autenticação
			return response()->json(['erro' => 'Erro! Usuário ou senha inválidos.', 403]);
		}
		//403 proibido (login inválido)
		dd($token);
		return 'login';
		//e retornar um json
	}
	public function logout(){
		auth('api')->logout(); //Aqui também o cliente tem que encaminhar um jwt válido
		return response()->json(['msg' => 'Logout realizado com sucesso!']);
	}

	public function refresh(){
		$token = auth('api')->refresh(); //cliente encaminhe o jwt váalido 
		return response()->json(['token' => $token]);
		
	}
	public function me(){
		return response()->json(auth()->user());
		
	}
}
