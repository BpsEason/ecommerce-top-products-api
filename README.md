# Ecommerce Top Products API

**Description**: A PHP-based API for displaying the top 10 best-selling products on an ecommerce platform, optimized with a cached table for non-real-time updates.

## Features
- Returns the top 10 best-selling products with details like name, price, image URL, and sales count.
- Built with PHP 7.4+ and MySQL 5.7+.
- Uses a cached table (`top_products_cache`) to reduce database load, updated hourly via a Cron job.
- Supports UTF-8 for multilingual product names.

## Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/BpsEason/ecommerce-top-products-api.git
   ```
2. Import the database schema:
   ```bash
   mysql -u root -p ecommerce < sql/schema.sql
   ```
3. Configure database credentials in `.env` or `src/config.php`.
4. Install dependencies (if using `phpdotenv`):
   ```bash
   composer require vlucas/phpdotenv
   ```
5. Set up a Cron job to run `update_top_products.php` hourly:
   ```bash
   0 * * * * /usr/bin/php /path/to/src/update_top_products.php >> /var/log/top_products_update.log 2>&1
   ```

## API Endpoint
- **GET /api/v1/top-products**
  - **Response Example**:
    ```json
    {
        "success": true,
        "data": [
            {
                "product_id": 1,
                "name": "Laptop",
                "price": 999.99,
                "image_url": "https://example.com/images/laptop.jpg",
                "sales_count": 150
            },
            ...
        ],
        "message": "Top products retrieved successfully"
    }
    ```

## Project Structure
```
/ecommerce-top-products-api
├── src/
│   ├── api.php
│   ├── update_top_products.php
│   └── config.php
├── sql/
│   └── schema.sql
├── .env.example
├── .gitignore
├── LICENSE
└── README.md
```

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (e.g., Apache, Nginx)
- Optional: Composer for dependency management

## Configuration
1. Copy `.env.example` to `.env` and update database credentials:
   ```
   DB_HOST=localhost
   DB_NAME=ecommerce
   DB_USER=root
   DB_PASS=password
   ```
2. Ensure the web server has write permissions for logs.

## Testing
- Test the API using `curl` or Postman:
  ```bash
  curl http://your-server/api/v1/top-products
  ```
- Verify the Cron job logs in `/var/log/top_products_update.log`.

## Contributing
Contributions are welcome! Please open an issue or submit a pull request for bug fixes or new features.

## License
[MIT License](LICENSE)