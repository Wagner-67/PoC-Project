<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use App\Entity\PasswordResetToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;


final class UserController extends AbstractController
{
    #[Route('/api/greetings', methods: ['GET'])]
    public function message(): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return new JsonResponse([
        'message'=>'Hello World!',
        ]);
    }

    #[Route('/api/user_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

       if (empty($data['email']) || empty($data['password']) || empty($data['password_confirmation']) || empty($data['name'])) {
            return new JsonResponse(['error'=>'All fields are required']);
       }

       if($data['password'] !== $data['password_confirmation']) {
            return new JsonResponse(['error'=>'Password and Confirmation dont match!']);
       }

        $user = $em->getRepository(User::class)->findOneBy(['email'=>$data['email']]);

        if(!$user) {

        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        $hashed = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashed);

        return new JsonResponse(['message' => 'User registered successfully']);

        }
        
        return new JsonResponse(['error'=>'This E-Mail already exist']);
    }

    #[Route('/api/password_reset_Link', methods: ['POST'])]
    public function send_email(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (empty($data['email'])) {
            return new JsonResponse(['error' => 'Email is required'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        $Response = ['message' => 'If your email is registered, you will receive a password reset email from us. Please also check your spam folder.'];

        if (!$user) {
            return new JsonResponse($Response);
        }

        $token = bin2hex(random_bytes(32));

        $reset = new PasswordResetToken();
        $reset->setUser($data['email']);
        $reset->setToken($token);
        $reset->setExpiredAt(new \DateTimeImmutable('+15 minutes'));
        $reset->setUsed(false);

        $email = (new Email())
        ->from('p73583347@gmail.com')
        ->to($data['email'])
        ->subject('Passwort Reset')
        ->html("
            <p>Your Password Reset Link is below!</p>
            <p>If you didn’t request a password reset, just ignoreabout:blank#blockedbeb this message.</p>
            <p><a href=\"https://example.com/reset-password?token=$token\">Reset your password</a></p>
            <p>Thank you!</p>
        ");

        $em->persist($reset);
        $em->flush();

        $mailer->send($email);
        return new JsonResponse($Response);
    }
#[Route('/api/reset_password', methods: ['POST'])]
public function reset(
    Request $request,
    EntityManagerInterface $em,
    UserPasswordHasherInterface $passwordHasher
): JsonResponse {

    $token = $request->query->get('token');
    $data = json_decode($request->getContent(), true);

    if (!$token) {
        return new JsonResponse(['error' => 'Token is missing.'], 400);
    }

    if (empty($data['password']) || empty($data['password_confirmation'])) {
        return new JsonResponse(['error' => 'Password and confirmation are required.'], 400);
    }

    if ($data['password'] !== $data['password_confirmation']) {
        return new JsonResponse(['error' => 'Passwords do not match.'], 400);
    }

    $resetToken = $em->getRepository(PasswordResetToken::class)->findOneBy(['token' => $token]);

    if (!$resetToken) {
        return new JsonResponse(['error' => 'Invalid token.'], 400);
    }

    if ($resetToken->isUsed()) {
        return new JsonResponse(['error' => 'Token has already been used.'], 400);
    }

    $user = $em->getRepository(User::class)->findOneBy(['email' => $resetToken->getUser()]);

    if (!$user) {
        return new JsonResponse(['error' => 'User not found.'], 404);
    }

    $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
    $user->setPassword($hashedPassword);

    $resetToken->setUsed(true);

    $em->flush();

    return new JsonResponse(['message' => 'Password has been successfully reset.']);
}

}
