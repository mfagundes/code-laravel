<?php

namespace CodeProject\Http\Controllers;

use CodeProject\Repositories\ProjectTaskRepository;
use CodeProject\Services\ProjectTaskService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CodeProject\Http\Requests;
use CodeProject\Http\Controllers\Controller;

class ProjectTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @var ProjectTaskRepository
     */

    public $repository;

    /**
     * @var ProjectTaskService
     */

    public $service;

    /**
     * @param ProjectTaskRepository $repository
     * @param ProjectTaskService $service
     */

    public function __construct(ProjectTaskRepository $repository, ProjectTaskService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }

    public function index($id)
    {
        return $this->repository->findWhere(['project_id'=>$id]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        return $this->service->create($request->all(), $id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @param $taskId
     * @return \Illuminate\Http\Response
     */
    public function show($id, $taskId)
    {
        $response = $this->repository->findWhere(['project_id'=>$id, 'id'=>$taskId]);
        if(!$response->isEmpty()) {
            return $response;
        } else {
            return [
                "error" => true,
                "message" => "Tarefa não existe"
            ];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @param $taskId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $taskId)
    {
        return $this->service->update($request->all(), $id, $taskId);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @param $taskId
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $taskId)
    {
        try {
            $this->repository->delete($taskId);
            return "Tarefa " . $taskId . " excluída com sucesso";
        } catch (ModelNotFoundException $e){
            return [
                "error" => true,
                "message" => "Tarefa não existe"
            ];
        }
    }
}
