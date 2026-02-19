# QR Code & Pagination Integration Guide

## Overview
Successfully integrated two bundles into the Symfony 6.4 gamification module:
1. **endroid/qr-code-bundle** - QR code generation for rewards
2. **knplabs/knp-paginator-bundle** - Pagination for games and rewards

---

## 1. Bundle Installation

### QR Code Bundle
```bash
composer require endroid/qr-code-bundle
```

**Installed Version:** 6.0.0
**Dependencies:**
- bacon/bacon-qr-code (v3.0.3)
- dasprid/enum (1.0.7)
- endroid/qr-code (6.0.9)
- endroid/installer (1.5.0)

### Paginator Bundle
```bash
composer require knplabs/knp-paginator-bundle
```

**Already Installed:** 6.10

---

## 2. Configuration

### QR Code Configuration
**File:** `config/packages/endroid_qr_code.yaml`

```yaml
endroid_qr_code:
    default:
        writer: Endroid\QrCode\Writer\PngWriter
        size: 300
        margin: 10
        encoding: 'UTF-8'
        error_correction_level: 'low'
        round_block_size_mode: 'margin'
        validate_result: false
```

### Paginator Configuration
**File:** `config/packages/knp_paginator.yaml`

```yaml
knp_paginator:
    page_range: 5                       
    default_options:
        page_name: page                 
        sort_field_name: sort           
        sort_direction_name: direction  
        distinct: true                  
        filter_field_name: filterField  
        filter_value_name: filterValue  
    template:
        pagination: '@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig'     
        sortable: '@KnpPaginator/Pagination/sortable_link.html.twig'
```

---

## 3. Controller Implementation

### GameController Updates

**File:** `src/Controller/Front/Game/GameController.php`

#### Added Imports
```php
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
```

#### Constructor Injection
```php
public function __construct(
    private GameService $gameService,
    private GamificationGameRepository $gameRepository,
    private PaginatorInterface $paginator
) {
}
```

#### Index Method (with Pagination)
```php
#[Route('', name: 'front_game_index', methods: ['GET'])]
public function index(Request $request): Response
{
    $query = $this->gameRepository->createQueryBuilder('g')
        ->where('g.isActive = :active')
        ->setParameter('active', true)
        ->orderBy('g.createdAt', 'DESC')
        ->getQuery();

    $pagination = $this->paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        6 // 6 games per page
    );

    return $this->render('front/game/index.html.twig', [
        'games' => $pagination,
    ]);
}
```

#### Filter by Type Method (with Pagination)
```php
#[Route('/type/{type}', name: 'front_game_by_type', methods: ['GET'])]
public function byType(string $type, Request $request): Response
{
    $validTypes = ['PUZZLE', 'MEMORY', 'TRIVIA', 'ARCADE'];
    if (!in_array($type, $validTypes)) {
        throw $this->createNotFoundException('Invalid game type');
    }

    $query = $this->gameRepository->createQueryBuilder('g')
        ->where('g.isActive = :active')
        ->andWhere('g.type = :type')
        ->setParameter('active', true)
        ->setParameter('type', $type)
        ->orderBy('g.createdAt', 'DESC')
        ->getQuery();

    $pagination = $this->paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        6 // 6 games per page
    );

    return $this->render('front/game/index.html.twig', [
        'games' => $pagination,
        'filter_type' => $type,
    ]);
}
```

---

### RewardController Updates

**File:** `src/Controller/Front/Game/RewardController.php`

#### Added Imports
```php
use App\Repository\Gamification\RewardRepository;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
```

#### Constructor Injection
```php
public function __construct(
    private RewardService $rewardService,
    private CertificateService $certificateService,
    private PaginatorInterface $paginator,
    private RewardRepository $rewardRepository
) {
}
```

#### Browse Method (with Pagination)
```php
#[Route('/browse', name: 'front_reward_browse', methods: ['GET'])]
public function browse(Request $request): Response
{
    $user = $this->getUser();
    $student = $user ? $user->getStudentProfile() : null;

    $query = $this->rewardRepository->createQueryBuilder('r')
        ->where('r.isActive = :active')
        ->setParameter('active', true)
        ->orderBy('r.createdAt', 'DESC')
        ->getQuery();

    $pagination = $this->paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        8 // 8 rewards per page
    );

    return $this->render('front/game/browse.html.twig', [
        'rewards' => $pagination,
        'student' => $student,
    ]);
}
```

#### Show Method (with QR Code)
```php
#[Route('/{id}', name: 'front_reward_show', methods: ['GET'])]
public function show(Reward $reward): Response
{
    $user = $this->getUser();
    $student = $user ? $user->getStudentProfile() : null;

    // Generate QR code for this reward
    $rewardUrl = $this->generateUrl(
        'front_reward_show', 
        ['id' => $reward->getId()], 
        UrlGeneratorInterface::ABSOLUTE_URL
    );
    
    $result = Builder::create()
        ->writer(new PngWriter())
        ->data($rewardUrl)
        ->encoding(new Encoding('UTF-8'))
        ->errorCorrectionLevel(ErrorCorrectionLevel::High)
        ->size(300)
        ->margin(10)
        ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
        ->build();

    $qrCodeDataUri = $result->getDataUri();

    return $this->render('front/game/reward_show.html.twig', [
        'reward' => $reward,
        'games' => $reward->getGames(),
        'student' => $student,
        'qrCode' => $qrCodeDataUri,
    ]);
}
```

---

## 4. Template Updates

### Game Index Template
**File:** `templates/front/game/index.html.twig`

