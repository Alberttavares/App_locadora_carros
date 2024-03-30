<?php

namespace App\Http\Controllers;

use App\Models\Carro;
use Illuminate\Http\Request;
use App\Repositories\CarroRepository;


class CarroController extends Controller
{
    protected $carro;

    public function __construct(Carro $carro)
    {
        $this->carro = $carro;
    }
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {

		$CarroRepository = new CarroRepository($this->carro);

		if($request->has('atributos_modelo')){
			$atributos_modelo = 'modelo:id,'.$request->atributos_modelo;
			$CarroRepository->selectAtributosRegistrosRelacionados($atributos_modelo);
		}else{
		
			$CarroRepository->selectAtributosRegistrosRelacionados('modelo');
		}

		if($request->has('filtro')){

			$CarroRepository->filtro($request->filtro);
		}

		if($request->has('atributos')){
			$CarroRepository->selectAtributos($request->atributos);
			//dd($request->get('atributos'));
		}
        return response()->json($CarroRepository->getResultado(), 200);
    }
	/**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        // Lógica para exibir o formulário de criação
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


		$request->validate($this->carro->rules(), $this->carro->feedback());
       	$carro = $this->carro->create([
			'modelo_id' => $request->nmodelo_id,
			'placa' => $request->placa,
			'disponivel'=> $request->disponivel,
			'km' => $request->km
		]);
        return response()->json($carro, 201);
    }
	 /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
        $carro = $this->carro->with('modelcarro')->find($id);
		if($carro === null){
			return response()->json(['erro' => 'recurso pesquisado não existe'], 404);
		}
        return response()->json($carro, 200);
    }
	/**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */

    public function edit(Carro $carro)
    {
        // Lógica para exibir o formulário de edição
    }
	/**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */

	public function update(Request $request, $id)
    {
       
        $carro = $this->carro->find($id);
		if($carro === null){
			 return response()->json(['erro' => 'Não é possível realizar atualização, o recurso não existe'], 404);
		}

		if($request->method()==='PATCH'){

			$regrasDinamicas = array();
			foreach($carro->rules() as $input => $regra){

				if(array_key_exists($input, $request->all())){
					$regrasDinamicas[$input] = $regra;
				}
			}
			$request->validate($regrasDinamicas);
			
		}else{
			$request->validate($carro->rules());

		}

		
		$carro->fill($request->all());
		$carro->save();
		

        return response()->json($carro, 200);
    	
    }
	 /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */

        public function destroy(Request $request, $id)
    {
        $carro = $this->carro->find($id);
		if($carro === null){
			return response()->json(['erro' => 'Não é possível realizar a exclusão, o recurso não existe'], 404);
	   	}

        $carro->delete();
        return response()->json(['msg' => 'O carro foi removido com sucesso!'], 200);
        
    }
}
