<?php

namespace CodeProject\Services;

use CodeProject\Repositories\ProjectTaskRepository;
use CodeProject\Validators\ProjectTaskValidator;
use Prettus\Validator\Exceptions\ValidatorException;

class ProjectTaskService
{
    /**
     * @var ProjectTaskRepository $repository
     */

    public $repository;

    /**
     * @var ProjectTaskValidator $validator
     */

    public $validator;

    public function __construct(ProjectTaskRepository $repository, ProjectTaskValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    public function create(array $data, $id)
    {
        try {
            $data['project_id']=$id;
            $this->validator->with($data)->passesOrFail();
            return $this->repository->create($data);
        } catch (ValidatorException $e) {
            return [
                'error'=>true,
                'message' => $e->getMessageBag()
            ];
        }
    }

    public function update(array $data, $id, $taskId)
    {
        try{
            $data['project_id'] = $id;
            $this->validator->with($data)->passesOrFail();
            return $this->repository->update($data, $taskId);
        } catch (ValidatorException $e) {
            return [
                'error'=>true,
                'message' => $e->getMessageBag()
            ];
        }
    }

}