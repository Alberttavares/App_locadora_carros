<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Modelo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ModeloRepository;

class ModeloController extends Controller
{
	protected $modelo;
	
	public function __construct(Modelo $modelo)
    {
        $this->modelo = $modelo;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$ModeloRepository = new ModeloRepository($this->modelo);

		if($request->has('atributos_marca')){
			$atributos_marca = 'marca:id,'.$request->atributos_marca;
			$ModeloRepository->selectAtributosRegistrosRelacionados($atributos_marca);
		}else{
		
			$ModeloRepository->selectAtributosRegistrosRelacionados('marca');
		}
		if($request->has('filtro')){

			$ModeloRepository->filtro($request->filtro);
		}

		if($request->has('atributos')){
			$ModeloRepository->selectAtributos($request->atributos);
			//dd($request->get('atributos'));
		}
        return response()->json($ModeloRepository->getResultado(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
		//
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
	{
		$request->validate($this->modelo->rules());

		$image = $request->file('imagem');
		$imagem_urn = $image->store('imagens/modelos', 'public');

		
		if (!$request->has('marca_id')) {
			return response()->json(['error' => 'O campo marca_id é obrigatório.'], 400);
		}

		$modelo = $this->modelo->create([
			'marca_id' => $request->marca_id,
			'nome' => $request->nome,
			'imagem' => $imagem_urn,
			'numero_portas' => $request->numero_portas,
			'lugares' => $request->lugares,
			'air_bag' => $request->air_bag,
			'abs' => $request->abs
		]);

		return response()->json($modelo, 201);
	}



    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
		$modelo = $this->modelo->with('marca')->find($id);
		if($modelo === null){
			return response()->json(['erro' => 'recurso pesquisado não existe'], 404);
		}
        return response()->json($modelo, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function edit(Modelo $modelo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		$modelo = $this->modelo->find($id);
		//dd($request->nome)modelo
		//dd($request->imagem);
		if($modelo === null){
			 return response()->json(['erro' => 'Não é possível realizar atualização, o recurso não existe'], 404);
		}

		if($request->method()==='PATCH'){

			$regrasDinamicas = array();



			foreach($modelo->rules() as $input => $regra){

				if(array_key_exists($input, $request->all())){
					$regrasDinamicas[$input] = $regra;
				}
			}
			$request->validate($regrasDinamicas);
		
		}else{
			$request->validate($modelo->rules());

		}

		if($request->file('imagem')){
			Storage::disk('public')->delete($modelo->imagem);
		}

		$image = $request->file('imagem');
		$imagem_urn = $image->store('imagens/modelos', 'public');

		$modelo->fill($request->all());
		$modelo->imagem = $imagem_urn;
		$modelo->save();
		/*
       	$modelo->update([
			'marca_id' => $request->marca_id,
			'nome' => $request->nome,
			'imagem' => $imagem_urn,
			'numero_portas'=>$request->numero_portas,
			'lugares'=>$request->lugares,
			'air_bag'=>$request->air_bag,
			'abs'=> $request->abs

		]);
		*/
        return response()->json($modelo, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id){

        $modelo = $this->modelo->find($id);
		if($modelo === null){
			return response()->json(['erro' => 'Não é possível realizar a exclusão, o recurso não existe'], 404);
	   	}

	   if($request->file('imagem')){
			Storage::disk('public')->delete($modelo->imagem);
		}

        $modelo->delete();
        return response()->json(['msg' => 'A marca foi removida com sucesso!'], 200);
    }

}
