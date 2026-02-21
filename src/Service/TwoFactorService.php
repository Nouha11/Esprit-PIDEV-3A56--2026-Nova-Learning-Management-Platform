<?php

namespace App\Service;

use App\Entity\users\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use OTPHP\TOTP;

class TwoFactorService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function generateSecret(): string
    {
        return TOTP::generate()->getSecret();
    }

    public function enableTwoFactor(User $user): string
    {
        $secret = $this->generateSecret();
        $user->setTotpSecret($secret);
        $user->setTotpEnabled(false); // Will be enabled after verification
        
        $this->entityManager->flush();
        
        return $secret;
    }

    public function verifyAndEnable(User $user, string $code): bool
    {
        if (!$user->getTotpSecret()) {
            return false;
        }

        $totp = TOTP::createFromSecret($user->getTotpSecret());
        
        if ($totp->verify($code)) {
            $user->setTotpEnabled(true);
            $this->entityManager->flush();
            return true;
        }
        
        return false;
    }

    public function disableTwoFactor(User $user): void
    {
        $user->setTotpEnabled(false);
        $user->setTotpSecret(null);
        $this->entityManager->flush();
    }

    public function generateQrCode(User $user): string
    {
        if (!$user->getTotpSecret()) {
            throw new \RuntimeException('User does not have a TOTP secret');
        }

        $totp = TOTP::createFromSecret($user->getTotpSecret());
        $totp->setLabel($user->getEmail());
        $totp->setIssuer('NOVA Platform');

        // Use SVG writer instead of PNG (doesn't require GD extension)
        $builder = new Builder(
            writer: new SvgWriter(),
            writerOptions: [],
            validateResult: false,
            data: $totp->getProvisioningUri(),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $result = $builder->build();

        return $result->getDataUri();
    }

    public function verifyCode(User $user, string $code): bool
    {
        if (!$user->getTotpSecret()) {
            return false;
        }

        $totp = TOTP::createFromSecret($user->getTotpSecret());
        return $totp->verify($code);
    }
}
