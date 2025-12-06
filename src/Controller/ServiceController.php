<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class ServiceController extends AbstractController
{
    #[Route('/services', name: 'app_services')]
    public function index(ServiceRepository $repo): Response
    {
        $services = $repo->findAll();

        return $this->render('service/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/service/new', name: 'app_service_new')]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/services';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }

                $imageFile->move($targetDir, $newFilename);

                $service->setImage('/uploads/services/'.$newFilename);
            }

            $em->persist($service);
            $em->flush();

            $this->addFlash('success', 'Service créé avec succès');
            return $this->redirectToRoute('app_services');
        }

        return $this->render('service/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/service/{id}', name: 'app_service_show', requirements: ['id' => '\d+'])]
    public function show(int $id, ServiceRepository $repo): Response
    {
        $service = $repo->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Service non trouvé');
        }

        return $this->render('service/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/service/{id}/edit', name: 'app_service_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs')]
    public function edit(int $id, Request $request, ServiceRepository $repo, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $service = $repo->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Service non trouvé');
        }

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                // Delete old image if exists
                $oldImage = $service->getImage();
                if ($oldImage) {
                    $oldImagePath = $this->getParameter('kernel.project_dir').'/public'.$oldImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/services';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }

                $imageFile->move($targetDir, $newFilename);
                $service->setImage('/uploads/services/'.$newFilename);
            }

            $em->flush();

            $this->addFlash('success', 'Service modifié avec succès');
            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
        }

        return $this->render('service/edit.html.twig', [
            'form' => $form->createView(),
            'service' => $service,
        ]);
    }

    #[Route('/service/{id}/delete', name: 'app_service_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs')]
    public function delete(int $id, Request $request, ServiceRepository $repo, EntityManagerInterface $em): Response
    {
        $service = $repo->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Service non trouvé');
        }

        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            // Delete image file if exists
            $image = $service->getImage();
            if ($image) {
                $imagePath = $this->getParameter('kernel.project_dir').'/public'.$image;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $em->remove($service);
            $em->flush();

            $this->addFlash('success', 'Service supprimé avec succès');
        }

        return $this->redirectToRoute('app_services');
    }
}
