<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(
    name: 'app:check-oauth',
    description: 'Check OAuth configuration and display redirect URIs',
)]
class CheckOAuthConfigCommand extends Command
{
    public function __construct(
        private RouterInterface $router
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('OAuth Configuration Check');

        // Check environment variables
        $io->section('Environment Variables');
        
        $googleClientId = $_ENV['GOOGLE_CLIENT_ID'] ?? 'NOT SET';
        $googleClientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? 'NOT SET';
        $linkedinClientId = $_ENV['LINKEDIN_CLIENT_ID'] ?? 'NOT SET';
        $linkedinClientSecret = $_ENV['LINKEDIN_CLIENT_SECRET'] ?? 'NOT SET';

        $io->table(
            ['Provider', 'Client ID', 'Client Secret'],
            [
                ['Google', $this->maskSecret($googleClientId), $this->maskSecret($googleClientSecret)],
                ['LinkedIn', $this->maskSecret($linkedinClientId), $this->maskSecret($linkedinClientSecret)],
            ]
        );

        // Check routes
        $io->section('OAuth Routes');
        
        try {
            $googleStart = $this->router->generate('connect_google_start', [], RouterInterface::ABSOLUTE_URL);
            $googleCheck = $this->router->generate('connect_google_check', [], RouterInterface::ABSOLUTE_URL);
            $linkedinStart = $this->router->generate('connect_linkedin_start', [], RouterInterface::ABSOLUTE_URL);
            $linkedinCheck = $this->router->generate('connect_linkedin_check', [], RouterInterface::ABSOLUTE_URL);

            $io->table(
                ['Route', 'URL'],
                [
                    ['Google Start', $googleStart],
                    ['Google Callback', $googleCheck],
                    ['LinkedIn Start', $linkedinStart],
                    ['LinkedIn Callback', $linkedinCheck],
                ]
            );

            // Display redirect URIs to configure
            $io->section('Configure These Redirect URIs in OAuth Console');
            
            $io->writeln('<info>Google OAuth Console:</info>');
            $io->writeln('  ' . $googleCheck);
            $io->writeln('  http://localhost:8000/connect/google/check');
            $io->writeln('  http://127.0.0.1:8000/connect/google/check');
            $io->newLine();
            
            $io->writeln('<info>LinkedIn OAuth Console:</info>');
            $io->writeln('  ' . $linkedinCheck);
            $io->writeln('  http://localhost:8000/connect/linkedin/check');
            $io->writeln('  http://127.0.0.1:8000/connect/linkedin/check');
            $io->newLine();

        } catch (\Exception $e) {
            $io->error('Error generating routes: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Configuration status
        $io->section('Configuration Status');
        
        $allConfigured = true;
        
        if ($googleClientId === 'NOT SET' || $googleClientId === 'your_google_client_id_here') {
            $io->warning('Google Client ID is not configured');
            $allConfigured = false;
        }
        
        if ($googleClientSecret === 'NOT SET' || $googleClientSecret === 'your_google_client_secret_here') {
            $io->warning('Google Client Secret is not configured');
            $allConfigured = false;
        }
        
        if ($linkedinClientId === 'NOT SET' || $linkedinClientId === 'your_linkedin_client_id_here') {
            $io->warning('LinkedIn Client ID is not configured');
            $allConfigured = false;
        }
        
        if ($linkedinClientSecret === 'NOT SET' || $linkedinClientSecret === 'your_linkedin_client_secret_here') {
            $io->warning('LinkedIn Client Secret is not configured');
            $allConfigured = false;
        }

        if ($allConfigured) {
            $io->success('All OAuth credentials are configured!');
        } else {
            $io->note('Configure missing credentials in .env.local file');
        }

        // Next steps
        $io->section('Next Steps');
        $io->listing([
            'Copy the redirect URIs above',
            'Add them to your OAuth provider console (Google/LinkedIn)',
            'Make sure credentials are in .env.local (not .env)',
            'Clear cache: php bin/console cache:clear',
            'Test OAuth login',
        ]);

        return Command::SUCCESS;
    }

    private function maskSecret(string $value): string
    {
        if ($value === 'NOT SET' || strlen($value) < 10) {
            return $value;
        }
        
        return substr($value, 0, 10) . '...' . substr($value, -4);
    }
}
