# Reward Email Notification Integration Guide

## How to Trigger Email When Reward is Unlocked

### Option 1: In GameController (when completing a game)

```php
<?php
namespace App\Controller\Front\Game;

use App\Service\game\RewardNotificationService;
// ... other imports

class GameController extends AbstractController
{
    public function __construct(
        private GameService $gameService,
        private RewardNotificationService $rewardNotificationService,
        // ... other dependencies
    ) {
    }

    #[Route('/{id}/complete', name: 'front_game_complete', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function complete(Game $game): Response
    {
        $user = $this->getUser();
        $student = $user->getStudentProfile();

        if (!$student) {
            $this->addFlash('error', 'Student profile not found');
            return $this->redirectToRoute('front_game_index');
        }

        // Process game completion
        $rewards = $this->gameService->processGameCompletion($game, $student, true);

        // Send email for each special reward unlocked
        if (!empty($rewards['special_rewards'])) {
            foreach ($rewards['special_rewards'] as $rewardData) {
                if ($rewardData['awarded']) {
                    // Get the actual Reward entity
                    $reward = $rewardData['reward']; // Assuming this is passed
                    
                    // Send email notification
                    $this->rewardNotificationService->sendRewardUnlockedEmail($student, $reward);
                }
            }
        }

        $this->addFlash('success', 'Congratulations! Check your email for reward details.');
        return $this->redirectToRoute('front_game_show', ['id' => $game->getId()]);
    }
}
```

### Option 2: In RewardService (centralized approach)

Update `src/Service/game/RewardService.php`:

```php
<?php
namespace App\Service\game;

use App\Entity\Gamification\Reward;
use App\Entity\users\StudentProfile;
use App\Repository\Gamification\RewardRepository;
use Doctrine\ORM\EntityManagerInterface;

class RewardService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RewardRepository $rewardRepository,
        private RewardNotificationService $rewardNotificationService
    ) {
    }

    /**
     * Award a reward to a student with email notification
     */
    public function awardRewardToStudent(Reward $reward, StudentProfile $student, bool $sendEmail = true): bool
    {
        // Check if student already has this reward
        if ($student->hasEarnedReward($reward)) {
            return false; // Already earned
        }

        // Award based on reward type
        switch ($reward->getType()) {
            case 'BONUS_XP':
                $student->addXP($reward->getValue());
                break;

            case 'BONUS_TOKENS':
                $student->addTokens($reward->getValue());
                break;

            case 'BADGE':
            case 'ACHIEVEMENT':
                $student->addEarnedReward($reward);
                break;
        }

        $this->em->flush();

        // Send email notification
        if ($sendEmail) {
            $this->rewardNotificationService->sendRewardUnlockedEmail($student, $reward);
        }

        return true;
    }
}
```

### Option 3: Using Symfony Events (Advanced)

Create an event subscriber:

```php
<?php
namespace App\EventSubscriber;

use App\Event\RewardUnlockedEvent;
use App\Service\game\RewardNotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RewardNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RewardNotificationService $rewardNotificationService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RewardUnlockedEvent::class => 'onRewardUnlocked',
        ];
    }

    public function onRewardUnlocked(RewardUnlockedEvent $event): void
    {
        $this->rewardNotificationService->sendRewardUnlockedEmail(
            $event->getStudent(),
            $event->getReward()
        );
    }
}
```

## Configuration

### 1. Configure Mailer in `.env`:

```env
# Gmail
MAILER_DSN=gmail://username:password@default

# SMTP
MAILER_DSN=smtp://user:pass@smtp.example.com:port

# For development (logs emails instead of sending)
MAILER_DSN=null://null
```

### 2. Update `config/packages/mailer.yaml` (optional):

```yaml
framework:
    mailer:
        dsn: '%env(MAILER_DSN)%'
        envelope:
            sender: 'noreply@nova-platform.com'
```

### 3. Configure sender email in services.yaml:

```yaml
services:
    App\Service\game\RewardNotificationService:
        arguments:
            $senderEmail: 'noreply@nova-platform.com'
            $senderName: 'NOVA Platform'
```

## Testing

### Test email in development:

```php
// In any controller
public function testEmail(RewardNotificationService $notificationService): Response
{
    $student = $this->studentRepository->find(1);
    $reward = $this->rewardRepository->find(1);
    
    $result = $notificationService->sendRewardUnlockedEmail($student, $reward);
    
    return new Response($result ? 'Email sent!' : 'Email failed');
}
```

### View emails in development:

If using `MAILER_DSN=null://null`, check the Symfony profiler to see the email content.

## Email Features

✅ Responsive design (mobile-friendly)
✅ Dark mode support (auto-detects user preference)
✅ Beautiful gradient design
✅ Shows reward icon/emoji
✅ Displays current stats (Level, XP, Tokens)
✅ Call-to-action button to view rewards
✅ Professional footer with links
✅ Inline CSS for maximum email client compatibility

## Customization

### Change email colors:

Edit `templates/emails/reward_unlocked.html.twig` and modify the gradient:

```css
background: linear-gradient(135deg, #YOUR_COLOR_1 0%, #YOUR_COLOR_2 100%);
```

### Add more stats:

Add to the context in `RewardNotificationService.php`:

```php
'context' => [
    // ... existing context
    'gamesPlayed' => $student->getGamesPlayed(),
    'rewardsCount' => $student->getEarnedRewards()->count(),
]
```

Then use in template:

```twig
<div class="stats-box">
    <span class="stats-value">{{ gamesPlayed }}</span>
    <span class="stats-label">Games Played</span>
</div>
```
