<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
          return $this->redirectToRoute('app_monProfil');
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
     public function monProfil(Request $request ,EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response {

         $participantProfil = $this->getDoctrine()->getManager()->getRepository(Participant::class)->findOneById($this->getUser()->getId());
         dump($participantProfil->getPseudo());
         $ParticipantForm = $this->createForm(ProfilType::class , $participantProfil);
         $ParticipantForm->handleRequest($request);


         if ($ParticipantForm->isSubmitted() && $ParticipantForm->isValid()) {

             /** @var getUsername  $file */
             $file = $ParticipantForm->get('image')->getData();
             if ($file)
             {

                 // On renomme le fichier
                 $newFilename = $participantProfil->getNom()."-".$this->getUser()->getUserIdentifier().".".$file->guessExtension();
                 $file->move($this->getParameter('upload_champ_entite_dir'), $newFilename);
                 $participantProfil->setImage($newFilename);

             }
             $this->validateEmail($participantProfil->getEmail());
             $this->validatePassword($participantProfil->getPassword());








             $plainPassword =  $participantProfil->getPassword();
             $hashPassword = $passwordHasher->hashPassword($participantProfil,$plainPassword);
             $participantProfil->setPassword($hashPassword);
             $entityManager = $this->getDoctrine()->getManager();
             $entityManager->persist($participantProfil);
             $entityManager->flush();
             $this->addFlash('success', 'Votre compte a été modifié avec sucess.');
             return $this->render('security/monProfil.html.twig' , ['ParticipantForm' => $ParticipantForm->createView() , 'participant'=>$participantProfil]) ; ;
         }
         return $this->render('security/monProfil.html.twig' , ['ParticipantForm' => $ParticipantForm->createView() , 'participant'=>$participantProfil]) ;




     }



    public function validateEmail(?string $email) : string {

        if (empty($email )) {
            throw new InvalidArgumentException('VEUILLER SAISIR UN EMAIL.');
        }
        if(!filter_var($email , FILTER_VALIDATE_EMAIL)) {
            throw new Exception('EMAIL SAISIE INVALIDE.');
        }
        return $email ;
    }

    public function validatePassword(?string $password) : string {

        if (empty($password )) {
            throw new InvalidArgumentException('VEUILLER SAISIR UN MOT DE PASSE.') ;
            throw new Exception('EMAIL SAISIE INVALIDE.');
            return $this->render('security/monProfil.html.twig' , ['ParticipantForm' => $ParticipantForm->createView() , 'participant'=>$participantProfil,'error' => $error]) ; ;

        }

        $passwordRegex = "^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$^" ;
        if(preg_match( $passwordRegex ,  $password )) {
            throw new InvalidArgumentException('LE MOT DE PASSE DOIT CONTENIR 8 CARACTERE AU MINIMUM 
            : DONT UNE LETTRE MAJUSCULE
            , UNE LETTRE MINUSCULE 
            , ET UN NOMBRE ');
            return $this->render('security/monProfil.html.twig' , ['ParticipantForm' => $ParticipantForm->createView() , 'participant'=>$participantProfil,'error' => $error]) ; ;
        }
        return $password  ;
    }


    public function validatePseudo(?string $pseudo) : string {

        if (empty($pseudo )) {
            throw new InvalidArgumentException('VEUILLER SAISIR UN USERNAME.') ;
        }


        return $pseudo  ;
    }






}
