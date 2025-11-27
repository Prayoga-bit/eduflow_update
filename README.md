# 🎓 EduFlow - Educational Management Platform

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-8BC34A?style=for-the-badge&logo=alpine.js&logoColor=white)

*A comprehensive educational platform designed to empower students with productivity tools and collaborative learning features.*

</div>

## 📖 About EduFlow

EduFlow is a modern educational management platform built with Laravel that provides students with essential tools for academic success. It combines productivity features like task management, note-taking, and Pomodoro timers with engaging social learning through interactive forums.

### ✨ Key Features

- **📝 Task Management**: Organize assignments and projects with a kanban-style board
- **⏱️ Pomodoro Timer**: Built-in focus timer to enhance productivity
- **📔 Note Taking**: Create, organize, and tag personal notes
- **💬 Interactive Forums**: Engage in discussions and collaborative learning
- **👥 Group Management**: Create and join study groups for specific topics
- **📎 File Attachments**: Share resources and materials within discussions
- **🏷️ Tagging System**: Organize content with customizable tags
- **🔐 User Authentication**: Secure login and user management

## 🚀 Features Overview

### 📋 Task Management System
- **Kanban Board**: Visual task organization with drag-and-drop functionality
- **Task Status Tracking**: Todo, In Progress, In Review, and Done columns
- **Priority Management**: Set and track task priorities
- **Due Date Management**: Never miss a deadline with built-in scheduling

### ⏰ Pomodoro Timer
- **Focus Sessions**: 25-minute focused work intervals
- **Break Tracking**: Automatic break reminders
- **Session History**: Track your productivity over time
- **User-specific Timers**: Personal timer management for each user

### 📝 Smart Note-Taking
- **Rich Text Editor**: Create formatted notes with ease
- **Tag Organization**: Categorize notes with custom tags
- **Search Functionality**: Quickly find specific notes
- **Personal Workspace**: Private note space for each user

### 🌐 Community Forums
- **Discussion Groups**: Topic-based forum groups
- **Post & Reply System**: Threaded conversations
- **Media Sharing**: Upload and share files within discussions
- **Group Moderation**: Organized community management
- **Real-time Engagement**: Active participation tracking

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 12.0
- **Language**: PHP 8.2+
- **Database**: SQLite (configurable to MySQL/PostgreSQL)
- **Authentication**: Laravel Sanctum

### Frontend
- **CSS Framework**: Tailwind CSS 3.4
- **JavaScript**: Alpine.js 3.14
- **Icons**: Heroicons
- **Build Tool**: Vite 6.3

### Development Tools
- **Package Manager**: Composer & NPM
- **Code Quality**: Laravel Pint
- **Testing**: PHPUnit
- **API**: RESTful endpoints

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- Web server (Apache/Nginx) or Laravel's built-in server

### Step-by-Step Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url> eduflow
   cd eduflow
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed  # Optional: seed with sample data
   ```

6. **Build Frontend Assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

7. **Start the Application**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access EduFlow!

## ⚙️ Configuration

### Database Configuration
Update your `.env` file with your database credentials:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# Or for MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eduflow
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Forum Settings
Configure forum-specific settings in `config/forum.php` or via environment variables:
```env
FORUM_ENABLE_EMAIL_VERIFICATION=true
FORUM_ENABLE_RECAPTCHA=false
FORUM_MAX_UPLOAD_SIZE=10240
FORUM_ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf,doc,docx
```

## 🎯 Usage Guide

### Getting Started
1. **Register/Login**: Create your account or sign in
2. **Dashboard**: Access your personalized dashboard
3. **Create Tasks**: Start organizing your assignments
4. **Join Forums**: Participate in subject-specific discussions
5. **Take Notes**: Document your learning journey

### Task Management
- Navigate to the Tasks section
- Create new tasks with titles, descriptions, and due dates
- Drag tasks between columns to update their status
- Use the Pomodoro timer to focus on specific tasks

### Forum Participation
- Browse available forum groups
- Join groups relevant to your subjects
- Create posts to start discussions
- Reply to engage with the community
- Upload files to share resources

### Note Organization
- Access the Notes section from your dashboard
- Create categorized notes with tags
- Use the search feature to find specific content
- Export or share notes as needed

## 🏗️ Project Structure

```
eduflow/
├── app/
│   ├── Http/Controllers/    # Request handling logic
│   ├── Models/             # Database models (User, Task, ForumPost, etc.)
│   ├── Services/           # Business logic services
│   ├── Policies/           # Authorization policies
│   └── Traits/             # Reusable code traits
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database schema definitions
│   ├── factories/          # Model factories for testing
│   └── seeders/            # Database seeders
├── resources/
│   ├── views/              # Blade templates
│   ├── css/                # Stylesheets
│   └── js/                 # JavaScript files
├── routes/                 # Application routes
└── tests/                  # Automated tests
```

## 🔧 Development

### Running in Development Mode
```bash
# Start the Laravel development server
php artisan serve

# Watch for frontend changes
npm run dev

# Run tests
php artisan test
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run static analysis
./vendor/bin/phpstan analyse
```

## 🤝 Contributing

We welcome contributions to EduFlow! Here's how you can help:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Commit your changes** (`git commit -m 'Add amazing feature'`)
4. **Push to the branch** (`git push origin feature/amazing-feature`)
5. **Open a Pull Request**

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use meaningful commit messages

## 📝 API Documentation

EduFlow provides RESTful API endpoints for all major features:

- **Authentication**: `/api/auth/*`
- **Tasks**: `/api/tasks/*`
- **Forums**: `/api/forums/*`
- **Notes**: `/api/notes/*`
- **Timers**: `/api/timers/*`

Detailed API documentation is available after installation at `/api/documentation`.

## 🔒 Security

EduFlow implements several security measures:
- CSRF protection on all forms
- XSS protection with input sanitization
- SQL injection prevention via Eloquent ORM
- Secure file upload handling
- Rate limiting on API endpoints

## 📊 Performance

- **Caching**: Redis/File-based caching for optimal performance
- **Database Optimization**: Indexed queries and eager loading
- **Asset Optimization**: Minified CSS/JS with Vite
- **Image Optimization**: Automatic image compression for uploads

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
```bash
php artisan config:clear
php artisan migrate:fresh
```

**Asset Build Issues**
```bash
npm run build
php artisan config:clear
```

**Permission Issues**
```bash
chmod -R 775 storage bootstrap/cache
```

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Laravel Community** for the excellent framework
- **Tailwind CSS** for the utility-first styling approach
- **Alpine.js** for lightweight frontend interactivity
- **Heroicons** for beautiful, consistent icons

## 📞 Support

- **Documentation**: Check this README and inline code comments
- **Issues**: Report bugs via GitHub Issues
- **Discussions**: Join community discussions for feature requests

---

<div align="center">

**Built with ❤️ for students, by developers who understand the academic journey**

[🌟 Star this repo](https://github.com/your-repo/eduflow) | [🐛 Report Bug](https://github.com/your-repo/eduflow/issues) | [💡 Request Feature](https://github.com/your-repo/eduflow/issues)

</div>