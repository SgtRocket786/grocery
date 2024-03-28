<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
};

if (isset($_POST['add_product'])) {

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $price = $_POST['price'];
    $price = filter_var($price, FILTER_SANITIZE_STRING);
    $category = $_POST['category'];
    $category = filter_var($category, FILTER_SANITIZE_STRING);
    $details = $_POST['details'];
    $details = filter_var($details, FILTER_SANITIZE_STRING);

    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;

    $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
    $select_products->execute([$name]);

    if ($select_products->rowCount() > 0) {
        $message[] = 'product name already exist!';
    } else {

        $insert_products = $conn->prepare("INSERT INTO `products`(name, category, details, price, image) VALUES(?,?,?,?,?)");
        $insert_products->execute([$name, $category, $details, $price, $image]);

        if ($insert_products) {
            if ($image_size > 2000000) {
                $message[] = 'image size is too large!';
            } else {
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'new product added!';
            }
        }
    }
};

if (isset($_GET['delete'])) {

    $delete_id = $_GET['delete'];
    $select_delete_image = $conn->prepare("SELECT image FROM `products` WHERE id = ?");
    $select_delete_image->execute([$delete_id]);
    $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);
    unlink('uploaded_img/' . $fetch_delete_image['image']);
    $delete_products = $conn->prepare("DELETE FROM `products` WHERE id = ?");
    $delete_products->execute([$delete_id]);
    $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
    $delete_wishlist->execute([$delete_id]);
    $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
    $delete_cart->execute([$delete_id]);
    header('location:admin_products.php');
}

// Sorting products by category
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

<?php include 'admin_header.php'; ?>

<section class="add-products">

    <h1 class="title">Add New Product</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="flex">
            <div class="inputBox">
                <input type="text" name="name" class="box" required placeholder="Enter Product Name">
                <select name="category" class="box" required>
                    <option value="" selected>Select Category</option>
                    <option value="vegetables">Vegetables</option>
                    <option value="fruits">Fruits</option>
                    <option value="meat">Meat</option>
                    <option value="fish">Fish</option>
                </select>
            </div>
            <div class="inputBox">
                <input type="number" min="0" name="price" class="box" required placeholder="Enter Product Price">
                <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png">
            </div>
        </div>
        <textarea name="details" class="box" required placeholder="Enter Product Details" cols="30" rows="10"></textarea>
        <input type="submit" class="btn" value="Add Product" name="add_product">
    </form>

</section>

<section class="show-products">

    <h1 class="title">Products Added</h1>

    <div class="category-dropdown">
        <label for="category">Select Category:</label>
        <select name="category" id="category">
            <option value="">All</option>
            <option value="vegetables">Vegetables</option>
            <option value="fruits">Fruits</option>
            <option value="meat">Meat</option>
            <option value="fish">Fish</option>
        </select>
    </div>

    <div class="box-container" id="product-container">
        <!-- Product items will be dynamically loaded here -->
    </div>

</section>

<script>
    // Function to fetch and display products based on selected category
    function fetchProductsByCategory(category) {
        const productContainer = document.getElementById('product-container');
        productContainer.innerHTML = '<p>Loading...</p>';
        // Fetch products based on selected category
        fetch('fetch_products.php?category=' + category)
            .then(response => response.text())
            .then(data => {
                productContainer.innerHTML = data;
            })
            .catch(error => console.error('Error fetching products:', error));
    }

    // Event listener for category dropdown change
    document.getElementById('category').addEventListener('change', function() {
        const selectedCategory = this.value;
        fetchProductsByCategory(selectedCategory);
    });

    // Initial fetch of products when page loads
    fetchProductsByCategory('');
</script>

</body>

</html>

