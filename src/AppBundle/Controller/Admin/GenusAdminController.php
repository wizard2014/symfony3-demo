<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Genus;
use AppBundle\Form\GenusFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 * @Security("is_granted('ROLE_MANAGE')")
 */
class GenusAdminController extends Controller
{
    /**
     * @Route("/genus", name="admin_genus_list")
     */
    public function indexAction()
    {
        $genuses = $this->getDoctrine()
                        ->getRepository('AppBundle:Genus')
                        ->findAll();

        return $this->render('admin/genus/list.html.twig', [
            'genuses' => $genuses
        ]);
    }

    /**
     * @Route("/genus/new", name="admin_genus_new")
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(GenusFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $genus = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($genus);
            $em->flush();

            $this->addFlash(
                'success',
                sprintf('Genus created by %s', $this->getUser()->getEmail())
            );

            return $this->redirectToRoute('admin_genus_list');
        }

        return $this->render('admin/genus/new.html.twig', [
            'genusForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/genus/{id}/edit", name="admin_genus_edit")
     */
    public function editAction(Request $request, Genus $genus)
    {
        $form = $this->createForm(GenusFormType::class, $genus);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $genus = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($genus);
            $em->flush();

            $this->addFlash('success', 'Genus updated');

            return $this->redirectToRoute('admin_genus_list');
        }

        return $this->render('admin/genus/edit.html.twig', [
            'genusForm' => $form->createView(),
        ]);
    }
}
