<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MainController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, SessionInterface $session)
    {
        // Création de l'utilisateur anonyme
        $user = new User();
        $userManager = $this->container->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        
        
        // Création ddu formulaire d'inscription
        $formRegister = $this->createFormBuilder($user)
            ->add('Username', TextType::class, array('attr' => array('maxlength' => 16)))
            ->add('Password', PasswordType::class, array('attr' => array('maxlength' => 12)))
            ->add('Email', TextType::class)
            ->add('Register', SubmitType::class, array('label' => 'Registration'))
            ->getForm();
        
        // Création ddu formulaire de connexion
        $formConnection = $this->createFormBuilder($user)
            ->add('Username', TextType::class, array('attr' => array('maxlength' => 16)))
            ->add('Password', PasswordType::class, array('attr' => array('maxlength' => 12)))
            ->add('Connect', SubmitType::class, array('label' => 'Connection'))
            ->getForm();
        
        $formRegister->handleRequest($request);
        $formConnection->handleRequest($request);

        // Vérification du login et du mot de passe, si valide, connexion
        if ($formConnection->isSubmitted() && $formConnection->isValid()) {
            $user = $formConnection->getData();
            
            $length = count($users);
            for($i = 0 ; $i < $length ; $i++) {
                if($users[$i]->getUsername() == $user->getUsername() && $users[$i]->getPassword() == $user->getPassword()) {
                    $session->set('username', $user->getUsername());
                    return $this->redirectToRoute('menu');
                }
            }
            return $this->redirectToRoute('homepage');
        }
        // Vérification du login existant ou non, si non, création d'un nouvel utilisateur, puis connexion
        else if ($formRegister->isSubmitted() && $formRegister->isValid()) {
            $user = $formRegister->getData();
            
            $length = count($users);
            for($i = 0 ; $i < $length ; $i++) {
                if($users[$i]->getUsername() == $user->getUsername()) {
                    return $this->redirectToRoute('homepage');
                }
            }
            
            $session->set('username', $user->getUsername());
             
            $newUser = $userManager->createUser($user);
            
            $newUser->setUsername($user->getUsername());
            $newUser->setPassword($user->getPassword());
            $newUser->setEmail($user->getEmail());
            
            $userManager->updateUser($newUser);

            return $this->redirectToRoute('menu');
        }
        
        return $this->render('bonobo/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'formConnection' => $formConnection->createView(),
            'formRegister' => $formRegister->createView()
        ]);
    }
}
