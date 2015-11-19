<?php

namespace CodeProject\Http\Controllers;

use CodeProject\Entities\Project;
use CodeProject\Repositories\ProjectRepository;

use CodeProject\Http\Requests;
use CodeProject\Services\ProjectService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;

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
     * @param ProjectRepository $repository
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
        return $this->repository->with(['owner', 'client'])->findWhere(['owner_id'=>Authorizer::getResourceOwnerId()]);
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
            if($this->checkProjectPermissions($id)==false) {
                return ['error'=>'Access forbidden'];
            }

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
            if($this->checkProjectPermissions($id)==false) {
                return ['error'=>'Access forbidden'];
            }
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
            if($this->checkProjectOwner($id)==false) {
                return ['error'=>'Access forbidden'];
            }

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

    /**
     * Check if logged user is owner of the project
     * @param $projectId
     * @return bool
     */
    private function checkProjectOwner($projectId)
    {
        $userId = Authorizer::getResourceOwnerId();

        return $this->repository->isOwner($projectId, $userId);
    }

    /** Check if logged user is member of the project
     * @param $projectId
     * @return bool
     */
    private function checkProjectMember($projectId)
    {
        $userId = Authorizer::getResourceOwnerId();

        return $this->repository->hasMember($projectId, $userId);
    }

    /**
     * Check if logged user is owner or member of the project
     * @param $projectId
     * @return bool
     */
    private function checkProjectPermissions($projectId)
    {
        if($this->checkProjectOwner($projectId) or $this->checkProjectMember($projectId)){
            return true;
        }

        return false;
    }

    /**
     * TODO: use dependency injection instead of using the class Project directly
     * Lists all members of project
     * @param $id
     * @return array|string
     */
    public function show_members($id)
    {
        try {
            $p = $this->repository->skipPresenter()->find($id);
            if($p->members->isEmpty()){
                return "Projeto não contém membros";
            } else {
                return $p->members;
            }
        } catch (ModelNotFoundException $e){
            return [
                "error" => true,
                "message" => "Projeto inexistente"
            ];
        }
    }

    public function add_member(Request $request, $id)
    {
        return $this->service->addMember($request->all(), $id);

    }

    public function remove_member(Request $request, $id)
    {
        return $this->service->removeMember($request->all(), $id);
    }

    public function is_member($id, $member_id)
    {
        return $this->service->is_member($id, $member_id);
    }
}
