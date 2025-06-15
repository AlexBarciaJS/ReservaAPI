<?php

namespace App\Controller;

use App\DTO\RegisterUserDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/register',
        summary: 'Registro de nuevo usuario',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'alex@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: '123456')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Usuario creado exitosamente'),
            new OA\Response(response: 400, description: 'Error de validación')
        ]
    )]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var RegisterUserDTO $dto */
        $dto = $serializer->deserialize($request->getContent(), RegisterUserDTO::class, 'json');

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], 400);
        }

        $user = new User();
        $user->setEmail($dto->email);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $passwordHasher->hashPassword($user, $dto->password)
        );

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'User registered successfully'], 201);
    }

    // Documentación falsa para Swagger (login es manejado por Symfony internamente)
    #[Route('/api/login', name: 'api_login_doc', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login',
        summary: 'Autenticación de usuario',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'alex@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: '123456')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Autenticación exitosa',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLC...')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Credenciales inválidas')
        ]
    )]
    public function loginDoc(): JsonResponse
    {
        // Método ficticio: solo para documentación Swagger
        return new JsonResponse(null, 401);
    }
}
