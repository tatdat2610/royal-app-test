<?php

namespace App\Command;

use App\Service\ApiConnector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:add-author',
    description: 'Create a new author',
)]
class AddAuthorCommand extends Command
{
    private ApiConnector $apiConnector;

    public function __construct(ApiConnector $apiConnector)
    {
        parent::__construct();
        $this->apiConnector = $apiConnector;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a new author to the API')
            ->addArgument('email', InputArgument::OPTIONAL, 'The email for auth')
            ->addArgument('password', InputArgument::OPTIONAL, 'The password for auth')
            ->addArgument('first_name', InputArgument::OPTIONAL, 'The first name of the author')
            ->addArgument('last_name', InputArgument::OPTIONAL, 'The last name of the author')
            ->addArgument('birthday', InputArgument::OPTIONAL, 'The birthday of the author (YYYY-MM-DD)')
            ->addArgument('biography', InputArgument::OPTIONAL, 'The biography of the author')
            ->addArgument('gender', InputArgument::OPTIONAL, 'The gender of the author (male/female)')
            ->addArgument('place_of_birth', InputArgument::OPTIONAL, 'The birthplace of the author');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $email = $input->getArgument('email') ?: $helper->ask($input, $output, new Question('Enter email: '));
        $password = $input->getArgument('password') ?: $helper->ask($input, $output, new Question('Enter email: '));
        $firstName = $input->getArgument('first_name') ?: $helper->ask($input, $output, new Question('Enter first name: '));
        $lastName = $input->getArgument('last_name') ?: $helper->ask($input, $output, new Question('Enter last name: '));
        $birthday = $input->getArgument('birthday') ?: $helper->ask($input, $output, new Question('Enter birthday (YYYY-MM-DD): '));
        $biography = $input->getArgument('biography') ?: $helper->ask($input, $output, new Question('Enter biography: '));
        $gender = $input->getArgument('gender') ?: $helper->ask($input, $output, new Question('Enter gender (male/female): '));
        $placeOfBirth = $input->getArgument('place_of_birth') ?: $helper->ask($input, $output, new Question('Enter place of birth: '));

        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birthday' => $birthday,
            'biography' => $biography,
            'gender' => $gender,
            'place_of_birth' => $placeOfBirth
        ];

        try {
            $auth = $this->apiConnector->login($email, $password);
            if (!empty($auth['token_key'])) {
                $token = $auth['token_key'];
                $response = $this->apiConnector->request('POST', '/authors', [
                    'json' => $data
                ], $token);

                if (isset($response['id'])) {
                    $output->writeln('<info>Author added successfully! ID: ' . $response['id'] . '</info>');
                } else {
                    $output->writeln('<error>Failed to add author.</error>');
                }
            } else {
                $output->writeln('<error>Failed to add author.</error>');
            }

        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
        }

        return Command::SUCCESS;
    }
}
