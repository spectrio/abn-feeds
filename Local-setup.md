## To set-up for MAC M chips locally
---
1. Download and Install XAMPP
   * Download and install XAMPP with PHP version 7.1.0.

2. Clone Repository:
   * Clone the project repository using Git.

3. Import Databases:
   * Import the databases named `feeder` into your MySQL server.

4. Configure dotenv library first follow instructions from
   dotenvLibrary_setup.md

5. Create Virtual Host:
   * Create a virtual host on your local server for example `abn.scalafeeds.com`.

6. Rename .env_prod or .env_dev file as .env

7. Run ./secript/secrets.sh

8. Make sure you are on VPN and your Redis and xampp server is running

9. Open `abn.scalafeeds.com` URL on your preferred web browser.

10. Project Setup Complete:
   * Your local website is now ready for development and testing.

11. You can replace `GM_ENDPOINT` and `REMOTE_BETA_ENDPOINT` 
        URL in .env if you want your application to use local environment instead of dev. 
