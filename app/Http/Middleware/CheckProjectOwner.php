<?php

namespace CodeProject\Http\Middleware;

use Closure;
use CodeProject\Repositories\ProjectRepository;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;

class CheckProjectOwner
{
    /**
     * @var ProjectRepository
     */
    private $repository;

    /**
     * @param ProjectRepository $repository
     */
    public function __construct(ProjectRepository $repository)
    {

        $this->repository = $repository;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user_id = Authorizer::getResourceOwnerId();
        $project_id = $request->project;
        if($this->repository->isOwner($project_id, $user_id) == false) {
            return [
                'error'=> true,
                'message' =>'Acesso liberado apenas para o dono do projeto'];
        }

        return $next($request);
    }
}
