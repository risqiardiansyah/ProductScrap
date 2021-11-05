<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Repositories\UserRepository;;

use Illuminate\Support\Facades\Validator;

class UserController extends ApiController
{
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function validateThis($request, $rules = array())
    {
        return Validator::make($request->all(), $rules);
    }

    public function getProduk()
    {
        $data = $this->userRepo->getProduk();

        if ($data) {
            return $this->sendResponse(0, 'Success', $data);
        } else {
            return $this->sendError(2, 'Error !', []);
        }
    }
}
