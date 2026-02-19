# Token Spending System

## Overview
A comprehensive token-based payment system for games with Ajax validation, transaction logging, and user-friendly error handling.

## Features

✅ Token balance checking before game access  
✅ Automatic token deduction on game play  
✅ Transaction logging for audit trail  
✅ Ajax validation (no page reload)  
✅ Beautiful modal for insufficient tokens  
✅ Real-time balance display  
✅ Free game support (0 token cost)  

## Components

### 1. TokenService

**Location**: `src/Service/game/TokenService.php`

**Main Methods**:

```php
// Check if student has enough tokens
hasEnoughTokens(StudentProfile $student, Game $game): bool

// Get missing token count
getMissingTokens(StudentProfile $student, Game $game): int

// Deduct tokens and log transaction
deductTokens(StudentProfile $student, Game $game, string $reason): bool

// Add tokens and log transaction
addTokens(StudentProfile $student, int $amount, string $reason): void

// Validate transaction
validateTransaction(StudentProfile $student, Game $game): array

// Check if game is free
isFreeGame(Game $game): bool
```

### 2. Controller Endpoints

**Check Tokens (Ajax)**: `POST /games/{id}/check-tokens`
- Validates if student can afford the game
- Returns JSON with balance, cost, and missing tokens
- Requires ROLE_STUDENT

**Play Game**: `GET /games/{id}/play`
- Checks tokens before allowing access
- Deducts tokens if sufficient
- Logs transaction
- Redirects to game or shows error

### 3. Frontend Components

**Play Button**: Ajax-enabled button with loading state
**Modal**: Bootstrap modal showing:
- Current token balance
- Game cost
- Missing tokens
- Helpful message
- Link to browse more games

## User Flow

1. **Student clicks "Play Now"**
   - Button shows loading spinner
   - Ajax request sent to check tokens

2. **Sufficient Tokens**
   - Tokens deducted
   - Transaction logged
   - Redirect to game

3. **Insufficient Tokens**
   - Modal appears with details
   - Shows how many tokens needed
   - Suggests earning more tokens
   - No page reload

## Transaction Logging

All token transactions are logged with:
- Student ID and name
- Game ID and name (if applicable)
- Amount (positive for credit, negative for debit)
- Previous balance
- New balance
- Reason for transaction
- Timestamp

**Log Location**: `var/log/dev.log` (or configured log file)

**Example Log Entry**:
```
[info] Token transaction {
    "student_id": 5,
    "student_name": "Nouha Hamrouni",
    "game_id": 1,
    "game_name": "Puzzle Master",
    "amount": -10,
    "previous_balance": 80,
    "new_balance": 70,
    "reason": "Game play: Puzzle Master",
    "timestamp": "2026-02-19T12:30:45+00:00"
}
```

## API Response Format

**Check Tokens Endpoint**:
```json
{
  "success": false,
  "canAfford": false,
  "message": "You need 5 more tokens to play this game",
  "missing": 5,
  "currentBalance": 15,
  "gameCost": 20,
  "gameIsFree": false
}
```

## Usage Examples

### In Controller

```php
// Check if student can afford game
if ($this->tokenService->hasEnoughTokens($student, $game)) {
    // Deduct tokens
    $this->tokenService->deductTokens($student, $game, 'Game play');
    // Allow access
}

// Add tokens as reward
$this->tokenService->addTokens($student, 50, 'Game completion reward');

// Validate transaction
$validation = $this->tokenService->validateTransaction($student, $game);
if (!$validation['valid']) {
    $this->addFlash('error', $validation['message']);
}
```

### In Template

```twig
{# Display token balance #}
<p>Your Tokens: {{ app.user.studentProfile.totalTokens }}</p>

{# Display game cost #}
<p>Cost: {{ game.tokenCost }} tokens</p>

{# Ajax-enabled play button #}
<button id="play-game-btn" 
        data-game-id="{{ game.id }}"
        data-game-cost="{{ game.tokenCost }}">
    Play Now
</button>
```

## Modal Customization

The insufficient tokens modal can be customized by editing:
- `templates/front/game/show.html.twig` (modal HTML)
- Modal styling in the `<style>` section
- JavaScript behavior in the `<script>` section

## Security Features

- CSRF protection on all endpoints
- Role-based access control (ROLE_STUDENT required)
- Server-side validation (never trust client)
- Transaction logging for audit trail
- Balance checks before deduction

## Testing

### Test Insufficient Tokens

1. Create a student with low token balance
2. Try to play an expensive game
3. Verify modal appears with correct information
4. Check that no tokens were deducted

### Test Sufficient Tokens

1. Create a student with enough tokens
2. Play a game
3. Verify tokens were deducted
4. Check transaction log
5. Verify game access granted

### Test Free Games

1. Set game tokenCost to 0
2. Play the game
3. Verify no tokens deducted
4. Verify immediate access

## Troubleshooting

**Modal not appearing**:
- Check browser console for errors
- Verify Bootstrap JS is loaded
- Check Ajax endpoint is accessible

**Tokens not deducting**:
- Check transaction logs
- Verify EntityManager is flushing
- Check student profile has deductTokens method

**Wrong balance displayed**:
- Clear Symfony cache
- Refresh page (Ctrl+F5)
- Check database values directly

## Future Enhancements

- [ ] Token purchase system
- [ ] Token gift system (send to friends)
- [ ] Daily token bonuses
- [ ] Token transaction history page
- [ ] Refund system for interrupted games
- [ ] Token packages/bundles
- [ ] Promotional token codes
- [ ] Token leaderboard

## Related Files

- `src/Service/game/TokenService.php` - Token logic
- `src/Controller/Front/Game/GameController.php` - Endpoints
- `templates/front/game/show.html.twig` - UI
- `src/Entity/users/StudentProfile.php` - Token balance
- `src/Entity/Gamification/Game.php` - Token cost
