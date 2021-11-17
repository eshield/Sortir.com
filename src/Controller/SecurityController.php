<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;



class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

      if ($this->getUser()) {
          return $this->redirectToRoute('/');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @route("/monProfil" , name="app_monProfil")
     */
     public function monProfil(Request $request ): Response {


         $Participant = new Participant() ;
         $ParticipantForm = $this->createForm(ProfilType::class , $Participant);
         $ParticipantForm->handleRequest($request);


         if ($ParticipantForm->isSubmitted() && $ParticipantForm->isValid()) {

             /** @var getUsername  $file */
             $file = $ParticipantForm->get('image')->getData();
             if ($file)
             {
                 // On renomme le fichier
                 $newFilename = $Participant->getNom()."-".$this->getUser()->getUserIdentifier().".".$file->guessExtension();
                 $file->move($this->getParameter('upload_champ_entite_dir'), $newFilename);
                 $Participant->setChamp($newFilename);
             }
             $entityManager = $this->getDoctrine()->getManager();
             $entityManager->persist($file);
             $entityManager->flush();
             $this->addFlash('success', 'Votre compte a été modifié avec sucess.');
             return $this->redirectToRoute('app_monProfil') ;
         }
         return $this->render('security/monProfil.html.twig' , ['ParticipantForm' => $ParticipantForm->createView() ]) ;




     }




}
