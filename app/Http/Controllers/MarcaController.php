<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Marca;
use Illuminate\Http\Request;
use App\Repositories\MarcaRepository;


class MarcaController extends Controller
{
    protected $marca;

    public function __construct(Marca $marca)
    {
        $this->marca = $marca;
    }
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {

		$MarcaRepository = new MarcaRepository($this->marca);

		if($request->has('atributos_modelos')){
			$atributos_modelos = 'modelos:id,'.$request->atributos_modelos;
			$MarcaRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
		}else{
		
			$MarcaRepository->selectAtributosRegistrosRelacionados('modelos');
		}

		if($request->has('filtro')){

			$MarcaRepository->filtro($request->filtro);
		}

		if($request->has('atributos')){
			$MarcaRepository->selectAtributos($request->atributos);
			//dd($request->get('atributos'));
		}
        return response()->json($MarcaRepository->getResultado(), 200);
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


		$request->validate($this->marca->rules(), $this->marca->feedback());
		

		$image = $request->file('imagem');
		$imagem_urn = $image->store('imagens', 'public');

	

       	$marca = $this->marca->create([
			'nome' => $request->nome,
			'imagem' => $imagem_urn
		]);

	
        return response()->json($marca, 201);
    }
	 /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);
		if($marca === null){
			return response()->json(['erro' => 'recurso pesquisado não existe'], 404);
		}
        return response()->json($marca, 200);
    }
	/**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */

    public function edit(Marca $marca)
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
       
        $marca = $this->marca->find($id);
		if($marca === null){
			 return response()->json(['erro' => 'Não é possível realizar atualização, o recurso não existe'], 404);
		}

		if($request->method()==='PATCH'){

			$regrasDinamicas = array();
			foreach($marca->rules() as $input => $regra){

				if(array_key_exists($input, $request->all())){
					$regrasDinamicas[$input] = $regra;
				}
			}
			$request->validate($regrasDinamicas, $marca->feedback());
			
		}else{
			$request->validate($marca->rules(), $marca->feedback());

		}

		if($request->file('imagem')){
			Storage::disk('public')->delete($marca->imagem);
		}

		$image = $request->file('imagem');
		$imagem_urn = $image->store('imagens', 'public');
		
		
		$marca->fill($request->all());
		$marca->imagem = $imagem_urn;
		$marca->save();
		
		
       	/*$marca->update([
			'nome' => $request->nome,
			'imagem' => $imagem_urn
		]);
		*/
        return response()->json($marca, 200);
    	
    }
	 /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */

        public function destroy(Request $request, $id)
    {
        $marca = $this->marca->find($id);
		if($marca === null){
			return response()->json(['erro' => 'Não é possível realizar a exclusão, o recurso não existe'], 404);
	   	}

	   if($request->file('imagem')){
			Storage::disk('public')->delete($marca->imagem);
		}

        $marca->delete();
        return response()->json(['msg' => 'A marca foi removida com sucesso!'], 200);
        
    }
}
