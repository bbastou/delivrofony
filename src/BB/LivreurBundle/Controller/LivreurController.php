<?php

namespace BB\LivreurBundle\Controller;

use BB\LivreurBundle\Entity\Livreur;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use BB\LivreurBundle\Form\LivreurType;

class LivreurController extends Controller
{
  public function indexAction($page)
  {
    $nbPerPage = $this->container->getParameter('nb_per_page');
    // On récupère notre objet Paginator
    $listLivreurs = $this->getDoctrine()
        ->getManager()
        ->getRepository('BBLivreurBundle:Livreur')
        ->getLivreurs($page, $nbPerPage)
    ;

    $nbPages = ceil(count($listLivreurs) / $nbPerPage);

    // Si la page n'existe pas : 404
    if ($page > $nbPages && $page > 1) {
        throw $this->createNotFoundException("La page ".$page." n'existe pas.");
    }

    return $this->render('BBLivreurBundle:Livreur:index.html.twig', array(
        'listLivreurs' => $listLivreurs,
        'nbPages' => $nbPages,
        'page' => $page,
    ));
  }

  public function viewAction($id)
  {
    $em = $this->getDoctrine()->getManager();
    $livreur = $em->getRepository('BBLivreurBundle:Livreur')->find($id);
    
    if (null === $livreur) {
        throw new NotFoundHttpException("Le livreur d'id ".$id." n'existe pas.");
    }

    return $this->render('BBLivreurBundle:Livreur:view.html.twig', array(
        'livreur' => $livreur
    ));

  }

  public function addAction(Request $request)
  {
    // création objet livreur
    $livreur = new Livreur();
    $form = $this->createForm(LivreurType::class, $livreur);

    // Si la requête est en POST et que le formulaire est valide
    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {    
        $em = $this->getDoctrine()->getManager();
        // Enregistrement en BDD
        $em->persist($livreur);
        $em->flush();

        // Message
        $request->getSession()->getFlashBag()->add('notice', 'Livreur bien enregistré.');

        // Redirection vers la page du nouveau livreur
        return $this->redirectToRoute('bb_livreur_view', array('id' => $livreur->getId()));
    }

    // On passe la méthode createView() du formulaire à la vue afin qu'elle puisse afficher le formulaire toute seule
    return $this->render('BBLivreurBundle:Livreur:add.html.twig', array(
        'form' => $form->createView(),
        'pageAjout' => 1
    ));

  }

  public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $livreur = $em->getRepository('BBLivreurBundle:Livreur')->find($id);

    if (null === $livreur) {
        throw new NotFoundHttpException("Le livreur d'id ".$id." n'existe pas.");
    }

    $form = $this->createForm(LivreurType::class, $livreur);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
        $em->flush();
        $request->getSession()->getFlashBag()->add('notice', 'Livreur modifié.');
        return $this->redirectToRoute('bb_livreur_view', array('id' => $livreur->getId()));
    }

    return $this->render('BBLivreurBundle:Livreur:edit.html.twig', array(
        'livreur' => $livreur,
        'form'   => $form->createView(),
    ));
  }

  public function deleteAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();
    $livreur = $em->getRepository('BBLivreurBundle:Livreur')->find($id);

    if (null === $livreur) {
        throw new NotFoundHttpException("Le livreur d'id ".$id." n'existe pas.");
    }

    // On crée un formulaire vide, qui ne contiendra que le champ CSRF
    // Cela permet de protéger la suppression contre cette faille
    $form = $this->get('form.factory')->create();

    // Si POST et form valide
    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
        // Suppression de la BDD
        $em->remove($livreur);
        $em->flush();

        // Message
        $request->getSession()->getFlashBag()->add('info', "Le livreur a bien été supprimé.");

        // Redirection vers la liste des livreurs
        return $this->redirectToRoute('bb_livreur_home');
    }
    
    return $this->render('BBLivreurBundle:Livreur:delete.html.twig', array(
        'livreur' => $livreur,
        'form'   => $form->createView(),
    ));
  }
}