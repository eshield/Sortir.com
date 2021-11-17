<?php

namespace App\Command;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use App\utils\CustomValidator;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommand extends Command
{
    private  $io ;
    private  $EntityManager ;
    private  $passwordHasher ;
    private  $ParticipantRepository ;
    private  $Validator  ;

    protected static $defaultName = 'create-user';
    protected static $defaultDescription = 'cette commande permet de crée un utilisateur';
    protected function configure(): void
    {
        $this
            ->setDescription('crée un utilisateur')
            ->addArgument('email', InputArgument::REQUIRED, "Email de l 'utilisateur ")
            ->addArgument('username', InputArgument::REQUIRED, "username de l 'utilisateur ")
            ->addArgument('Password', InputArgument::REQUIRED, "mot de passe de l'utilisateur ")
            ->addArgument('role', InputArgument::REQUIRED, "role de l'utilisateur ")
            ->addArgument('actif', InputArgument::REQUIRED, "statut du compte de l'utilisateur ")
            ->addArgument('administrateur', InputArgument::REQUIRED, "statut du compte de l'utilisateur ")
        ;
    }

    public function __construct(
        CustomValidator $Validator ,
        EntityManagerInterface $EntityManager ,
        UserPasswordHasherInterface $passwordHasher  ,
        ParticipantRepository $ParticipantRepository

    )
    {

    $this->EntityManager = $EntityManager ;
    $this->passwordHasher = $passwordHasher ;
    $this->ParticipantRepository = $ParticipantRepository ;
    $this->Validator = $Validator ;
    parent::__construct() ;

    }

   protected function interact(InputInterface $input, OutputInterface $output)
   {
      $this->io->section("crée un utilisateur");
      $this->enterEmail($input,$output);
      $this->enterUsername($input,$output);
      $this->enterPassword($input,$output);
      $this->enterRole($input,$output);
      $this->enterActif($input,$output);
       $this->enterAdministrateur($input,$output);

   }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
       $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $email */
        $email = $input->getArgument('email');

        /** @var string $Password */
        $Password = $input->getArgument('Password');

        /** @var array<string> $role */
        $role = [$input->getArgument('role')];

        /** @var string $username */
        $username = $input->getArgument('username');

        /** @var boolean $actif */
        $actif = $input->getArgument('actif');

        /** @var boolean $admin */
        $admin = $input->getArgument('administrateur');

        $Participant = new Participant();

        //hash le password
        $hashPassword = $this->passwordHasher->hashPassword($Participant ,$Password);



        $Participant ->setEmail($email)
                     ->setPassword($hashPassword)
                     ->setRoles($role)
                     ->setUsername($username)
                     ->setActif($actif)
                     ->setAdministrateur($admin);



        $this->EntityManager->persist($Participant);
        $this->EntityManager->flush();
        $this->io->success('UN NOUVEL UTILISATEUR A ETE AJOUTEE AVEC SUCCESS') ;



        return Command::SUCCESS;
    }

    /**
     * set participant email
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterEmail(InputInterface $input, OutputInterface $output) : void {
        $helper = $this->getHelper('question') ;
        $emailQuestion = new Question("email de l'utilisateur : ");
        $emailQuestion->setValidator([$this->Validator,'validateEmail']);
        $email = $helper->ask($input , $output ,$emailQuestion ) ;
        if($this->isUserAlreadyExist($email)){
            throw new RuntimeException(sprintf('utilisateur déja present avec cette email : %s',$email));
        }
        $input->setArgument('email' , $email);

    }

    /**
     * set participant password
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterPassword(InputInterface $input, OutputInterface $output) : void {
        $helper = $this->getHelper('question') ;
        $PasswordQuestion = new Question("mot de passe de l'utilisateur : ");
        $PasswordQuestion->setValidator([$this->Validator,'validatePassword']);
         // cache le password
        $PasswordQuestion->setHidden(true)
                         ->setHiddenFallback(false);

        $Password = $helper->ask($input , $output ,$PasswordQuestion ) ;
        $input->setArgument('Password' , $Password);

    }

    /**
     * set participant role
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterRole(InputInterface $input, OutputInterface $output) : void {
        $helper = $this->getHelper('question') ;
        $roleQuestion = new ChoiceQuestion("SELECTIONNER LE ROLE DE L'UTILISATEUR : ['ROLE_USER'] " ,['ROLE_USER'] , 'ROLE_USER');
        $roleQuestion->setErrorMessage('ROLE UTILISATEUR INVALIDE');
        $role = $helper->ask($input , $output ,$roleQuestion ) ;
        $output->writeln("<info>role de l'utilisateur choisit : {$role}</info>") ;
        $input->setArgument('role' , $role);
    }

    /**
     * set participant username
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterUsername(InputInterface $input, OutputInterface $output) : void {
        $helper = $this->getHelper('question') ;
        $UsernameQuestion = new Question("username de l'utilisateur : ");
        $UsernameQuestion->setValidator([$this->Validator,'validateUsername']);
        $username = $helper->ask($input , $output ,$UsernameQuestion ) ;
        if($this->isUsernameAlreadyExist($username)){
            throw new RuntimeException(sprintf('utilisateur déja present avec cette username : %s',$username));
        }
        $input->setArgument('username' , $username);

    }

    /**
     * set participant actif
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterActif(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('question') ;
        $ActifQuestion = new ChoiceQuestion("SELECTION DE L'ACTIVITE DE L'UTILISATEUR : ['ACTIF'] "
            , ['INACTIF','ACTIF']
            ,'ACTIF') ;
        $ActifQuestion->setErrorMessage('STATUT UTILISATEUR INVALIDE');
        $Actif = $helper->ask($input , $output ,$ActifQuestion ) ;
        $output->writeln("<info>role de l'utilisateur choisit : {$Actif}</info>") ;
        $input->setArgument('actif' , $Actif);

    }

    /**
     * set participant administrateur
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterAdministrateur(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('question') ;
        $ActifAdmin = new ChoiceQuestion("SELECTIONNE SI L'UTILISATEUR EST UN ADMIN : ['NON'] "
            , ['NON','OUI']
            ,'NON') ;
        $ActifAdmin->setErrorMessage('STATUT UTILISATEUR INVALIDE');
        $admin = $helper->ask($input , $output ,$ActifAdmin ) ;
        $output->writeln("<info>role de l'utilisateur choisit : {$admin}</info>") ;

        $input->setArgument('administrateur' , $admin);

    }



    private function isUsernameAlreadyExist(String $username): ?user
    {
        return $this->ParticipantRepository->findOneBy(['username' => $username ]);
    }


    private function isUserAlreadyExist(String $email): ?user
    {
        return $this->ParticipantRepository->findOneBy(['email' => $email ]);
    }
}
