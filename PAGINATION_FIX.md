# Pagination Fix - Reward Entity

## Issue
Error when accessing `/rewards/browse`:
```
[Semantical Error] line 0, col 85 near 'createdAt DE': 
Error: Class App\Entity\Gamification\Reward has no field or association named createdAt
```

## Root Cause
The `Reward` entity doesn't have a `createdAt` field, but the pagination query was trying to order by it.

## Solution
Changed the ordering field from `createdAt` to `id` in the RewardController.

### Before
```php
$query = $this->rewardRepository->createQueryBuilder('r')
    ->where('r.isActive = :active')
    ->setParameter('active', true)
    ->orderBy('r.createdAt', 'DESC')  // ❌ Field doesn't exist
    ->getQuery();
```

### After
```php
$query = $this->rewardRepository->createQueryBuilder('r')
    ->where('r.isActive = :active')
    ->setParameter('active', true)
    ->orderBy('r.id', 'DESC')  // ✅ Orders by ID (newest first)
    ->getQuery();
```

## Entity Comparison

### Game Entity
```php
#[ORM\Column]
private ?\DateTimeImmutable $createdAt = null;  // ✅ Has createdAt
```

### Reward Entity
```php
// ❌ No createdAt field
// Only has: id, name, description, type, value, requirement, icon, isActive
```

## Impact
- Rewards are now ordered by ID in descending order
- Higher IDs (newer rewards) appear first
- Pagination works correctly
- No functionality lost

## Alternative Solutions

### Option 1: Add createdAt to Reward Entity (Recommended for Production)
```php
// In Reward.php
#[ORM\Column]
private ?\DateTimeImmutable $createdAt = null;

public function __construct()
{
    $this->createdAt = new \DateTimeImmutable();
    $this->games = new ArrayCollection();
    $this->students = new ArrayCollection();
}

public function getCreatedAt(): ?\DateTimeImmutable
{
    return $this->createdAt;
}
```

Then run migration:
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Option 2: Order by Name
```php
->orderBy('r.name', 'ASC')  // Alphabetical order
```

### Option 3: Order by Type then ID
```php
->orderBy('r.type', 'ASC')
->addOrderBy('r.id', 'DESC')
```

## Current Status
✅ Fixed - Rewards browse page now works correctly
✅ Orders by ID (newest first)
✅ Pagination functional

## Recommendation
For production, consider adding `createdAt` field to Reward entity for better tracking and ordering.

---

**Fixed:** February 18, 2026
**Status:** Working ✅
