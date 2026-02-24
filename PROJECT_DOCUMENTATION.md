# 📚 Nova Library Management System - Project Documentation

## 🎯 Project Overview

**Nova** is a comprehensive library management system built with Symfony 6 that manages physical and digital books, loans, payments, and includes an AI-powered reading assistant.

---

## 🏗️ Technical Stack

### Backend
- **Framework**: Symfony 6.4 (PHP 8.1+)
- **Database**: MySQL/MariaDB
- **ORM**: Doctrine
- **Architecture**: MVC (Model-View-Controller)

### Frontend
- **Template Engine**: Twig
- **CSS Framework**: Bootstrap 5
- **JavaScript**: Vanilla JS + PDF.js
- **Maps**: Leaflet.js (OpenStreetMap)

### External APIs
- **AI Provider**: Groq API (Llama 3.3 70B model)
- **Alternative**: OpenAI, DeepSeek (configurable)

---

## 📋 Main Features

### 1. **User Management**
- **Roles**: Admin, Student, Tutor
- **Authentication**: Symfony Security Component
- **Profiles**: Separate student and tutor profiles

### 2. **Book Management**
- **Types**: Physical books and Digital books (PDFs)
- **CRUD Operations**: Create, Read, Update, Delete
- **File Upload**: Local storage for PDFs and cover images
- **Search**: By title, author, category

### 3. **Loan System** ⭐
- **Workflow**: 6 statuses (PENDING, APPROVED, REJECTED, ACTIVE, RETURNED, OVERDUE)
- **Business Rules**:
  - Max 3 active loans per user
  - 14-day loan period
  - Auto-overdue detection
  - Admin approval required
- **Library Selection**: Interactive map with geolocation
- **Distance Calculation**: Haversine formula for nearest library

### 4. **Payment System** 💳
- **Digital Purchases**: Buy PDF books
- **Payment Methods**: Credit Card, PayPal (simulated)
- **Validation**: Luhn algorithm for card numbers
- **Security**: Only last 4 digits stored
- **History**: Complete payment tracking

### 5. **Notification System** 🔔
- **Real-time Notifications**: Loan status changes, payments
- **Types**: Approved, Rejected, Active, Returned, Payment success
- **UI**: Dropdown with unread count
- **Mark as Read**: Individual or bulk

### 6. **Analytics Dashboard** 📊
- **Sales Over Time**: Line chart (last 12 months)
- **Revenue Trends**: Bar chart
- **Top Selling Books**: Top 10 list
- **Statistics**: Total sales, revenue, average order value

### 7. **AI Reading Assistant** 🤖 (INNOVATIVE FEATURE)
- **PDF Viewer**: Built with PDF.js
- **Text Selection**: Selectable text layer
- **AI Explanations**: 
  - Groq AI mode (real AI)
  - Fallback mode (local analysis)
- **Custom Questions**: Users can ask specific questions
- **Features**:
  - Concept explanation
  - Term definitions
  - Context and examples
  - Language detection
  - Keyword extraction

### 8. **Interactive Map** 🗺️
- **Library Locations**: All Tunisian libraries with GPS coordinates
- **User Geolocation**: "Use My Location" button
- **Distance Calculation**: Shows distance in km/meters
- **Auto-sorting**: Libraries sorted by distance
- **Interactive**: Click markers or cards

---

## 🗂️ Project Structure

```
Pi_web/
├── config/                 # Configuration files
│   ├── packages/          # Bundle configurations
│   └── services.yaml      # Service definitions
├── migrations/            # Database migrations
├── public/                # Public assets
│   ├── uploads/          # User uploads (PDFs, images)
│   └── assets/           # Static assets (CSS, JS, images)
├── src/
│   ├── Controller/       # Controllers (Admin, Front)
│   ├── Entity/           # Doctrine entities
│   ├── Form/             # Form types
│   ├── Repository/       # Database repositories
│   ├── Service/          # Business logic services
│   └── Security/         # Authentication
├── templates/            # Twig templates
│   ├── admin/           # Admin interface
│   └── front/           # User interface
└── .env                 # Environment variables
```

---

## 🔑 Key Technical Concepts

### 1. **MVC Architecture**
- **Model**: Entities (Book, Loan, Payment, User)
- **View**: Twig templates
- **Controller**: Handle requests, business logic

