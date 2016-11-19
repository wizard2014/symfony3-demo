<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Genus;
use AppBundle\Entity\GenusNote;
use AppBundle\Service\MarkdownTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/genus/new")
     */
    public function newAction()
    {
        $genus = new Genus();
        $genus->setName('Octopus' . rand(1, 100));
        $genus->setSubFamily('Octopodinae');
        $genus->setSpeciesCount(rand(100, 99999));

        $genusNote = new GenusNote();
        $genusNote->setUsername('User');
        $genusNote->setUserAvatarFilename('ryan.jpeg');
        $genusNote->setNote('Lorem Ipsum is simply dummy text of the printing and typesetting industry.');
        $genusNote->setCreatedAt(new \DateTime('-1 month'));
        $genusNote->setGenus($genus);

        $em = $this->getDoctrine()->getManager();
        $em->persist($genus);
        $em->persist($genusNote);
        $em->flush();

        return new Response('Genus created!');
    }

    /**
     * @Route("/genus")
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();

        $genuses = $em->getRepository('AppBundle:Genus')
                        ->findAllPublishedOrderedRecentlyActive();

        return $this->render('default/list.html.twig', [
            'genuses' => $genuses,
        ]);
    }

    /**
     * @Route("/genus/{name}", name="genus_show")
     */
    public function showAction($name)
    {
        $em = $this->getDoctrine()->getManager();
        $genus = $em->getRepository('AppBundle:Genus')
                    ->findOneBy(['name' => $name]);

        if (!$genus) {
            throw $this->createNotFoundException();
        }

        $transformer = $this->get('app.markdown_transformer');
        $funFact = $transformer->parse($genus->getFunFact());

        /*$funFact = $genus->getFunFact();

        $cache = $this->get('doctrine_cache.providers.my_markdown_cache');
        $key = md5($funFact);

        if ($cache->contains($key)) {
            $funFact = $cache->fetch($key);
        } else {
            $funFact = $this->get('markdown.parser')
                            ->transform($funFact);

            $cache->save($key, $funFact);
        }*/

        $recentNotes = $em->getRepository('AppBundle:GenusNote')
                            ->finaAllRecentNotesForGenus($genus);

        return $this->render('default/show.html.twig', [
            'genus'           => $genus,
            'funFact'         => $funFact,
            'recentNoteCount' => count($recentNotes),
        ]);
    }

    /**
     * @Route("/genus/{name}/notes", name="genus_show_notes")
     * @Method("GET")
     */
    public function getNotesAction(Genus $genus)
    {
        $notes = [];

        foreach ($genus->getNotes() as $note) {
            $notes[] = [
                'id'        => $note->getId(),
                'username'  => $note->getUsername(),
                'avatarUri' => '/images/' . $note->getUserAvatarFilename(),
                'note'      => $note->getNote(),
                'date'      => $note->getCreatedAt()->format('Y-m-d'),
            ];
        }

        return new JsonResponse(['notes' => $notes]);
    }
}
