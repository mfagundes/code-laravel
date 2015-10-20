<?php

namespace CodeProject\Http\Controllers;

use CodeProject\Repositories\ClientRepository;
use CodeProject\Services\ClientService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\EventDispatcher\Tests\Service;


class ClientController extends Controller
{

    /**
     * @var ClientRepository
     */

    private $repository;

    /**
     * @var ClientService
     */
    private $service;

    /**
     * @param ClientRepository $repository
     * @param ClientService $service
     */

    public function __construct(ClientRepository $repository, ClientService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->repository->all();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->service->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            return $this->repository->find($id);
        } catch(ModelNotFoundException $e){
            return [
                "error" => true,
                "message" => "Erro: ". $e->getMessage()
            ];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{
            return $this->service->update($request->all(), $id);
        } catch (ModelNotFoundException $e){
            return [
                "error" => true,
                "message" => "Erro: ".$e->getMessage()
            ];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $this->repository->delete($id);
            return "Cliente com id " . $id . " excluído com sucesso";
        } catch(ModelNotFoundException $e) {
            return [
                "error" => true,
                "message" => "Erro: " . $e->getMessage()
            ];
        } catch(QueryException $e){
            return [
                "error" => true,
                "message" => "Cliente possui projetos vinculados. Elimine-os antes de excluir o cliente"
            ];
        }
    }
}