### 2. **Doctrine ORM**
- **Entities**: PHP classes mapped to database tables
- **Relationships**: OneToMany, ManyToOne, ManyToMany
- **Migrations**: Version control for database schema

### 3. **Symfony Services**
- **Dependency Injection**: Automatic service wiring
- **Custom Services**: PaymentService, NotificationService, AiAssistantService, FileUploadService

### 4. **Security**
- **Password Hashing**: Bcrypt algorithm
- **CSRF Protection**: Form tokens
- **Role-based Access**: @IsGranted annotations
- **Input Validation**: Symfony Validator

### 5. **API Integration**
- **HTTP Client**: Symfony HttpClient
- **REST API**: JSON requests/responses
- **Error Handling**: Try-catch with fallback
- **Timeout Management**: 15-20 second limits

---

## 💡 Innovative Features Explained

### AI Reading Assistant

**Problem**: Students struggle to understand complex texts while reading.

**Solution**: AI-powered explanations with two modes:

1. **Groq AI Mode**:
   - Uses Llama 3.3 70B model
   - Provides deep, educational explanations
   - Answers specific user questions
   - Defines terms and provides examples

2. **Fallback Mode**:
   - Local PHP analysis (no API)
   - Word/sentence count
   - Language detection (French/English)
   - Keyword extraction
   - Instant results

**Technical Implementation**:
```php
// Service with multi-provider support
class AiAssistantService {
    - configureProvider() // Switch between APIs
    - explainText()       // Main explanation logic
    - getSmartFallbackExplanation() // Local analysis
}
```

**Frontend**:
- PDF.js for rendering
- Text layer for selection
- Toggle switch for mode selection
- Optional question input

---

## 🎨 Design Patterns Used

### 1. **Repository Pattern**
```php
class BookRepository extends ServiceEntityRepository {
    public function findByTitle(string $title) { ... }
}
```

### 2. **Service Layer**
```php
class PaymentService {
    public function processPayment(...) { ... }
    public function validateCard(...) { ... }
}
```

### 3. **Form Type Pattern**
```php
class BookType extends AbstractType {
    public function buildForm(...) { ... }
}
```

### 4. **Strategy Pattern** (AI Providers)
```php
switch ($provider) {
    case 'groq': // Use Groq
    case 'openai': // Use OpenAI
    case 'deepseek': // Use DeepSeek
}
```

---

## 🔒 Security Measures

1. **Authentication**: Symfony Security with password hashing
2. **Authorization**: Role-based access control (ROLE_ADMIN, ROLE_STUDENT)
3. **CSRF Protection**: Form tokens
4. **Input Validation**: Server-side validation
5. **SQL Injection Prevention**: Doctrine ORM parameterized queries
6. **XSS Prevention**: Twig auto-escaping
7. **File Upload Security**: MIME type validation, size limits
8. **Payment Security**: Only last 4 digits stored, Luhn validation

---

## 📊 Database Schema

### Main Tables:
- **users**: User accounts
- **student_profile**: Student-specific data
- **tutor_profile**: Tutor-specific data
- **book**: Books (physical and digital)
- **library**: Physical library locations
- **loan**: Book loan records
- **payment**: Payment transactions
- **digital_purchase**: Digital book purchases
- **notification**: User notifications

### Key Relationships:
- User → Loans (OneToMany)
- Book → Libraries (ManyToMany)
- Loan → Book (ManyToOne)
- Payment → Book (ManyToOne)
- Payment → User (ManyToOne)

---

## 🚀 Deployment & Configuration

### Environment Variables (.env):
```env
DATABASE_URL=mysql://root:@127.0.0.1:3306/nova_db
AI_PROVIDER=groq
AI_API_KEY=your_key_here
```

### Installation Steps:
1. `composer install` - Install dependencies
2. `php bin/console doctrine:database:create` - Create database
3. `php bin/console doctrine:migrations:migrate` - Run migrations
4. `php bin/console cache:clear` - Clear cache
5. `symfony server:start` - Start development server

---

## 📈 Performance Optimizations

