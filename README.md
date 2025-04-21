# Restaurant Website with Admin Panel

A complete restaurant website with food ordering system, table reservation, cart functionality, and admin panel.

## Features

- User Authentication (Login/Register)
- Menu Display with Categories
- Shopping Cart
- Online Food Ordering
- Table Reservation System
- Online Payment Integration (PayPal)
- Admin Panel
  - Dashboard with Statistics
  - Order Management
  - Reservation Management
  - Menu Management
  - User Management
  - Category Management

## Technologies Used

- PHP 7.4+
- MySQL 5.7+
- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- Font Awesome
- PayPal API

## Setup Instructions

1. **Database Setup**
   - Create a new MySQL database named `restaurant_db`
   - Import the `database.sql` file to create the required tables

2. **Configuration**
   - Update database connection details in `config/db_connect.php`
   - Update PayPal client ID in cart.php (if using PayPal integration)

3. **Server Requirements**
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Apache/Nginx web server
   - mod_rewrite enabled (for Apache)

4. **Installation Steps**
   ```bash
   # Clone the repository
   git clone [repository-url]

   # Move to web server directory (e.g., for WAMP)
   move * C:/wamp64/www/restaurant/

   # Set permissions (for Linux/Mac)
   chmod 755 -R *
   chmod 777 -R uploads/
   ```

5. **Admin Account**
   - Default admin credentials:
     - Username: admin
     - Password: admin123
   - Change these credentials after first login

## Directory Structure

```
restaurant/
├── admin/             # Admin panel files
├── assets/
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   └── images/       # Image files
├── config/           # Configuration files
├── includes/         # Common include files
├── uploads/          # Uploaded files
├── database.sql      # Database structure
└── README.md         # This file
```

## Security Considerations

1. Change default admin credentials
2. Keep configuration files secure
3. Use prepared statements for database queries
4. Validate and sanitize all user inputs
5. Implement CSRF protection
6. Use secure password hashing

## Contributing

1. Fork the repository
2. Create a new branch
3. Make your changes
4. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, email support@restaurant.com or create an issue in the repository. 