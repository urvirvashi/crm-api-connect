<?php
namespace App\Controller;

use App\DTO\SubscriberInput;
use App\Service\SubscriberService;
use App\Service\EnquiryService;
use App\Exception\ValidationException;
use App\Exception\CrmApiException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

class SignUpController extends AbstractController
{
    public function __construct(private LoggerInterface $logger) {}

    #[Route('/api/signup', name: 'signup', methods: ['POST'])]
    public function signup(
        Request $request,
        SubscriberService $subscriberService,
        EnquiryService $enquiryService,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            $input = $serializer->deserialize(
                $request->getContent(),
                SubscriberInput::class,
                'json'
            );

            $violations = $validator->validate($input);
            if (count($violations) > 0) {
                $firstError = $violations[0];
                throw new ValidationException($firstError->getMessage());
            }

            $subscriberId = $subscriberService->register($input);
            if (!empty($input->message)) {
                $enquiryService->send($subscriberId, $input->message);
            }
            return $this->json(['subscriberId' => $subscriberId], 201);
        } catch (ValidationException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (CrmApiException $e) {
            $this->logger->error('CRM API Error', ['exception' => $e]);
            return $this->json(['error' => 'Failed to process subscriber request '. $e], 502);
        } catch (\Throwable $e) {
            $this->logger->error('Unhandled error during signup', ['exception' => $e]);
            return $this->json(['error' => 'Server error'. $e], 500);
        }
    }
}
