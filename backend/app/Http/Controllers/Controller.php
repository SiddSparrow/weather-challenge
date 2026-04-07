<?php
class UserService
{
    public function register($data)
    {
        $user = User::create([$data]);       
        return $user;
    }
}

// controller
class UserController
{

    public function store(Request $request, UserService $service)
    {
        return $service->register($request);
    }
}