1. **Lazy Loading**: Doctrine lazy loading for relationships
2. **Query Optimization**: Custom repository methods
3. **Caching**: Symfony cache for configuration
4. **Asset Optimization**: Minified CSS/JS
5. **PDF Rendering**: Page-by-page rendering (not all at once)
6. **API Timeout**: 15-20 second limits to prevent hanging

---

## 🧪 Testing Scenarios

### User Flows:
1. **Student borrows a book**:
   - Browse books → Select library (map) → Request loan → Wait for approval → Pick up book

2. **Student buys digital book**:
   - Browse books → Purchase → Enter payment → Download PDF → Read with AI assistant

3. **Admin manages loans**:
   - View pending loans → Approve/Reject → Track active loans → Mark as returned

4. **Admin views analytics**:
   - Dashboard → Sales charts → Revenue trends → Top books

---

## 🎓 Learning Outcomes

### Technical Skills:
- ✅ Symfony framework (routing, controllers, services)
- ✅ Doctrine ORM (entities, relationships, migrations)
- ✅ Twig templating
- ✅ RESTful API integration
- ✅ JavaScript (DOM manipulation, async/await)
- ✅ Database design and normalization
- ✅ Security best practices
- ✅ Git version control

### Soft Skills:
- ✅ Problem-solving
- ✅ Project planning
- ✅ Documentation
- ✅ User experience design
- ✅ Error handling and debugging

---

## 🔮 Future Enhancements

1. **Email Notifications**: Send emails for loan status
2. **Book Recommendations**: AI-based suggestions
3. **Reviews & Ratings**: User feedback system
4. **Advanced Search**: Filters, categories, tags
5. **Mobile App**: React Native or Flutter
6. **Real Payment Gateway**: Stripe or PayPal integration
7. **Multi-language**: i18n support
8. **Book Reservations**: Queue system for popular books

---

## 📝 Common Teacher Questions & Answers

### Q: Why Symfony?
**A**: Symfony is a professional, enterprise-grade framework with excellent documentation, strong community support, and follows best practices (SOLID principles, design patterns). It's widely used in the industry.

### Q: Why not use a CMS like WordPress?
**A**: This project requires custom business logic (loan workflow, payment processing, AI integration) that would be difficult to implement in a CMS. A framework gives us full control.

### Q: How does the AI assistant work?
**A**: It uses the Groq API (Llama 3.3 model) to analyze selected text and provide explanations. We send the text via HTTP request, the AI processes it, and returns a detailed explanation. We also have a fallback mode using local PHP analysis.

### Q: Is the payment system secure?
**A**: Yes. We validate card numbers using the Luhn algorithm, only store the last 4 digits, and use HTTPS. In production, we'd integrate a real payment gateway like Stripe.

### Q: How do you handle errors?
**A**: We use try-catch blocks, validate all inputs, provide user-friendly error messages, and have fallback mechanisms (like the AI fallback mode).

### Q: What about scalability?
**A**: The architecture supports scalability: we use services for business logic, repositories for data access, and could easily add caching (Redis), queue systems (RabbitMQ), or move to microservices.

### Q: Why use Leaflet instead of Google Maps?
**A**: Leaflet with OpenStreetMap is free, open-source, and has no API limits. It's perfect for educational projects and provides all the features we need.

---

## 🎯 Project Highlights for Presentation

1. **Complete CRUD Operations**: Full book management
2. **Complex Workflow**: 6-status loan system with business rules
3. **Real-world Integration**: Payment processing, geolocation
4. **Innovation**: AI reading assistant (unique feature!)
5. **Professional UI**: Bootstrap 5, responsive design
6. **Security**: Authentication, authorization, validation
7. **Analytics**: Charts and statistics for decision-making
8. **Scalable Architecture**: Services, repositories, clean code

---

## 📞 Support & Resources

- **Symfony Documentation**: https://symfony.com/doc
- **Doctrine Documentation**: https://www.doctrine-project.org
- **Twig Documentation**: https://twig.symfony.com
- **PDF.js**: https://mozilla.github.io/pdf.js/
- **Leaflet.js**: https://leafletjs.com
- **Groq API**: https://console.groq.com/docs

---

**Project Completed**: February 2026  
**Framework**: Symfony 6.4  
**Database**: MySQL  
**Deployment**: XAMPP (Development)

---

*This project demonstrates proficiency in modern web development, API integration, database design, and innovative problem-solving with AI technology.*
