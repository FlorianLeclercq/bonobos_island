<?php 

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MenuController extends Controller
{
    /**
     * @Route("/menu", name="menu")
     */
    public function menuAction(SessionInterface $session, Request $request)
    {
        // Vérification de la session, si perdue, retour à l'index
        if(!$session->has('username')){
            return $this->redirectToRoute('homepage');
        }
        
        $username = $session->get('username');
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        
        // Création du formulaire pour accéder au profil
        $formProfile = $this->createFormBuilder()
            ->add('MyProfile', SubmitType::class, array('label' => 'My Profile'))
            ->getForm();
        
        // Création du formulaire pour se déconnecter
        $formDisconnection = $this->createFormBuilder()
            ->add('Disconnect', SubmitType::class, array('label' => 'Disconnect'))
            ->getForm();
        
        $formDisconnection->handleRequest($request);
        $formProfile->handleRequest($request);
        
        // On va soit au profil soit à l'index en se connectant selon le formulaire choisi
        if ($formProfile->isSubmitted() && $formProfile->isValid()) {
            return $this->redirectToRoute('myProfile');
        }
        else if ($formDisconnection->isSubmitted() && $formDisconnection->isValid()) {
            $session->clear();
            return $this->redirectToRoute('homepage');
        }
        
        return $this->render('bonobo/menu.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            "username" => $username,
            "users" => $users,
            "formDisconnection" => $formDisconnection->createView(),
            "formProfile" => $formProfile->createView()
        ]);
    }
}

?>