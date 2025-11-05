<?php

namespace App\Controller;

use Psr\Log\LoggerInterface; // You can inject other services too


use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCodeBundle\Response\QrCodeResponse;

use OTPHP\TOTP;
use Psr\Clock\ClockInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface ;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface; // To access the current user's token.
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use App\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface; // Your User entity should implement this


final class UserController extends AbstractController
{    
    private TotpAuthenticatorInterface $totpAuthenticator;
    private $tokenStorage;
    private BuilderInterface $qrCodeBuilder;
    private ClockInterface $clock;

    public function __construct(
        TotpAuthenticatorInterface $totpAuthenticator,
        ClockInterface $clock,
        TokenStorageInterface $tokenStorage,
        BuilderInterface $qrCodeBuilder)
    {
        $this->totpAuthenticator = $totpAuthenticator;
        $this->qrCodeBuilder = $qrCodeBuilder;
        $this->clock = $clock;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/api/getallusers', name: 'app_users', methods: ['GET'])]
    public function getUsers(
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): JsonResponse
    {

        try {
            $query = $em->createQuery(
                'SELECT u.id,u.firstname,u.lastname,u.mobile,u.isactivated,u.isblocked,u.userpic
                FROM App\Entity\User u
                ORDER BY u.id ASC'
            );
    
            $users = $query->getResult();
            return new JsonResponse(['users' => $users],200);
        
        } catch(\Exception) {
            return new JsonResponse(['message' => 'Unauthorized Access.'], 401);
        }
    }

    #[Route('/api/getuserid/{id}', name: 'app_userid', methods: ['GET'])]
    public function getUserId(
        int $id,
        EntityManagerInterface $em
    ): JsonResponse
    {

        // if (null === $this->getUser()) {
        //     return new JsonResponse(['message' => 'User is not Logged in...'],500);
        //     // User is not logged in (anonymous user)
        // } else {
        //     return new JsonResponse(['message' => 'User Logged in...'],200);
        //     // User is logged in
        // }


        $query = $em->createQuery(
            'SELECT u.id,u.firstname,u.lastname,u.email,u.mobile,u.qrcodeurl,u.roles,u.isactivated,u.isblocked,u.userpic
            FROM App\Entity\User u
            WHERE u.id = :id
            ORDER BY u.id ASC'
        )->setParameter('id', $id);
        if ($query->getResult()) {
            return new JsonResponse(['message' => 'User profile retrieved.' ,'user' => $query->getResult()], 200);
        } else {
            return new JsonResponse(['message' => 'User ID not found.'],404);
        }
    }

    #[Route('/api/updateuser', name: 'app_updateuser', methods: ['PATCH'])]
    public function updateUser(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository        
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        $user = $em->getRepository(User::class)->find($data->id);
        if ($user) {
            $user->setFirstname($data->getUsername());
            $user->setLastname($data->getLastname());
            $user->setMobile($data->getMobile());
            $em->flush();
            return new JsonResponse(['message' => 'User Profile has been updated.'], 200);
        } else {
            return new JsonResponse(['message' => 'User ID not found.'], 404);
        }
    }

    #[Route('/api/changeuserpassword/{id}', name: 'app_changeuserpassword', methods: ['PATCH'])]
    public function updateUserpassword(
        Request $request,
        int $id,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository        
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        $user = $em->getRepository(User::class)->find($id);
        if ($user) {            
            $plaintextPassword = $data->password;
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);
            $em->flush();
            return new JsonResponse(['message' => 'Your Password has been changed successfully.'], 200);
        } else {
            return new JsonResponse(['message' => 'User ID not found.'], 404);
        }
    }


    #[Route('/api/uploadpicture', name: 'app_updateuserpic', methods: ['POST'])]
    public function updateUserpic(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        UserRepository $userRepository        
    ): JsonResponse
    {
        $data = json_decode($request->getContent());

        $idValue = $request->request->get('id');        
        $user = $em->getRepository(User::class)->find($idValue);
        if (!$user) {
            return new JsonResponse(['message' => 'User ID not found.'], 404);
        }
        $uploadedFile = $request->files->get('userpic');

        if ($uploadedFile) {
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = $uploadedFile->guessExtension();

            $newfile = '00' . $idValue . '.' . $ext;

            $projectDir = $this->getParameter('kernel.project_dir');
            $usersPublicPath = $projectDir . '/public/users/';

            $uploadedFile->move($usersPublicPath, $newfile);
            $pic = '/users/' . $newfile;
            $user->setUserpic($pic);
            $em->flush();
            return new JsonResponse(['message' => 'Your Profile Picture has been changed.', 'userpic' => $pic], 200);
        }        
    }

