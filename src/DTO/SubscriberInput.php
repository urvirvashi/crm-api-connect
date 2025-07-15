<?php
namespace App\DTO;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class SubscriberInput
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[SerializedName('emailAddress')]
    public string $email;

    public ?string $firstName = null;
    public ?string $lastName = null;

    #[Assert\NotBlank]
    #[SerializedName('dateOfBirth')]
    public \DateTimeImmutable $dateOfBirth;

    #[SerializedName('marketingConsent')]
    public bool $marketingConsent = false;

    public ?string $message = null;
}
