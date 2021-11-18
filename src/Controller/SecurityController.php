<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Void_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;



class SecurityController extends AbstractController
{

private $erreur = 0;

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
     public function monProfil(Request $request ,UserPasswordHasherInterface $passwordHasher , ParticipantRepository $ParticipantRepository): Response {
         $emailUser = $this->getUser()->getUserIdentifier();
         $pseudoUser = $this->getUser()->getPseudo();
         $this->erreur = 0 ;
         $participantProfil = $this->getDoctrine()->getManager()->getRepository(Participant::class)->findOneById($this->getUser()->getId());
         $ParticipantForm = $this->createForm(ProfilType::class , $participantProfil);
         $ParticipantForm->handleRequest($request);
         dump($this->getUser()) ;

         if ($ParticipantForm->isSubmitted() && $ParticipantForm->isValid()) {



             /** @var getUsername  $file */
             $file = $ParticipantForm->get('image')->getData();




             $this->validateEmail($participantProfil->getEmail() ,$emailUser);
             $this->validatePassword($participantProfil->getPassword() );
             $this->veriftel($participantProfil->getTelephone());
             $this->validatePseudo($participantProfil->getPseudo()  ,$pseudoUser );

             if($this->erreur === 0 ) {

                 if ($file)
                 {
                     $filesystem = new Filesystem();

                     // On renomme le fichier
                     $newFilename = $participantProfil->getNom()."-".$this->getUser()->getUserIdentifier().".".$file->guessExtension();

                     //on supprime si un fichier porte le meme nom
                     $filesystem->remove($newFilename);
                     $file->move($this->getParameter('upload_champ_entite_dir'), $newFilename);
                     $participantProfil->setImage($newFilename);

                 }



             $plainPassword =  $participantProfil->getPassword();
             $hashPassword = $passwordHasher->hashPassword($participantProfil,$plainPassword);
             $participantProfil->setPassword($hashPassword);
             $entityManager = $this->getDoctrine()->getManager();
             $entityManager->persist($participantProfil);
             $entityManager->flush();
             $this->addFlash('success', 'Votre compte a été modifié avec sucess.');
             return $this->render('security/monProfil.html.twig' , ['ParticipantForm' => $ParticipantForm->createView() , 'participant'=>$participantProfil]) ;
             }
             else
                 return $this->render('security/monProfil.html.twig' , ['ParticipantForm' => $ParticipantForm->createView() , 'participant'=>$participantProfil]) ;
         }
         return $this->render('security/monProfil.html.twig' , ['ParticipantForm' => $ParticipantForm->createView() , 'participant'=>$participantProfil]) ;




     }



    public function validateEmail(string $email , string $userEmail) : void {
        $emailSearch = null ;
        if (empty($email )) {
            $this->addFlash('error', 'EMAIL VIDE  ');
            $this->erreur =+ 1 ;
        }
        if(!filter_var($email , FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'EMAIL INCORRECTE ');
            $this->erreur =+ 1 ;
        }

        $Entity = $this->getDoctrine()->getManager()->getRepository(Participant::class)->findOneBy(['email'=> $email]);
        if($Entity){
            $emailSearch = $Entity->getEmail() ;
        }
        if($email != $userEmail && $emailSearch != null  ) {
            $this->addFlash('error', 'EMAIL DEJA UTILISEE ');
            $this->erreur =+ 1 ;
        }
    }


    public function validatePseudo(string $pseudo , string $userPseudo) : void {
        $pseudoSearch = null ;

        if (empty($pseudo )) {
            $this->addFlash('error', 'PSEUDO VIDE  ');
            $this->erreur =+ 1 ;
        }
                $Entity = $this->getDoctrine()->getManager()->getRepository(Participant::class)->findOneBy(['pseudo'=> $pseudo]);
                if($Entity){
                    $pseudoSearch = $Entity->getPseudo() ;
                }

        if($pseudo != $userPseudo && $pseudoSearch != null  ) {
            $this->addFlash('error', 'PSEUDO DEJA UTILISEE ');
            $this->erreur =+ 1 ;
        }
    }


    public function validatePassword(string $password ) : void {
        $passwordRegex = "^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$^" ;



        if (empty($password )) {
            $this->addFlash('error', 'mot de passe vide ');
            $this->erreur =+ 1 ;
        }

        if(!preg_match( $passwordRegex ,  $password ))  {
            $this->addFlash('error', 'LE MOT DE PASSE DOIT CONTENIR 8 CARACTERE AU MINIMUM 
            : DONT UNE LETTRE MAJUSCULE
            , UNE LETTRE MINUSCULE 
            , ET UN NOMBRE ');
            $this->erreur =+ 1 ;


        }
    }




    public function veriftel(?string $tel) : void {
      $telRegex= "^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$^";
        if (empty($tel )) {
            $this->addFlash('error', 'LE NUMERO DE TELEPHONE EST 10 CHIFFRE MINIMUM');
            $this->erreur =+ 1 ;
        }
        if(!preg_match( $telRegex ,  $tel ))  {
            $this->addFlash('error', 'LE NUMERO DE TELEPHONE EST 10 CHIFFRE MINIMUM');
            $this->erreur =+ 1 ;
        }


    }





}