    #[Route('/api/activateuser/{id}', name: 'app_activateuser', methods: ['PATCH'])]
    public function activateUser(
        int $mailtoken,
        EntityManagerInterface $em,
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $JWTManager    
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        $username = $em->getRepository(User::class)->findOneBy(['mailtoken' => $token]);
        if ($username) {
            $username->setIscactivated($data->getIscativated());
            $em->flush();
            return new JsonResponse(['message' => 'User has been activated.'],200);
        } else {
            return new JsonResponse(['message' => 'User ID not found.'], 404);
        }
    }    

    #[Route('/api/blockuser/{id}', name: 'app_blockuser', methods: ['PATCH'])]
    public function blockuser(
        int $id,
        EntityManagerInterface $em,
        Request $request,
        UserRepository $userRepository
    ): JsonResponse
    {
        $data = json_decode($request->getContent());
        $user = $em->getRepository(User::class)->find($id);
        if ($user) {
            $user->setIsblocked($data->getIsblocked());
            $em->flush();
            return new JsonResponse(['message' => 'User has been blocked.'], 200);
        } else {
            return new JsonResponse(['message' => 'User ID not found.'], 404);
        }
    }

    #[Route('/api/deleteuser/{id}', name: 'app_deleteuser', methods: ['DELETE'])]
    public function deleteUser(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);
        if ($user) {
            $em->remove($user);
            $em->flush();
            return new JsonResponse(['message' => 'User has been deleted.'], 200);
        }
        return new JsonResponse(['message' => 'User ID not found.'],404);        
    }

    #[Route('/api/enablemfa/{id}', name: 'generate_qrcode_base64', methods: ['PATCH'])]
    public function enableMfa(
        Request $request,
        int $id,
        EntityManagerInterface $em,
        UserRepository $userRepository        
    ): JsonResponse
    {
    
        $data = json_decode($request->getContent());
        $user = $em->getRepository(User::class)->find($id);
        if ($user) {

            if ($data->Twofactorenabled == false) {
                $user->setTotpSecret(null);
                $user->setQrcodeurl(null);
                $em->flush();
                return new JsonResponse(['message' => 'Multi-Factor Authenticator has been Disabled...'], 200);
            } else {
                    
                    //this will generate Secret Key
                    $secret = $this->totpAuthenticator->generateSecret();

                    //VALID FORMAT: otpauth://totp/ISSUER:ACCOUNTNAME?secret=SECRETKEY&issuer=ISSUER
                    $issuer = 'SUPERCAR INC.';
                    $accountName = $user->getEmail();
                    $secret = $secret; // Base32 encoded secret key
            
                    // Encode components for the VALID URI
                    $encodedIssuer = urlencode($issuer);
                    $encodedAccountName = urlencode($accountName);
            
                    // Construct the otpauth URI, DONT CHANGE THE otpauth://totp/%s:%s?secret=%s&issuer=%s
                    $uri = sprintf(
                        'otpauth://totp/%s:%s?secret=%s&issuer=%s',
                        $encodedIssuer,
                        $encodedAccountName,
                        $secret,
                        $encodedIssuer
                    );

                    $result = $this->qrCodeBuilder->build(
                        data: $uri,
                        size: 200,
                        margin: 10
                    );
                    
                    // Get the Base64 data URI
                    $dataUri = $result->getDataUri();     

                    //Update database
                    $user->setQrcodeurl($dataUri);
                    $user->setTotpSecret($secret);  
                    $em->flush();    

                return new JsonResponse(['message' => 'Multi-Factor Authenticator has been Enabled...', 'qrcodeurl' => $dataUri], 200);
            }
        } 
        return new JsonResponse(['message' => 'User ID not found.'], 404);        
    }

    #[Route('/api/otpvalidation', name: 'app_verify_2fa_manual', methods: ['POST'])]
    public function validateOtp(
        Request $request,
        TokenStorageInterface $tokenStorage,
        TwoFactorProviderRegistry $providerRegistry,
        EventDispatcherInterface $eventDispatcher,        
        EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent());

        $uid = $data->id;
        $otp = $data->otp;
        $user = $this->getUser();

        if ($user) {            
            if ($user->isTwoFactorAuthEnabled()) {

                if (!$user instanceof UserInterface || !$user->isTotpAuthenticationEnabled()) {
                    return new JsonResponse(['message' => 'TOTP authentication is not enabled for this user.'],200);
                } 

                $totp = TOTP::create($user->getTotpSecret());
                if ($totp->verify($otp)) {
                    return new JsonResponse(['message' => 'Successfull OTP code validation.', 'username' => $user->getUsername()], 200);
                } else {
                    return new JsonResponse(['message' => 'OTP Code is not valid.'], 404);
                }

            } else {
                return new JsonResponse(['message' => 'Sorry, MFA is not available for now..'],404);
            }
        } else {
            return new JsonResponse(['message' => 'Sorry, MFA is not available for now..'],404);
        }

    }

    #[Route('/app_logout', name: 'app_logout', methods: ['ANY'])]
    public function logout(Security $security, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $session->clear(); // Clears all session attributes        
        $security->logout(); // This logs out the user and invalidates the session
        return $this->redirectToRoute('app_home');        
    }
  
}
