# Quick Start - PDF Certificate Feature

## 1. Install Dependencies

```bash
composer require knplabs/knp-snappy-bundle
```

## 2. Install wkhtmltopdf

### Windows
Download and install from: https://wkhtmltopdf.org/downloads.html

### Linux
```bash
sudo apt-get install wkhtmltopdf
```

### macOS
```bash
brew install wkhtmltopdf
```

## 3. Configure Binary Path

Edit `config/packages/knp_snappy.yaml` with your wkhtmltopdf path.

## 4. Test Installation

```bash
# Test if wkhtmltopdf is accessible
wkhtmltopdf --version

# Clear cache
php bin/console cache:clear

# Test certificate generation
php bin/console app:test-certificate
```

## 5. Usage

1. Log in as a student
2. Go to "My Rewards" page
3. Click "Download Certificate" on any Badge/Achievement reward

## Files Created

- `src/Service/game/CertificateService.php` - PDF generation service
- `templates/front/game/certificate.html.twig` - Certificate template
- `config/packages/knp_snappy.yaml` - Configuration
- `src/Command/TestCertificateCommand.php` - Test command

## Files Modified

- `src/Controller/Front/Game/RewardController.php` - Added certificate route
- `templates/front/game/my_rewards.html.twig` - Added download buttons

See `PDF_CERTIFICATE_SETUP.md` for detailed documentation.
