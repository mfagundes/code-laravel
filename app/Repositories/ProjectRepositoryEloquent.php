<?php

namespace CodeProject\Repositories;

use CodeProject\Entities\Project;
use Prettus\Repository\Eloquent\BaseRepository;

class ProjectRepositoryEloquent extends BaseRepository implements ProjectRepository
{
    public function model()
    {
        return Project::class;
    }

    public function hasMember($projectId, $memberId){
        $project = $this->find($projectId);

        foreach($project as $member) {
            if($member->id == $memberId){
                return true;
            }
        }

        return false;
    }

}