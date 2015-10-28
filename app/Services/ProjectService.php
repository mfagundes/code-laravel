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

    public function __construct(ProjectRepository $repository, ProjectValidator $validator, ProjectMemberRepository $projectMemberRepository)
    {

        $this->repository = $repository;
        $this->validator = $validator;
        $this->projectMemberRepository = $projectMemberRepository;
    }

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

    public function addMember(array $data, $id)
    {
        try {
            $p = $this->repository->find($id);
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

    public function removeMember(array $data, $id)
    {
        try {
            $p = $this->repository->find($id);
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


}