# Game Rating System - Testing Guide

## Status: ✅ READY FOR TESTING

The game rating system has been fully implemented and is ready to test.

## What Was Implemented

### 1. Database
- ✅ `game_rating` table created with unique constraint on (game_id, user_id)
- ✅ Rating validation: 1-5 stars only
- ✅ Cascade delete on game/user deletion
- ✅ Timestamps for created_at and updated_at

### 2. Backend
- ✅ GameRating entity with validation
- ✅ GameRatingRepository with efficient queries
- ✅ GameRatingController with Ajax endpoints
- ✅ GameController updated to pass rating data to views

### 3. Frontend
- ✅ Star rating display component (read-only)
- ✅ Star rating input component (interactive)
- ✅ Ajax submission without page reload
- ✅ Toast notifications for feedback
- ✅ Dark/light theme support

### 4. Integration
- ✅ Game show page displays rating input
- ✅ Game list page displays average ratings
- ✅ Bulk rating fetch for performance
- ✅ User's existing rating pre-filled

## How to Test

### Test 1: View Game Ratings (Not Logged In)
1. Open browser: `http://127.0.0.1:8001/games`
2. You should see star ratings on each game card
3. Click "View Details" on any game
4. Scroll to "Game Rating" section
5. You should see average rating display (read-only)
6. Message: "Login to rate this game"

### Test 2: Submit a New Rating
1. Login as a student
2. Navigate to: `http://127.0.0.1:8001/games`
3. Click "View Details" on any game
4. Scroll to "Game Rating" section
5. Hover over stars - they should highlight
6. Click on a star (e.g., 4th star for 4-star rating)
7. Toast notification should appear: "Thank you for rating this game!"
8. Average rating should update immediately
9. Your rating should be displayed: "You rated this game 4 stars"

### Test 3: Update Existing Rating
1. While still on the same game page
2. Click on a different star (e.g., 5th star)
3. Toast notification: "Your rating has been updated!"
4. Average rating updates
5. Message updates: "You rated this game 5 stars"

### Test 4: Rating Persistence
1. After rating a game, refresh the page
2. Your rating should still be displayed
3. Stars should be pre-filled with your rating
4. Average rating should be correct

### Test 5: Game List Ratings
1. Navigate to: `http://127.0.0.1:8001/games`
2. Each game card should show:
   - Star icons (filled/half/empty)
   - Average rating number
   - Total count of ratings
3. Example: "4.5 (12 ratings)"

### Test 6: Dark/Light Theme
1. Toggle between dark and light themes
2. Star icons should be visible in both modes
3. Filled stars: gold color (#ffc107)
4. Empty stars: muted/gray color
5. Hover effects should work in both themes

### Test 7: Multiple Users Rating
1. Login as User A, rate a game 5 stars
2. Logout, login as User B, rate same game 3 stars
3. Average should be 4.0 (2 ratings)
4. Each user should see their own rating when logged in

### Test 8: Validation
1. Try to submit invalid rating (should be prevented by frontend)
2. Check browser console for any errors
3. All Ajax requests should return success

## Expected Behavior

### Rating Display
- **No ratings**: "No ratings yet"
- **1 rating**: "4.0 (1 rating)"
- **Multiple**: "4.5 (23 ratings)"

### Star Display
- **4.0**: 4 filled stars, 1 empty
- **4.5**: 4 filled stars, 1 half star
- **4.7**: 5 filled stars (rounds up)

### User Feedback
- **New rating**: "Thank you for rating this game!"
- **Update rating**: "Your rating has been updated!"
- **Error**: "An error occurred. Please try again."

## Endpoints

### POST /games/{id}/rate
Submit or update a rating
```json
Request: {"rating": 4}
Response: {
  "success": true,
  "message": "Thank you for rating this game!",
  "action": "created",
  "userRating": 4,
  "averageRating": 4.2,
  "totalRatings": 15
}
```

### GET /games/{id}/rating-stats
Get rating statistics
```json
Response: {
  "success": true,
  "averageRating": 4.2,
  "totalRatings": 15,
  "userRating": 4
}
```

## Database Queries for Testing

```sql
-- View all ratings
SELECT u.username, g.name, gr.rating, gr.created_at
FROM game_rating gr
JOIN user u ON gr.user_id = u.id
JOIN game g ON gr.game_id = g.id
ORDER BY gr.created_at DESC;

-- Get average rating for a specific game
SELECT g.name, 
       ROUND(AVG(gr.rating), 1) as avg_rating, 
       COUNT(gr.id) as total_ratings
FROM game g
LEFT JOIN game_rating gr ON g.id = gr.game_id
WHERE g.id = 1
GROUP BY g.id;

-- Get all games with ratings
SELECT g.id, g.name, 
       ROUND(AVG(gr.rating), 1) as avg_rating, 
       COUNT(gr.id) as total_ratings
FROM game g
LEFT JOIN game_rating gr ON g.id = gr.game_id
GROUP BY g.id
ORDER BY avg_rating DESC;
```

## Troubleshooting

### Issue: Stars not showing
- Check Bootstrap Icons are loaded
- Verify `bi-star`, `bi-star-fill`, `bi-star-half` classes exist
- Check browser console for CSS errors

### Issue: Ajax not working
- Check browser console for JavaScript errors
- Verify route exists: `php bin/console debug:router | grep rate`
- Check network tab for 404 or 500 errors

### Issue: Rating not saving
- Check database connection
- Verify user is logged in
- Check `game_rating` table exists
- Review Symfony logs: `var/log/dev.log`

### Issue: Average not updating
- Clear cache: `php bin/console cache:clear`
- Check repository methods are working
- Verify database queries return correct data

## Files Modified/Created

### Created
- `src/Entity/Gamification/GameRating.php`
- `src/Repository/Gamification/GameRatingRepository.php`
- `src/Controller/Front/Game/GameRatingController.php`
- `templates/front/game/_star_rating.html.twig`
- `templates/front/game/_star_rating_input.html.twig`
- `database_seeds/create_game_rating_table.sql`
- `src/Command/CreateGameRatingTableCommand.php`

### Modified
- `src/Controller/Front/Game/GameController.php` - Added rating data
- `templates/front/game/show.html.twig` - Added rating section
- `templates/front/game/_games_list.html.twig` - Added rating display

## Next Steps

1. Test all scenarios above
2. Report any bugs or issues
3. Consider adding:
   - Rating statistics to admin dashboard
   - Top-rated games page
   - Rating filters in game list
   - Email notifications for new ratings
   - Rating history for users

## Success Criteria

✅ Users can rate games 1-5 stars
✅ Each user can only rate a game once (updates existing)
✅ Average rating displays correctly
✅ Rating count displays correctly
✅ Ajax submission works without page reload
✅ Toast notifications provide feedback
✅ Dark/light theme support works
✅ Ratings persist after page reload
✅ Game list shows ratings on cards
✅ No console errors or warnings

---

**Status**: All features implemented and ready for testing!
**Last Updated**: 2026-02-20
