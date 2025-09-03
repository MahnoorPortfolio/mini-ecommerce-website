# Mini E-commerce Project

A simple, full-stack e-commerce website built with HTML, CSS, JavaScript, and PHP.

## Features

*   **Dynamic Homepage:** Features a rotating hero banner, category navigation, and a showcase of featured products.
*   **Product Catalog:**
    *   Browse all products with sorting and filtering by category.
    *   Detailed product pages with multiple images, full descriptions, and pricing.
*   **Customer Reviews & Ratings:**
    *   Users can rate products on a 5-star scale and write detailed reviews.
    *   Average product ratings are displayed on product listings.
    *   Users can edit or delete their own reviews.
*   **Shopping Cart:**
    *   Add/remove items and update quantities.
    *   A persistent cart for logged-in users.
    *   Calculates and displays the total price.
*   **Wishlist:**
    *   Allows logged-in users to save products for later.
    *   Add/remove items from the wishlist from product or detail pages.
*   **User Authentication:**
    *   Secure user registration and login system.
    *   Session management to keep users logged in.
*   **Checkout Process:**
    *   Multi-step checkout process for collecting shipping and billing information.
    *   Supports multiple payment methods, including "Cash on Delivery" and credit/debit cards.
*   **Order Management:**
    *   Confirmation page summarizing the order details.
    *   Generates a downloadable PDF invoice for each order.
*   **Related Products:**
    *   Suggests other products from the same category on the product detail page.

## Technologies Used

*   **Frontend:**
    *   HTML5
    *   CSS3
    *   JavaScript
*   **Backend:**
    *   PHP
*   **Database:**
    *   MySQL / MariaDB

## Setup and Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/MahnoorPortfolio/mini-ecommerce-website.git
    cd mini-ecommerce-website
    ```

2.  **Database Setup:**

    *   **Step 1: Create the Database**
        *   Open phpMyAdmin (or any other MySQL client).
        *   Create a new database and name it `mini_ecommerce`.

    *   **Step 2: Create Tables**
        *   Select the `mini_ecommerce` database.
        *   Go to the **SQL** tab and run the following script to create all the required tables:

        ```sql
        CREATE TABLE IF NOT EXISTS `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(50) DEFAULT NULL,
          `email` varchar(100) DEFAULT NULL,
          `password` varchar(255) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `username` (`username`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `products` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `price` decimal(10,2) NOT NULL DEFAULT 0.00,
          `image` varchar(255) NOT NULL,
          `category` varchar(100) NOT NULL,
          `description` text NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `wishlist` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `product_id` int(11) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `user_id` (`user_id`,`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `reviews` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `product_id` int(11) NOT NULL,
          `rating` int(11) NOT NULL,
          `review` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `user_id` (`user_id`,`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ```

    *   **Step 3: Import Products**
        *   After setting up the tables, open your web browser and navigate to the following URL to automatically import the product data:
        *   [http://localhost/mini-ecommerce/import_images.php](http://localhost/mini-ecommerce/import_images.php)

    *   **Step 4: Configure Connection (if needed)**
        *   The database connection settings are in `includes/db.php`. The default credentials are set for a standard MAMP installation (`root`/`root`). If your setup is different, update this file accordingly.

3.  **Web Server:**
    *   Make sure you have a web server like Apache (MAMP, XAMPP, WAMP) running.
    *   Place the project files in the web server's root directory (e.g., `htdocs` for MAMP/XAMPP).

4.  **Run the application:**
    *   Open your web browser and navigate to `http://localhost/mini-ecommerce`.

## Usage

Once the application is running, you can browse the products, add them to your cart, and proceed through the checkout process.

## Contributing

Contributions are welcome! Please feel free to submit a pull request.

1.  Fork the Project
2.  Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the Branch (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
