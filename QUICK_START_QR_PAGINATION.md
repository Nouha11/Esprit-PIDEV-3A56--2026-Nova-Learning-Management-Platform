# Quick Start: QR Code & Pagination

## ✅ Installation Complete!

Both bundles have been successfully installed and integrated:
- **endroid/qr-code-bundle** v6.0.0
- **knplabs/knp-paginator-bundle** v6.10

---

## 🚀 What's New

### 1. Paginated Game List
- **URL:** `/games`
- **Items per page:** 6 games
- **Features:**
  - Page navigation
  - Filter by type (PUZZLE, MEMORY, TRIVIA, ARCADE)
  - Bootstrap styled pagination

### 2. Paginated Reward List
- **URL:** `/rewards/browse`
- **Items per page:** 8 rewards
- **Features:**
  - Page navigation
  - Tab filtering (All, Badges, Achievements, Bonuses)
  - Bootstrap styled pagination

### 3. QR Codes for Rewards
- **Location:** Reward detail page (`/rewards/{id}`)
- **Features:**
  - Unique QR code for each reward
  - Encodes full URL to reward page
  - 300x300px PNG image
  - High error correction
  - Scannable with any QR reader

---

## 📱 How to Use

### View Paginated Games
1. Go to `/games`
2. Browse through pages using navigation
3. Click page numbers or Next/Previous
4. Filter by game type to see paginated results

### View Paginated Rewards
1. Go to `/rewards/browse`
2. Use tabs to filter by type
3. Navigate through pages
4. Click on any reward to see details

### Scan QR Codes
1. Open any reward detail page
2. Scroll to "Share This Reward" section
3. Scan QR code with mobile device
4. Mobile device opens reward page

---

## 🎨 Visual Examples

### Pagination Controls
```
← Previous  1  2  [3]  4  5  Next →
```

### QR Code Display
```
┌─────────────────────────┐
│  Share This Reward      │
├─────────────────────────┤
│                         │
│      [QR CODE IMAGE]    │
│                         │
│  Scan to view reward    │
└─────────────────────────┘
```

---

## 🔧 Configuration

### Change Items Per Page

**Games (currently 6):**
```php
// src/Controller/Front/Game/GameController.php
$pagination = $this->paginator->paginate($query, $page, 6);
```

**Rewards (currently 8):**
```php
// src/Controller/Front/Game/RewardController.php
$pagination = $this->paginator->paginate($query, $page, 8);
```

### Change QR Code Size

```php
// src/Controller/Front/Game/RewardController.php
$result = Builder::create()
    ->size(400) // Change from 300 to 400
    ->margin(15) // Change from 10 to 15
```

---

## ✨ Key Features

### Pagination
- ✅ Automatic page calculation
- ✅ Query parameter handling
- ✅ Bootstrap 4 styling
- ✅ Maintains filters
- ✅ SEO friendly URLs

### QR Codes
- ✅ Unique per reward
- ✅ High quality PNG
- ✅ Error correction
- ✅ Mobile optimized
- ✅ Easy sharing

---

## 🧪 Testing

### Test Pagination
```bash
# Visit these URLs
http://localhost/games
http://localhost/games?page=2
http://localhost/games/type/PUZZLE
http://localhost/rewards/browse
http://localhost/rewards/browse?page=2
```

### Test QR Codes
1. Visit any reward: `http://localhost/rewards/1`
2. Look for QR code section
3. Use phone camera or QR app to scan
4. Verify it opens the reward page

---

## 📊 Performance

### Before
- All games loaded at once
- All rewards loaded at once
- Slow page loads with many items

### After
- Only 6 games per page
- Only 8 rewards per page
- Fast page loads
- Better user experience

---

## 🎯 Routes

### Game Routes
```
GET  /games              # Paginated game list
GET  /games?page=2       # Page 2
GET  /games/type/PUZZLE  # Filtered + paginated
```

### Reward Routes
```
GET  /rewards/browse        # Paginated reward list
GET  /rewards/browse?page=2 # Page 2
GET  /rewards/{id}          # Reward detail with QR code
```

---

## 💡 Tips

1. **Pagination works automatically** - just add `?page=X` to URL
2. **QR codes are generated on-the-fly** - no storage needed
3. **Mobile friendly** - both features work great on mobile
4. **Customizable** - easy to change items per page or QR size
5. **SEO friendly** - search engines can crawl paginated pages

---

## 🐛 Common Issues

### Pagination not showing?
- Check if you have more items than items per page
- Verify template has `{{ knp_pagination_render(games) }}`

### QR code not displaying?
- Check browser console for errors
- Verify `$qrCode` variable is passed to template
- Check if image src starts with `data:image/png;base64`

### Styling issues?
- Verify Bootstrap is loaded
- Check pagination template configuration
- Add custom CSS if needed

---

## 📚 Documentation

Full documentation: `QR_CODE_PAGINATION_INTEGRATION.md`

---

**Status:** Ready to Use ✅
**Last Updated:** February 18, 2026
