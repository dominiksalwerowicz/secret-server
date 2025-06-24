<?php

namespace App\Controller;

use App\Entity\Secret;
use App\Form\SecretAddForm;
use App\Helper\ResponseFormatSelector;
use App\Repository\SecretRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/secret', name: 'secret')]
final class SecretController extends AbstractController
{
    /**
     * Fetches a Secret by its hash, even if the hash is correct it will only return a Secret, if it is
     * still valid(not expired, and has remaining views).
     * 
     * Each fetch for a secret will decrement its remaining views.
     */
    #[Route('/{hash}', name: '_get', methods: ['GET'])]
    public function get(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        string $hash
    ): Response {
        $secret = $entityManager
            ->getRepository(Secret::class)
            ->findOneBy(['hash' => $hash]);

        if (!isset($secret) || !$secret->isValid()) {
            return new Response('Secret not found', 404);
        }

        $preferredFormats = $request->getAcceptableContentTypes();
        $dataMimeType = ResponseFormatSelector::select($preferredFormats);

        if ($dataMimeType == null) {
            return new Response('Not supported response format', Response::HTTP_NOT_ACCEPTABLE);
        }

        $secret->decrementRemainingViews();
        $entityManager->flush();

        $data = $serializer->serialize($secret, $request->getFormat($dataMimeType));

        return new Response($data, 200, [
            'Content-Type' => $dataMimeType,
        ]);
    }


    /**
     * Store a Secret with the provided values, and returns the stored Secret's data.
     */
    #[Route('', name: '_add', methods: ['POST'])]
    public function add(
        Request $request,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): Response {
        $preferredFormats = $request->getAcceptableContentTypes();
        $dataMimeType = ResponseFormatSelector::select($preferredFormats);

        if ($dataMimeType == null) {
            return new Response('Not supported response format', Response::HTTP_NOT_ACCEPTABLE);
        }

        $form = $formFactory->create(SecretAddForm::class);
        $form->submit(json_decode($request->getContent(), true), true);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new Response('Invalid input', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $formData = $form->getData();

        $now = new DateTime();

        $secret = (new Secret())
            ->setHash(hash("sha256", $now->format("Y-m-h H:i:s") . $formData['secret']))
            ->setSecretText($formData['secret'])
            ->setCreatedAt($now)
            ->setExpiresAt($formData['expireAfter'])
            ->setRemainingViews($formData['expireAfterViews']);

        $entityManager->persist($secret);
        $entityManager->flush();

        $data = $serializer->serialize($secret, $request->getFormat($dataMimeType));

        return new Response($data, 200, [
            'Content-Type' => $dataMimeType,
        ]);
    }
}
