<?php

namespace CodeProject\Http\Controllers;

use CodeProject\Repositories\ProjectRepository;

use CodeProject\Http\Requests;
use CodeProject\Services\ProjectService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * @var ProjectRepository
     */
    private $repository;

    /**
     * @var ProjectService
     */

    private $service;

    /**
     * @param ProjecttRepository $repository
     * @param ProjectService $service
     */

    public function __construct(ProjectRepository $repository, ProjectService $service )
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
        return $this->repository->with(['owner', 'client'])->all();
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
        try {
            return $this->repository->with(['owner', 'client'])->find($id);
        } catch(ModelNotFoundException $e) {
            return [
                "error"=>true,
                "message" => "Erro: ".$e->getMessage()
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
        try {
            return $this->service->update($request->all(), $id);
        } catch(ModelNotFoundException $e) {
            return [
                "error" => true,
                "message" => "Erro: ". $e->getMessage()
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
            return "Projeto com id " . $id . " excluído com sucesso";
        } catch (ModelNotFoundException $e) {
            return [
                "error" => true,
                "message" => "Erro: ".$e->getMessage()
            ];
        } catch(QueryException $e) {
            return [
                "error" => true,
                "message" => "Projeto possui notas ligadas a ele e devem ser eliminadas antes."
            ];
        }
    }
}
