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
    *   Create a new database in your MySQL/MariaDB server.
    *   Import the `database.sql` file (if provided) to set up the necessary tables.
    *   Update the database connection details in `config.php` (or your relevant configuration file).

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
