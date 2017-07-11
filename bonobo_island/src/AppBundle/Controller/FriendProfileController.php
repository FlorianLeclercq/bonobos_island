<?php 

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FriendProfileController extends Controller
{
    /**
     * @Route("menu/profile/{friendUsername}", name="profile")
     */
    public function friendProfileAction($friendUsername, SessionInterface $session, Request $request)
    {
        // Vérification de la session, si perdue, retour à l'index
        if(!$session->has('username')){
            return $this->redirectToRoute('homepage');
        }
        // Vérification du profil d'ami, si c'est l'utilisateur connecté, le renvoyer sur son profil
        else if($session->get('username') == $friendUsername) {
            return $this->redirectToRoute('myProfile');
        }
        
        $userManager = $this->container->get('fos_user.user_manager');
        $friendUser = $userManager->findUserByUsername($friendUsername);
        
        // Vérification de si le profil sélectionné est déjà un ami de l'utilisateur ou non
        $user = $userManager->findUserByUsername($session->get('username'));
        $actualFriends = $user->getFriends();
        $length = count($actualFriends);
        $isFriended = false;
        for($i = 0 ; $i < $length ; $i++) {
            if($friendUser->getUsername() == $actualFriends[$i]->getUsername()) {
                $isFriended = true;
                break;
            }
        }
        
        // Si c'est un ami, création du formulaire pour ne plus l'avoir en ami
        if($isFriended) {
            $formFriend = $this->createFormBuilder()
            ->add('RemoveFriend', SubmitType::class, array('label' => 'Remove Friend'))
            ->getForm();
        }
        // Sinon, création du formulaire pour l'avoir en ami
        else {
            $formFriend = $this->createFormBuilder()
            ->add('AddFriend', SubmitType::class, array('label' => 'Add Friend'))
            ->getForm();
        }
        
        // Création du formulaire de retour au menu
        $formMenu = $this->createFormBuilder()
            ->add('BackToMenu', SubmitType::class, array('label' => 'Back To Menu'))
            ->getForm();
        
        $formFriend->handleRequest($request);
        $formMenu->handleRequest($request);
        
        // Ajout ou suppression en ami selon le formulaire choisi précédemment, puis retour sur le profil de l'utilisateur
        if($formFriend->isSubmitted() && $formFriend->isValid()) {
            if($isFriended) {
                $user->removeFriend($friendUser);
            }
            else {
                $user->addFriend($friendUser);
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($friendUser);
            $em->persist($user);
            $em->flush();
            
            return $this->redirectToRoute("myProfile");
        }
        // Si le formulaire de menu est validé, retour au menu
        else if ($formMenu->isSubmitted() && $formMenu->isValid()) {
            return $this->redirectToRoute('menu');
        }
        
        return $this->render('bonobo/profile.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'user'=>$friendUser,
            "formMenu" => $formMenu->createView(),
            "formFriend" => $formFriend->createView()
        ]);
    }
}

?>