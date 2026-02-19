# PDF Certificate Feature - Setup Guide

## Overview
This feature generates personalized PDF certificates when users earn Badge or Achievement rewards in the NOVA gamification system.

## Features Implemented

### 1. Certificate Service (`src/Service/game/CertificateService.php`)
- Generates PDF certificates from HTML templates
- Creates downloadable PDF files with proper headers
- Sanitizes filenames for safe downloads

### 2. Certificate Template (`templates/front/game/certificate.html.twig`)
- Beautiful landscape A4 certificate design
- Includes:
  - User's full name
  - Reward name and description
  - Reward type badge
  - Date earned
  - Decorative borders and styling
  - NOVA branding
  - Professional layout with watermark

### 3. Updated Reward Controller
- New route: `/rewards/{id}/certificate` to download certificates
- Security: Only students who earned the reward can download
- Validation: Only Badge and Achievement rewards get certificates

### 4. Updated My Rewards Page
- Displays all earned rewards in card format
- Certificate download button for Badge/Achievement rewards
- Visual indicators for certificate availability
- Responsive grid layout

## Installation Steps

### Step 1: Install knp-snappy-bundle

```bash
composer require knplabs/knp-snappy-bundle
```

### Step 2: Install wkhtmltopdf

#### Windows:
1. Download from: https://wkhtmltopdf.org/downloads.html
2. Install to: `C:\Program Files\wkhtmltopdf\`
3. Update path in `config/packages/knp_snappy.yaml`

#### Linux (Ubuntu/Debian):
```bash
sudo apt-get update
sudo apt-get install wkhtmltopdf
```

#### macOS:
```bash
brew install wkhtmltopdf
```

### Step 3: Configure Binary Path

Edit `config/packages/knp_snappy.yaml` and set the correct path:

**Windows (IMPORTANT - Note the quotes!):**
```yaml
knp_snappy:
    pdf:
        binary: '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"'
```

⚠️ **Windows Path with Spaces**: Use double quotes inside single quotes: `'"path"'`

**Alternative for Windows (Short Path):**
```yaml
knp_snappy:
    pdf:
        binary: 'C:\PROGRA~1\wkhtmltopdf\bin\wkhtmltopdf.exe'
```

**Linux/Mac:**
```yaml
knp_snappy:
    pdf:
        binary: /usr/local/bin/wkhtmltopdf
```

**Or use environment variable:**
```yaml
knp_snappy:
    pdf:
        binary: '%env(WKHTMLTOPDF_PATH)%'
