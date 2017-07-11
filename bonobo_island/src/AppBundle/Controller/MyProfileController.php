<?php 

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class MyProfileController extends Controller
{
    /**
     * @Route("/myProfile", name="myProfile")
     */
    public function myProfileAction(SessionInterface $session, Request $request)
    {
        // Vérification de la session, si perdue, retour à l'index
        if(!$session->has('username')){
            return $this->redirectToRoute('homepage');
        }
        
        $username = $session->get('username');
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);
        
        // Si l'utilisateur client est en mode modification, création du formulaire de modification de données personnelles
        if($session->has('modify') && $session->get('modify')){
            $formModify = $this->createFormBuilder($user)
            ->add('Age', NumberType::class, array('attr' => array('maxlength' => 3), 'data' => $user->getAge()))
            ->add('Family', TextType::class, array('attr' => array('maxlength' => 16, 'data' => $user->getFamily())))
            ->add('Breed', TextType::class, array('attr' => array('maxlength' => 16, 'data' => $user->getBreed())))
            ->add('FavoriteFood', TextType::class, array('attr' => array('maxlength' => 16), 'label' => 'Favorite Food', 'data' => $user->getFavoriteFood()))
            ->add('Apply', SubmitType::class, array('label' => 'Apply'))
            ->getForm();
        }
        // S'il ne l'est pas, création d'un formulaire pour qu'il y soit
        else {
            $session->set('modify', false);
            $formModify = $this->createFormBuilder()
            ->add('Modify', SubmitType::class, array('label' => 'Modify'))
            ->getForm();
        }
            
        // Création du formulaire pour revenir au menu
        $formMenu = $this->createFormBuilder()
            ->add('BackToMenu', SubmitType::class, array('label' => 'Back To Menu'))
            ->getForm();
        
        $formModify->handleRequest($request);
        $formMenu->handleRequest($request);

        // Si mode modification, l'utilisateur valide et applique ses changements et repasse en mode normal, si non, il passe en mode modification
        if ($formModify->isSubmitted() && $formModify->isValid()) {
            if($session->get('modify')) {
                $session->set('modify', false);
                
                $user->setAge($formModify->get('Age')->getData());
                $user->setFamily($formModify->get('Family')->getData());
                $user->setBreed($formModify->get('Breed')->getData());
                $user->setFavoriteFood($formModify->get('FavoriteFood')->getData());
                
                $userManager->updateUser($user);
            }
            else $session->set('modify', true);

            return $this->redirectToRoute('myProfile');
        }
        // Si le formulaire de menu est validé, retour au menu
        else if ($formMenu->isSubmitted() && $formMenu->isValid()) {
            $session->set('modify', false);
            return $this->redirectToRoute('menu');
        }
        
        return $this->render('bonobo/myProfile.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            "user" => $user,
            "modifying" => $session->get('modify'),
            "formModify" => $formModify->createView(),
            "formMenu" => $formMenu->createView(),
            'friends' => $user->getFriends(),
            'count' => count($user->getFriends())
        ]);
    }
}

?>