<?php

namespace CodeProject\Services;

use CodeProject\Entities\ProjectMember;
use CodeProject\Entities\User;
use CodeProject\Repositories\ProjectMemberRepository;
use CodeProject\Repositories\ProjectRepository;
use CodeProject\Validators\ProjectValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Mockery\CountValidator\Exception;
use Prettus\Validator\Exceptions\ValidatorException;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ProjectService
{
    /**
     * @var ProjectRepository
     */

    protected $repository;

    /**
     * @var ProjectValidator $validator
     */

    protected $validator;
    /**
     * @var ProjectMemberRepository
     */
    private $projectMemberRepository;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param ProjectRepository $repository
     * @param ProjectValidator $validator
     * @param ProjectMemberRepository $projectMemberRepository
     */
    public function __construct(ProjectRepository $repository, ProjectValidator $validator, ProjectMemberRepository $projectMemberRepository, Filesystem $filesystem, Storage $storage)
    {

        $this->repository = $repository;
        $this->validator = $validator;
        $this->projectMemberRepository = $projectMemberRepository;
        $this->filesystem = $filesystem;
        $this->storage = $storage;
    }

    /**
     * Create projects after checking params are valid
     * @param array $data
     * @return array|mixed
     */
    public function create(array $data)
    {
        try{
            $this->validator->with($data)->passesOrFail();
            return $this->repository->create($data);
        } catch (ValidatorException $e) {
            return [
                'error' => true,
                'message' => $e->getMessageBag()
            ];
        }
    }

    /**
     * Update project after checking params are valid
     * @param array $data
     * @param $id
     * @return array|mixed
     */
    public function update(array $data, $id)
    {
        try {
            $this->validator->with($data)->passesOrFail();
            return $this->repository->update($data, $id);
        } catch (ValidatorException $e) {
            return [
                'error' => true,
                'message' => $e->getMessageBag()
            ];
        }
    }

    /**
     * TODO: refactor this method (maybe move to ProjectRepositoryEloquent)
     * Checks if project ($id) and member ($data['member_id']) exist
     * and create relation between them
     * @param array $data
     * @param $id
     * @return array|static
     */
    public function addMember(array $data, $id)
    {
        try {
            $p = $this->repository->skipPresenter()->find($id);
            $u = User::find($data['member_id']);

            foreach($p->members as $member) {
                if($member['id']==$u['id']){
                    return [
                        "error" => true,
                        "message" => "Usuário já é membro do projeto"
                    ];
                }
            }
            return ProjectMember::create(['project_id'=>$p['id'], 'member_id'=>$u['id']]);
        } catch(ModelNotFoundException $e){
            return [
                "error"=>true,
                "message" => "O projeto não existe"
            ];
        } catch(QueryException $e) {
            return [
                "error"=>true,
                "message" => "O usuário não existe" . $e->getMessage()
            ];
        }
    }

    /**
     * TODO: refactor this method (maybe move to ProjectRepositoryEloquent)
     *
     * Checks if project ($id) exists and remove user $data['member_id']
     * if he/she is a project member
     * @param array $data
     * @param $id
     * @return array|string
     *
     * Não consegui aqui usar ProjectMember, daí ter criado um repository
     */
    public function removeMember(array $data, $id)
    {
        try {
            $p = $this->repository->skipPresenter()->find($id);
            foreach($p->members as $member) {
                if($member['id']==$data['member_id']){
                    $pm = $this->projectMemberRepository->findWhere(['project_id'=>$id, 'member_id'=>$data['member_id']]);
                    if(count($pm)==1){
                        $this->projectMemberRepository->delete($pm[0]['id']);
                        return "Usuário " . $data['member_id'] . " removido com sucesso do projeto " . $id;
                    } else {
                        return [
                            "error" => true,
                            "message" => "O usuário " . $data['member_id'] . " está duplicado no projeto " . $id
                        ];
                    }
                }
            }

            return "Não existe o usuário " . $data['member_id'] . " no projeto " . $id;

        } catch(ModelNotFoundException $e) {
            return [
                "error" => true,
                "message" => "Projeto não existe"
            ];
        }

    }

    /**
     * TODO: use json response
     * Checks if user ($member_id) is member of project ($id)
     * @param $id
     * @param $member_id
     * @return string
     */
    public function is_member($id, $member_id)
    {
        $m = \CodeProject\Entities\User::find($member_id);
        if(count($m)==0) {
            return "Usuário com id " . $member_id . " não existe";
        }

        if(count($this->projectMemberRepository->findWhere(['project_id'=>$id, 'member_id'=>$member_id]))>0) {
            return "Usuário " . $m->name . " pertence ao projeto " . $id;
        } else {
            return "Usuário " . $m->name . " não pertence ao projeto " . $id;
        }
    }

    public function createFile(array $data)
    {
        $project = $this->repository->skipPresenter()->find($data['project_id']);
        if(!$project)
            return "Projeto não existe";

        try {
            $projectFile = $project->files()->create($data);

            $this->storage->put($projectFile->id . "." . $data['extension'], $this->filesystem->get($data['file']));
            return "Arquivo " . $data['name'] . " criado com sucesso";
        } catch (Exception $e) {
            return "Erro ao criar arquivo: " . $e;
        }
    }

    public function removeFile($project_id, $file_id)
    {
        $user_is_authorized = true;
        try {
            $project = $this->repository->skipPresenter()->find($project_id);

            if($user_is_authorized){

                $projectFile = $project->files()->find($file_id);
                if ($projectFile) {
                    $deletedFile = $project->files()->find($file_id)->delete();
                    $this->storage->delete($projectFile['id'] . '.' . $projectFile['extension']);
                    return "Arquivo " . $projectFile['name'] . " removido com sucesso";
                } else {
                    return "Arquivo não existe ou não pertence ao projeto";
                }
            }

        } catch (ModelNotFoundException $e) {
            return "Projeto não existe";
        } catch (ErrorException $e) {
            return "Erro na exclusão de arquivo";
        }
    }


}