```

Then add to `.env`:
```
WKHTMLTOPDF_PATH="C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"
```

### Step 4: Clear Cache

```bash
php bin/console cache:clear
```

### Step 5: Test the Feature

1. Log in as a student
2. Manually add a reward to test:
   ```php
   // In a controller or command
   $student->addEarnedReward($reward);
   $entityManager->flush();
   ```
3. Visit `/rewards/my-rewards`
4. Click "Download Certificate" on a Badge or Achievement reward

## Usage

### For Students

1. Navigate to "My Rewards" page
2. View earned rewards displayed as cards
3. Click "Download Certificate" button on Badge/Achievement rewards
4. PDF certificate downloads automatically

### Certificate Includes

- **Header**: NOVA logo and "Certificate of Achievement" title
- **Recipient**: Student's full name
- **Reward Details**: 
  - Reward type badge
  - Reward name
  - Reward description
  - Reward value (XP/Tokens)
- **Footer**: 
  - Date earned
  - Authorized signature
- **Design**: 
  - Professional landscape layout
  - Decorative borders
  - Gradient backgrounds
  - Watermark

## Customization

### Modify Certificate Design

Edit `templates/front/game/certificate.html.twig`:

```twig
{# Change colors #}
background: linear-gradient(135deg, #YOUR_COLOR_1 0%, #YOUR_COLOR_2 100%);

{# Change fonts #}
font-family: 'Your Font', serif;

{# Modify layout #}
{# Adjust padding, margins, sizes as needed #}
```

### Add Custom Logo

Replace the emoji logo with an image:

```twig
<div class="certificate-logo">
    <img src="{{ asset('path/to/logo.png') }}" alt="NOVA Logo">
</div>
```

### Change Certificate Size

In `CertificateService.php`:

```php
$pdfContent = $this->pdf->getOutputFromHtml($html, [
    'page-size' => 'Letter',  // or 'A4', 'Legal', etc.
    'orientation' => 'Portrait',  // or 'Landscape'
]);
```

## Troubleshooting

### Issue: "'C:\Program' is not recognized as an internal or external command"

**Cause:** Windows paths with spaces not properly quoted

**Solution:** 
Update `config/packages/knp_snappy.yaml`:
```yaml
binary: '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"'
```

Note: Use double quotes inside single quotes for paths with spaces.

**Alternative Solutions:**
1. Use short path: `binary: 'C:\PROGRA~1\wkhtmltopdf\bin\wkhtmltopdf.exe'`
2. Install to path without spaces: `C:\wkhtmltopdf\`
3. Add to system PATH and use: `binary: 'wkhtmltopdf'`

After changing, clear cache:
```bash
php bin/console cache:clear
```

See `CERTIFICATE_WINDOWS_FIX.md` for detailed Windows troubleshooting.

### Issue: "wkhtmltopdf not found"

**Solution:** 
- Verify wkhtmltopdf is installed: `wkhtmltopdf --version`
- Check the binary path in `knp_snappy.yaml`
- Use absolute path to the binary

### Issue: "Permission denied"

**Solution:**
- On Linux/Mac: `chmod +x /path/to/wkhtmltopdf`
- On Windows: Run as administrator

### Issue: "Images not loading in PDF"

**Solution:**
- Use absolute URLs for images
- Enable local file access in config:
  ```yaml
  options:
      enable-local-file-access: true
  ```

### Issue: "Fonts not rendering correctly"

**Solution:**
- Use web-safe fonts (Georgia, Times New Roman, Arial)
- Or embed fonts using base64 in CSS

### Issue: "Certificate download not working"

**Solution:**
- Check student has earned the reward
- Verify reward type is BADGE or ACHIEVEMENT
- Check browser console for errors
- Verify CertificateService is autowired correctly

## Database Considerations

### Current Implementation
- Uses existing `student_earned_rewards` relationship
- Earned date uses current timestamp (placeholder)

### Recommended Enhancement
Create a tracking table to store when rewards were earned:

```php
// Create new entity: StudentRewardEarned
#[ORM\Entity]
class StudentRewardEarned
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: StudentProfile::class)]
    private ?StudentProfile $student = null;

    #[ORM\ManyToOne(targetEntity: Reward::class)]
    private ?Reward $reward = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $earnedAt = null;

    // Getters and setters...
}
```

Then update the certificate controller to use actual earned date:

```php
// Get the actual earned date from tracking table
$earnedReward = $earnedRewardRepository->findOneBy([
    'student' => $student,
    'reward' => $reward
]);

$earnedDate = $earnedReward->getEarnedAt();
```

## Security Notes

1. **Access Control**: Only authenticated students can download certificates
2. **Validation**: Students can only download certificates for rewards they've earned
3. **Type Restriction**: Only Badge and Achievement rewards generate certificates
4. **Filename Sanitization**: Special characters removed from filenames

## Performance Tips

1. **Caching**: Consider caching generated PDFs for frequently downloaded certificates
2. **Async Generation**: For high traffic, generate PDFs asynchronously using Messenger
3. **CDN**: Store generated PDFs on CDN for faster delivery

## Future Enhancements

1. **Email Certificates**: Automatically email certificates when earned
2. **Certificate Gallery**: Display all certificates in a gallery view
3. **Social Sharing**: Allow students to share certificates on social media
4. **Custom Templates**: Let admins create custom certificate templates
5. **QR Code**: Add QR code for certificate verification
6. **Digital Signature**: Add cryptographic signature for authenticity
7. **Multi-language**: Support certificates in multiple languages

## Testing

### Manual Test

```bash
# 1. Create test data
php bin/console doctrine:fixtures:load

# 2. Log in as student
# 3. Add reward to student (via admin or code)
# 4. Visit /rewards/my-rewards
# 5. Click "Download Certificate"
```

### Automated Test

```php
// tests/Controller/RewardControllerTest.php
public function testCertificateDownload(): void
{
    $client = static::createClient();
    
    // Login as student
    $this->loginAsStudent($client);
    
    // Create and assign reward
    $reward = $this->createBadgeReward();
    $student = $this->getStudent();
    $student->addEarnedReward($reward);
    $this->entityManager->flush();
    
    // Request certificate
    $client->request('GET', '/rewards/' . $reward->getId() . '/certificate');
    
    // Assert PDF response
    $this->assertResponseIsSuccessful();
    $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
}
```

## Support

For issues or questions:
1. Check this documentation
2. Review the troubleshooting section
3. Check knp-snappy-bundle documentation: https://github.com/KnpLabs/KnpSnappyBundle
4. Check wkhtmltopdf documentation: https://wkhtmltopdf.org/

## Files Modified/Created

### Created:
- `src/Service/game/CertificateService.php`
- `templates/front/game/certificate.html.twig`
- `config/packages/knp_snappy.yaml`
- `PDF_CERTIFICATE_SETUP.md`

### Modified:
- `src/Controller/Front/Game/RewardController.php`
- `templates/front/game/my_rewards.html.twig`

## Status

✅ **COMPLETE** - PDF certificate feature is fully implemented and ready to use!

Just install the dependencies and configure the binary path to start generating certificates.
