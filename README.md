
Built by https://www.blackbox.ai

---

# Rental Vehicle Management System

## Project Overview
The Rental Vehicle Management System is a web application created to facilitate the renting of vehicles such as buses and cars. Users can view available vehicles, access detailed information for each vehicle, and get in touch for bookings. This project features a user-friendly interface designed to enhance the customer experience with functionalities like filtering available vehicles and real-time GPS tracking.

## Installation
To install the Rental Vehicle Management System, follow these steps:
1. Ensure you have a local server environment like XAMPP, MAMP, or similar that supports PHP.
2. Clone the repository or download the project files from the source.
   ```bash
   git clone https://github.com/yourusername/rental-vehicle-management.git
   ```
3. Place the project directory in the `htdocs` folder (for XAMPP) or the appropriate directory for your server setup.
4. Configure the database:
   - Create a new database in your MySQL server.
   - Import the database schema (not included, but you may create tables based on PHP PDO queries).
5. Update the `includes/config.php` file to connect to your database:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'username');
   define('DB_PASS', 'password');
   define('DB_NAME', 'database_name');
   ```

## Usage
1. Start your local server (XAMPP, MAMP, etc.).
2. Open your web browser and navigate to `http://localhost/rental-vehicle-management/`.
3. Browse the available vehicles, filter by type (bus or car), and view detailed information for each vehicle.
4. For booking inquiries, follow the contact links provided in the vehicle details.

## Features
- View a list of available vehicles with filtering options (all, bus, car).
- Detailed information for each vehicle, including facilities and GPS tracking (if available).
- Contact options via WhatsApp and phone for rental inquiries.
- Support for real-time updates on vehicle location (GPS tracking).

## Dependencies
This project uses the following dependencies:
- PHP (7.3 or higher recommended)
- MySQL (for the database)
- Bootstrap (for responsive design)
- Font Awesome (for icons)

## Project Structure
```
├── index.php                # Main landing page displaying available vehicles
├── vehicle_detail.php       # Page for detailed vehicle information
├── includes/                # Directory for included files such as configurations, database connections, and functions
│   ├── config.php           # Database configuration settings
│   ├── db.php               # Database connection handling
│   └── functions.php        # General helper functions
```
This basic structure includes the main files required for the functioning of the application alongside the included configuration files.

## License
This project is licensed under the MIT License. Please feel free to modify and distribute as needed, provided original credits are maintained.