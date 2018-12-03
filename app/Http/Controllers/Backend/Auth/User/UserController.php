<?php

namespace App\Http\Controllers\Backend\Auth\User;

use App\Criterion\Eloquent\ThisWhereEqualsCriteria;
use App\Http\Controllers\Controller;
use App\Repositories\Auth\User\UserRepository;
use App\Transformers\UserTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $permissions = $userRepository->model()::PERMISSIONS;

        $this->middleware('permission:' . $permissions['index'], ['only' => 'index']);
        $this->middleware('permission:' . $permissions['create'], ['only' => 'create']);
        $this->middleware('permission:' . $permissions['show'], ['only' => 'show']);
        $this->middleware('permission:' . $permissions['update'], ['only' => 'update']);
        $this->middleware('permission:' . $permissions['destroy'], ['only' => 'destroy']);

        $this->userRepository = $userRepository;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Spatie\Fractalistic\Fractal
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \ReflectionException
     */
    public function index(Request $request)
    {
        $this->userRepository->pushCriteria(new RequestCriteria($request));

        return $this->transform($this->userRepository->paginate(), new UserTransformer);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Spatie\Fractalistic\Fractal
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \ReflectionException
     */
    public function show(Request $request)
    {
        $this->userRepository->pushCriteria(new ThisWhereEqualsCriteria('id', $this->decodeId($request)));

        $user = $this->userRepository->first();
        if (is_null($user)) {
            throw new ModelNotFoundException;
        }

        return $this->transform($user, new UserTransformer);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        return $this->created($this->transform($this->userRepository->create($request->all()), new UserTransformer));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Spatie\Fractalistic\Fractal
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \ReflectionException
     */
    public function update(Request $request)
    {
        $user = $this->userRepository->update($request->only([
            'first_name',
            'last_name',
            'email',
            'password',
        ]), $this->decodeId($request));

        return $this->transform($user, new UserTransformer);
    }

    public function destroy()
    {
        return $this->noContent();
    }
}
