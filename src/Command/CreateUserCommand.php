<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;

class CreateUserCommand extends Command
{
    private  $io ;
    private  $EntityManagerInterface ;
    private  $UserPassportInterface ;


    protected static $defaultName = 'create-user';
    protected static $defaultDescription = 'Add a short description for your command';
    protected function configure(): void
    {
        $this
            ->setDescription('crée un utilisateur')
            ->addArgument('email', InputArgument::REQUIRED, "Email de l 'utilisateur ")
            ->addArgument('username', InputArgument::REQUIRED, "username de l 'utilisateur ")
            ->addArgument('Password', InputArgument::REQUIRED, "mot de passe de l'utilisateur ")
            ->addArgument('role', InputArgument::REQUIRED, "role de l'utilisateur ")
            ->addArgument('actif', InputArgument::REQUIRED, "statut du compte de l'utilisateur ")
        ;
    }

    public function __construct(
        EntityManagerInterface $EntityManagerInterface ,
        UserPassportInterface $UserPassportInterface  )
    {
    $this->UserPassportInterface = $EntityManagerInterface ;
        $this->UserPassportInterface = $UserPassportInterface ;

    }

   protected function interact(InputInterface $input, OutputInterface $output)
   {
      $this->io->section("crée un utilisateur");
      $this->enterEmail($input,$output);
      $this->enterUsername($input,$output);
      $this->enterPassword($input,$output);
      $this->enterRole($input,$output);
      $this->enterActif($input,$output);
   }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
       $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
        'Veuiller rentrer un email '
    );

        $email = $input->getArgument('email');

        if (email) {
            $this->io->note(sprintf('You passed an argument: %s', $email));
        }

     //

        $output->writeln(
            'Veuiller rentrer un pseudo '
        );

        $password = $input->getArgument('password');

        if (email) {
            $this->io->note(sprintf('You passed an argument: %s', $password));
        }

        $this->io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    /**
     * set user email
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function enterEmail(InputInterface $input, OutputInterface $output) : void {
        $helper = $this->getHelper('question') ;
        $emailQuestion = new Question("email de l'utilisateur");

    }

}
