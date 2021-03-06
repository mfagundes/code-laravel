<?php

namespace CodeProject\Repositories;

use CodeProject\Entities\Project;
use Prettus\Repository\Eloquent\BaseRepository;
use CodeProject\Presenters\ProjectPresenter;

class ProjectRepositoryEloquent extends BaseRepository implements ProjectRepository
{
    /**
     * BaseRepository requires model() method
     * @return mixed
     */
    public function model()
    {
        return Project::class;
    }

    /**
     * Check if memberId is a member of project projectId
     * @param $projectId
     * @param $memberId
     * @return bool
     */
    public function hasMember($projectId, $memberId){
        $project = $this->find($projectId);

        foreach($project->members as $member) {
            if($member->id == $memberId){
                return true;
            }
        }

        return false;
    }

    /**
     * Check if $userId is owner of $projectId
     * @param $projectId
     * @param $userId
     * @return bool
     */
    public function isOwner($projectId, $userId)
    {
        if(count($this->findWhere(['id'=>$projectId, 'owner_id'=>$userId]))){
            return true;
        }

        return false;
    }

    public function presenter()
    {
        return ProjectPresenter::class;
    }

}