#### Added Pagination
```twig
<!-- Pagination -->
<div class="row mt-4">
    <div class="col-12">
        <div class="navigation d-flex justify-content-center">
            {{ knp_pagination_render(games) }}
        </div>
    </div>
</div>
```

### Reward Browse Template
**File:** `templates/front/game/browse.html.twig`

#### Added Pagination
```twig
<!-- Pagination -->
<div class="row mt-4">
    <div class="col-12">
        <div class="navigation d-flex justify-content-center">
            {{ knp_pagination_render(rewards) }}
        </div>
    </div>
</div>
```

### Reward Show Template
**File:** `templates/front/game/reward_show.html.twig`

#### Added QR Code Display
```twig
<!-- QR Code Section -->
<div class="mb-4">
    <h5 class="text-muted mb-3">
        <i class="bi bi-qr-code me-2"></i>Share This Reward
    </h5>
    <div class="card border-primary">
        <div class="card-body text-center">
            <img src="{{ qrCode }}" alt="QR Code for {{ reward.name }}" 
                 class="img-fluid mb-3" style="max-width: 250px;">
            <p class="small text-muted mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Scan this QR code to view this reward
            </p>
        </div>
    </div>
</div>
```

---

## 5. Features Implemented

### Pagination Features
- ✅ Game list paginated (6 games per page)
- ✅ Reward list paginated (8 rewards per page)
- ✅ Filter by game type with pagination
- ✅ Bootstrap 4 styled pagination controls
- ✅ Page navigation with page numbers
- ✅ Maintains query parameters

### QR Code Features
- ✅ Unique QR code for each reward
- ✅ Encodes absolute URL to reward detail page
- ✅ High error correction level
- ✅ 300x300px size with 10px margin
- ✅ PNG format with data URI
- ✅ Displayed in styled card on reward detail page
- ✅ Scannable with any QR code reader

---

## 6. Usage Examples

### Accessing Paginated Games
```
/games                    # Page 1 (default)
/games?page=2            # Page 2
/games/type/PUZZLE       # Puzzle games, page 1
/games/type/PUZZLE?page=2 # Puzzle games, page 2
```

### Accessing Paginated Rewards
```
/rewards/browse          # Page 1 (default)
/rewards/browse?page=2   # Page 2
/rewards/browse?page=3   # Page 3
```

### QR Code Generation
- Automatically generated for each reward
- Accessible at: `/rewards/{id}`
- QR code encodes: `https://yourdomain.com/rewards/{id}`
- Scannable from mobile devices

---

## 7. Customization Options

### Pagination Customization

#### Change Items Per Page
```php
// In controller
$pagination = $this->paginator->paginate(
    $query,
    $request->query->getInt('page', 1),
    12 // Change to desired number
);
```

#### Change Pagination Template
```yaml
# config/packages/knp_paginator.yaml
knp_paginator:
    template:
        pagination: '@KnpPaginator/Pagination/twitter_bootstrap_v5_pagination.html.twig'
```

### QR Code Customization

#### Change Size
```php
$result = Builder::create()
    ->size(400) // Change size
    ->margin(15) // Change margin
    // ... other options
```

#### Change Error Correction
```php
use Endroid\QrCode\ErrorCorrectionLevel;

$result = Builder::create()
    ->errorCorrectionLevel(ErrorCorrectionLevel::High) // High, Medium, Low, Quartile
    // ... other options
```

#### Change Format
```php
use Endroid\QrCode\Writer\SvgWriter;

$result = Builder::create()
    ->writer(new SvgWriter()) // SVG instead of PNG
    // ... other options
```

---

## 8. Testing

### Test Pagination
1. Navigate to `/games`
2. Verify 6 games per page
3. Click page numbers to navigate
4. Test filter by type with pagination
5. Navigate to `/rewards/browse`
6. Verify 8 rewards per page

### Test QR Codes
1. Navigate to any reward detail page
2. Verify QR code is displayed
3. Scan QR code with mobile device
4. Verify it redirects to correct reward page
5. Test with different rewards

---

## 9. Benefits

### Pagination Benefits
- Improved page load times
- Better user experience
- Reduced server load
- Easier navigation through large datasets
- SEO friendly

### QR Code Benefits
- Easy sharing of rewards
- Mobile-friendly access
- Offline capability (once generated)
- Professional appearance
- Marketing opportunities

---

## 10. Troubleshooting

### Pagination Not Working
- Check if Request object is injected
- Verify query builder returns Query object
- Check template has pagination render call

### QR Code Not Displaying
- Verify endroid/qr-code-bundle is installed
- Check if absolute URL is generated correctly
- Verify data URI is passed to template
- Check browser console for errors

### Pagination Styling Issues
- Verify Bootstrap is loaded
- Check pagination template path
- Customize CSS if needed

---

## 11. Files Modified

### Controllers
- `src/Controller/Front/Game/GameController.php`
- `src/Controller/Front/Game/RewardController.php`

### Templates
- `templates/front/game/index.html.twig`
- `templates/front/game/browse.html.twig`
- `templates/front/game/reward_show.html.twig`

### Configuration
- `config/packages/endroid_qr_code.yaml` (auto-generated)
- `config/packages/knp_paginator.yaml` (existing)

### Composer
- `composer.json` (updated with new package)
- `composer.lock` (updated)

---

## 12. Next Steps

### Potential Enhancements
- Add sorting options to pagination
- Add filtering options
- Cache QR codes for performance
- Add download QR code button
- Add QR code to PDF certificates
- Implement AJAX pagination
- Add search functionality with pagination

---

**Last Updated:** February 18, 2026
**Status:** Fully Integrated ✅
**Symfony Version:** 6.4
