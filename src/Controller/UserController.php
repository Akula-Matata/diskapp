<?php

namespace DiskApp\Controller;

use Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use DiskApp\Service\UserService;

class UserController extends BaseController
{
    public function register(Request $request)
    {
        try
        {
        	$json = json_decode($request->getContent(), true);

            $username = $json["username"];
            $password = $json["password"];

            $hash = hash('sha256', $password . self::SALT, false);

            $this->userService->createUser($username, $hash);

            return new JsonResponse(
                [
                    'message' => 'user was successfully created!',
                    'username' => $username
                ], 
                Response::HTTP_CREATED
            );
        }
        catch(Exception $ex)
        {
            return new JsonResponse(
                [
                    'message' => $ex->getMessage()
                ], 
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}