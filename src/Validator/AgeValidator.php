<?php
namespace App\Validator;

use DateTimeInterface;
use App\Exception\ValidationException;

class AgeValidator
{
    public function ensureAdult(DateTimeInterface $dob, int $age = 18, ?DateTimeInterface $referenceDate = null): void
    {
        $referenceDate ??= new \DateTimeImmutable();
        if ($dob->diff($referenceDate)->y < $age) {
            throw new ValidationException("Subscriber must be at least {$age} years old.");
        }
    }
}
