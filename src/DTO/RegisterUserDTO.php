<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserDTO
{
    #[Assert\NotBlank(message: "Email is required")]
    #[Assert\Email(message: "Invalid email format")]
    public string $email;

    #[Assert\NotBlank(message: "Password is required")]
    #[Assert\Length(min: 6, minMessage: "Password must be at least 6 characters")]
    public string $password;
}
