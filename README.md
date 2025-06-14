# Ecommerce Top Products API

**A lightweight PHP-based API for retrieving the top 10 best-selling products on an ecommerce platform, optimized with a cached database table for non-real-time updates.**

This API provides a simple and efficient way to display the top 10 products based on sales volume over the past 30 days. It uses a MySQL database with a cached table to reduce query load and a scheduled task to update rankings hourly. Built with PHP 7.4+ and MySQL 5.7+, it ensures compatibility and performance for small to medium-sized ecommerce applications.

## Features
- **Top 10 Products Endpoint**: Returns product details including ID, name, price, image URL, and sales count.
- **Cached Data**: Uses a `top_products_cache` table to store precomputed rankings, minimizing database load.
- **Hourly Updates**: A scheduled task updates the cache every hour via a Cron job, ensuring fresh data without real-time overhead.
- **Multilingual Support**: Configured with UTF-8 (utf8mb4) for handling multilingual product names.
- **Robust Error Handling**: Includes detailed logging and JSON error responses for easy debugging.
- **Secure Configuration**: Supports environment variables via `.env` for sensitive database credentials.

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (e.g., Apache, Nginx)
- Composer for dependency management
- Cron (for scheduled tasks)
- Optional: Redis for advanced caching (not implemented in this version)

### Steps
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/BpsEason/ecommerce-top-products-api.git
   cd ecommerce-top-products-api
   ```

2. **Install Dependencies**:
   Install required PHP packages using Composer:
   ```bash
   composer require vlucas/phpdotenv monolog/monolog
   ```

3. **Configure Environment**:
   Copy the example environment file and update it with your database credentials:
   ```bash
   cp .env.example .env
   ```
   Edit `.env` with your settings:
   ```
   DB_HOST=localhost
   DB_NAME=ecommerce
   DB_USER=your_db_user
   DB_PASS=your_db_password
   ```

4. **Set Up Database**:
   Import the provided SQL schema to create the necessary tables:
   ```bash
   mysql -u your_db_user -p ecommerce < sql/schema.sql
   ```

5. **Configure Logging**:
   Create a logs directory and set appropriate permissions:
   ```bash
   mkdir logs
   chmod 775 logs
   ```

6. **Set Up Cron Job**:
   Schedule the `corrected_update_top_products.php` script to run hourly:
   ```bash
   crontab -e
   ```
   Add the following line:
   ```
   0 * * * * /usr/bin/php /path/to/ecommerce-top-products-api/src/corrected_update_top_products.php >> /path/to/ecommerce-top-products-api/logs/cron.log 2>&1
   ```

7. **Deploy API**:
   - Place the project in your web server's document root (e.g., `/var/www/html`).
   - Ensure `src/corrected_api.php` is accessible via your domain (e.g., `http://your-server/api/v1/top-products`).
   - Configure your web server to handle PHP requests (see Apache/Nginx examples below).

   **Apache Example**:
   Ensure `.htaccess` is enabled and PHP is configured in your Apache setup.

   **Nginx Example**:
   ```nginx
   server {
       listen 80;
       server_name your-server;
       root /path/to/ecommerce-top-products-api/src;
       index corrected_api.php;

       location ~ \.php$ {
           include fastcgi_params;
           fastcgi_pass unix:/run/php/php7.4-fpm.sock; # Adjust for your PHP version
           fastcgi_index corrected_api.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       }
   }
   ```

## Usage

### API Endpoint
- **GET /api/v1/top-products**
  - Retrieves the top 10 best-selling products from the cache.
  - **Response Format**: JSON
  - **Example Request**:
    ```bash
    curl http://your-server/api/v1/top-products
    ```
  - **Example Response**:
    ```json
    {
        "success": true,
        "data": [
            {
                "product_id": 1,
                "name": "Laptop Pro",
                "price": 999.99,
                "image_url": "https://example.com/images/laptop.jpg",
                "sales_count": 150
            },
            ...
        ],
        "message": "Top products retrieved successfully"
    }
    ```
  - **Error Responses**:
    - **404 Not Found**:
      ```json
      {
          "success": false,
          "error": "Not Found",
          "message": "The requested resource was not found"
      }
      ```
    - **500 Server Error**:
      ```json
      {
          "success": false,
          "error": "Server error",
          "message": "Database connection or query failed"
      }
      ```

### Cache Update
- The `corrected_update_top_products.php` script updates the `top_products_cache` table hourly.
- It calculates sales from `order_items` for orders with status `shipped` or `delivered` over the past 30 days.
- Logs are written to `logs/app.log` and `logs/cron.log` for monitoring.

## Project Structure
```
/ecommerce-top-products-api
├── src/
│   ├── config.php              # Database and environment configuration
│   ├── corrected_api.php       # API endpoint for retrieving top products
│   ├── corrected_update_top_products.php # Script for updating cache
├── sql/
│   └── schema.sql             # Database schema
├── logs/
│   ├── app.log               # Application logs
│   ├── cron.log              # Cron job logs
├── vendor/                    # Composer dependencies
├── .env                       # Environment variables (not committed)
├── .env.example               # Example environment file
├── .gitignore                 # Git ignore file
├── composer.json              # Composer configuration
├── LICENSE                    # License file
└── README.md                  # Project documentation
```

## Requirements
- **PHP**: 7.4 or higher with PDO and MySQL extensions
- **MySQL**: 5.7 or higher
- **Composer**: For dependency management
- **Web Server**: Apache or Nginx
- **Cron**: For scheduling cache updates
- **File Permissions**: Write access for `logs/` directory

## Testing
1. **API Testing**:
   Use `curl` or Postman to test the endpoint:
   ```bash
   curl http://your-server/api/v1/top-products
   ```
   Verify the response contains `success: true` and a list of products.

2. **Cache Update Testing**:
   Manually run the update script:
   ```bash
   php src/corrected_update_top_products.php
   ```
   Check `logs/app.log` for success messages and `top_products_cache` table for updated data.

3. **Database Verification**:
   Query the cache table:
   ```sql
   SELECT * FROM top_products_cache ORDER BY rank_order ASC;
   ```
   Ensure data matches expected rankings.

## Contributing
Contributions are welcome! To contribute:
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/your-feature`).
3. Commit your changes (`git commit -m "Add your feature"`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request with a clear description of your changes.

Please report bugs or suggest improvements via GitHub Issues.

## License
This project is licensed under the [MIT License](LICENSE).

## Acknowledgments
- Built with [PHP](https://www.php.net/), [MySQL](https://www.mysql.com/), and [Composer](https://getcomposer.org/).
- Uses [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) for environment management and [monolog/monolog](https://github.com/Seldaek/monolog) for logging.
- Inspired by common ecommerce API patterns for performance and scalability.

---

**Contact**: For questions or support, open an issue on GitHub or contact yourusername@example.com.