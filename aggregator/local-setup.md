## To set-up for MAC M chips locally
---
1. Download and Install XAMPP
   * Download and install XAMPP with PHP version 7.1.0.

2. Clone Repository:
   * Clone the project repository using Git.

3. Import Databases:
   * Import the databases named `feeder` into your MySQL server.

4. Rename .env_prod or .env_dev file as .env

5. Run ./secript/secrets.sh

6. Make sure you are on VPN and your Redis and xampp server is running

7. Run composer install

8. Run php artisan key:generate

9. Run php artisan serve

10. Above command will give a URL like `http://localhost:8000`
   * Open this URL on your preferred web browser.

11. Project Setup Complete:
   * Your local website is now ready for development and testing.
