<?php

// Include database connection or config file
@include 'config.php';

// Get the selected category from the query string
$category = $_GET['category'];

// Prepare SQL query to fetch products based on the selected category
$query = "SELECT * FROM `products`";
$params = [];

// If a category is selected, add a WHERE clause to the query
if (!empty($category)) {
    $query .= " WHERE category = ?";
    $params[] = $category;
}

// Prepare and execute the query
$show_products = $conn->prepare($query);
$show_products->execute($params);

// Check if products are found
if ($show_products->rowCount() > 0) {
    // Loop through each product and generate HTML markup
    while ($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="box">';
        echo '<div class="price">$' . $fetch_products['price'] . '/-</div>';
        echo '<img src="uploaded_img/' . $fetch_products['image'] . '" alt="" style="width: 100px;">';
        echo '<div class="name">' . $fetch_products['name'] . '</div>';
        echo '<div class="cat">' . $fetch_products['category'] . '</div>';
        echo '<div class="details">' . $fetch_products['details'] . '</div>';
        echo '<div class="flex-btn">';
        echo '<a href="admin_update_product.php?update=' . $fetch_products['id'] . '" class="option-btn">Update</a>';
        echo '<a href="admin_products.php?delete=' . $fetch_products['id'] . '" class="delete-btn" onclick="return confirm(\'Delete this product?\');">Delete</a>';
        echo '</div>';
        echo '</div>';
    }
} else {
    // If no products found, display a message
    echo '<p class="empty">No Products Found</p>';
}
?>
