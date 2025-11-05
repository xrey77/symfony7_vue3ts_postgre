<?php

namespace App\Controller;

use Psr\Log\LoggerInterface; // You can inject other services too

// use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Entity\User;
use DateTimeImmutable;
// use App\Security\LoginFormAuthenticator; // Replace with your authenticator's class name

final class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'app_login', methods: ['POST'])]
    public function index(
        Request $request,
        UserRepository $userRepository,
        UserAuthenticatorInterface $userAuthenticator,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $JWTManager    
        ): JsonResponse
    {
        $data = json_decode($request->getContent());
        // $user = $userService->validateuser($data->username, $data->password);
        // if ($user) {
        //     return new JsonResponse(['message' => "Found User", 'username' => $user->getUsername()]);
        // } else {
        //     return new JsonResponse(['message' => "User Not Found."],404);
        // }


        $username = $userRepository->findOneBy(['username' => $data->username]);
        if ($username) {
            if ($username->getIsactivated()== 0) {
                return new JsonResponse([
                    'message' => 'Your account is not yet activated, please check your email inbox and activate.'
                ],404);    
            } 

            if ($username->getIsblocked() == 1) {
                return new JsonResponse([
                    'message' => 'You account has been blocked.'
                ],404);        
            }

            if (password_verify($data->password,$username->getPassword())) {

                // =====START GENERATE TOKEN=====                    
                $expiration = new DateTimeImmutable('+1 day');
                $customPayload = [
                    'exp' => $expiration->getTimestamp(),
                ];
                $token = $JWTManager->createFromPayload($username, $customPayload);
                // ======END GENERATE TOKEN======

                    return new JsonResponse([
                        'message' => 'Login Successfull.',
                        'id' => $username->getId(),
                        'fullname' => $username->getFirstname() . ' ' . $username->getLastname() ,
                        'username' => $username->getUsername(),
                        'email' => $username->getEmail(),
                        'isactivated' => $username->getIsactivated(),
                        'isblocked' => $username->getIsblocked(),
                        'userpicture' => $username->getUserpic(),
                        'secretkey' => $username->getSecretkey(),
                        'qrcodeurl' => $username->getQrcodeurl(),
                        'roles' => $username->getRoles(),
                        'token' => $token //$JWTManager->create($username)
                    ],200);
            } else {

                return new JsonResponse([
                    'message' => 'Invalid Password, please try again.'
                ],404);    

            }
        } else {
            return new JsonResponse([
                'message' => 'Username does not exists, please register.'
            ],404);
        }
    }
}
