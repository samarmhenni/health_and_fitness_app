<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/reservations', name: 'app_reservations')]
    public function index(): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reservation/index.html.twig', [
            'reservations' => $user->getReservations(),
        ]);
    }

    #[Route('/reservation/new/{serviceId}', name: 'app_reservation_new')]
    public function new(int $serviceId, Request $request, ServiceRepository $serviceRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $service = $serviceRepository->find($serviceId);
        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        $reservation = new Reservation();
        $reservation->setService($service);
        $reservation->setUser($user);

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réservation a été enregistrée.');

            return $this->redirectToRoute('app_reservations');
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'service' => $service,
        ]);
    }

    #[Route('/reservation/{id}', name: 'app_reservation_show')]
    public function show(Reservation $reservation): Response
    {
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
