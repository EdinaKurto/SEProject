<?php
/* header("Access-Control-Allow-Origin: https://cuppatea-frontend-gjxs4.ondigitalocean.app");
 header("Access-Control-Allow-Methods: GET,PUT,POST,DELETE,PATCH,OPTIONS");
 header("Access-Control-Allow-Headers: Content-Type, Authorization, Authentication");
 header("Access-Control-Allow-Credentials", "true"); */
 require_once __DIR__ . '/../services/ProductService.php';
 require_once __DIR__ . '/../../utils/MessageHandler.php';
 require_once __DIR__ . '/../../vendor/autoload.php';

use Aws\S3\S3Client;
 
 use Firebase\JWT\JWT;
 use Firebase\JWT\Key;
 
 Flight::set('product_service', new ProductService());
 
 Flight::group('/products', function() {
 
     /**
     * @OA\Post(
     *     path="/products/add",
     *     summary="Add a new product.",
     *     description="Creates a new product and returns the created product in the response.",
     *     tags={"Products"},
     *     security={{"ApiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "category_id", "quantity", "price_each", "description"},
     *             @OA\Property(property="name", type="string", example="Mint Tea", description="Name of the product"),
     *             @OA\Property(property="category_id", type="integer", example=1, description="ID of the category the product belongs to"),
     *             @OA\Property(property="quantity", type="integer", example=50, description="Available quantity of the product"),
     *             @OA\Property(property="price_each", type="number", format="float", example=21.50, description="Price of each unit of the product"),
     *             @OA\Property(property="description", type="string", example="Premium mint tea.", description="Description of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Mint Tea", description="Name of the product"),
     *             @OA\Property(property="category_id", type="integer", example=1, description="ID of the category the product belongs to"),
     *             @OA\Property(property="quantity", type="integer", example=50, description="Available quantity of the product"),
     *             @OA\Property(property="price_each", type="number", format="float", example=21.50, description="Price of each unit of the product"),
     *             @OA\Property(property="description", type="string", example="Premium mint tea.", description="Description of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input")
     *         )
     *     ),
     * )
     */
    Flight::route('POST /add', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
        $data = Flight::request()->data->getData();

        $required_fields = ['name', 'category_id', 'quantity', 'price_each', 'description'];

        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                Flight::halt(400, "Field '$field' is required.");
            }

            if (is_string($data[$field]) && trim($data[$field]) === '') {
                Flight::halt(400, "Field '$field' cannot be empty.");
            }
        }

        if (!is_numeric($data['quantity']) || intval($data['quantity']) < 0) {
            Flight::halt(400, "'quantity' must be a non-negative integer.");
        }

        if (!is_numeric($data['price_each']) || floatval($data['price_each']) <= 0) {
            Flight::halt(400, "'price_each' must be a positive number.");
        }

        $category = Flight::get('category_service')->get_category_by_id($data['category_id']);
        if (!$category) {
            Flight::halt(400, "Category with ID {$data['category_id']} does not exist.");
        }


        $product = [
            'name' => trim($data['name']),
            'category_id' => intval($data['category_id']),
            'quantity' => intval($data['quantity']),
            'price_each' => floatval($data['price_each']),
            'description' => trim($data['description'])
        ];

        $inserted_product = Flight::get('product_service')->add_product($product);
        MessageHandler::handleServiceResponse($inserted_product);
    });

 
     /**
     * @OA\Get(
     *     path="/products/{id}",
     *     summary="Get product details by ID.",
     *     description="Fetches the details of a specific product by its ID.",
     *     tags={"Products"},
     *     security={{"ApiKey": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to fetch",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched product details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=5, description="ID of the product"),
     *             @OA\Property(property="name", type="string", example="Mint Tea", description="Name of the product"),
     *             @OA\Property(property="category", type="string", example="Green Teas", description="ID of the category the product belongs to"),
     *             @OA\Property(property="quantity", type="string", example="50", description="Available quantity of the product"),
     *             @OA\Property(property="price_each", type="string", example="21.50", description="Price of each unit of the product"),
     *             @OA\Property(property="description", type="string", example="Premium mint tea.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input")
     *         )
     *     ),
     * )
     */
     Flight::route('GET /@id', function ($id) {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $product = Flight::get('product_service')->get_product_by_id($id);
        MessageHandler::handleServiceResponse($product);
     });
 
     /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get all products.",
     *     description="Fetches a list of all products, including their details.",
     *     tags={"Products"},
     *     security={{"ApiKey": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter products by name or description",
     *         @OA\Schema(type="string", example="Green")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         required=false,
     *         description="Sort order for the products (e.g., 'price_asc', 'price_desc')",
     *         @OA\Schema(type="string", example="price_asc")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         required=false,
     *         description="Minimum price to filter products",
     *         @OA\Schema(type="number", format="float", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         required=false,
     *         description="Maximum price to filter products",
     *         @OA\Schema(type="number", format="float", example=100)
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         description="Category ID to filter products",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched all products",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1, description="ID of the product"),
     *         @OA\Property(property="name", type="string", example="Green Tea", description="Name of the product"),
     *         @OA\Property(property="category_name", type="string", example="White Teas", description="Name of the category the product belongs to"),
     *         @OA\Property(property="quantity", type="integer", example=22, description="Available quantity of the product"),
     *         @OA\Property(property="price_each", type="number", format="float", example=22.1, description="Price of each unit of the product"),
     *         @OA\Property(property="description", type="string", example="Description", description="Description of the product")
     *     )

     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Server error")
     *         )
     *     )
     * )
     */
     Flight::route('GET /', function () {
         Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
         $search = Flight::request()->query['search'] ?? null;
         $sort = Flight::request()->query['sort'] ?? null;
         $min_price = Flight::request()->query['min_price'] ?? null;
         $max_price = Flight::request()->query['max_price'] ?? null;
         $category_id = Flight::request()->query['category_id'] ?? null;
     
         $products = Flight::get('product_service')->get_all_products($search, $sort, $min_price, $max_price, $category_id);
     
         MessageHandler::handleServiceResponse($products);

     });



 
 
     /**
     * @OA\Delete(
     *     path="/products/delete/{product_id}",
     *     summary="Delete a product by ID.",
     *     description="Deletes a product based on the provided product ID.",
     *     tags={"Products"},
     *     security={{"ApiKey": {}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product successfully deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You have successfully deleted the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid product ID")
     *         )
     *     ),
     * )
     */
     Flight::route('DELETE /delete/@product_id', function ($product_id) {
        Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
         $product_service = new productService();
         $result = $product_service->delete_product($product_id);
         MessageHandler::handleServiceResponse($result, "You have successfully deleted the product");
     });
     
     /**
     * @OA\Put(
     *     path="/products/update/{id}",
     *     summary="Update a product by ID.",
     *     description="Updates the details of an existing product based on the provided product ID.",
     *     tags={"Products"},
     *     security={{"ApiKey": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to update",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "category_id", "quantity", "price_each", "description"},
     *             @OA\Property(property="name", type="string", example="Minty Tea", description="Updated name of the product"),
     *             @OA\Property(property="category_id", type="integer", example=1, description="Updated category ID of the product"),
     *             @OA\Property(property="quantity", type="integer", example=50, description="Updated available quantity of the product"),
     *             @OA\Property(property="price_each", type="number", format="float", example=29.99, description="Updated price of each unit of the product"),
     *             @OA\Property(property="description", type="string", example="Updated mint tea.", description="Updated description of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product successfully updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=5, description="ID of the updated product"),
     *             @OA\Property(property="name", type="string", example="Minty Tea", description="Updated name of the product"),
     *             @OA\Property(property="category_id", type="integer", example=1, description="Updated category ID of the product"),
     *             @OA\Property(property="quantity", type="integer", example=50, description="Updated available quantity of the product"),
     *             @OA\Property(property="price_each", type="number", format="float", example=29.99, description="Updated price of each unit of the product"),
     *             @OA\Property(property="description", type="string", example="Updated mint tea.", description="Updated description of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input")
     *         )
     *     ),
     * )
     */
    Flight::route('PUT /update/@id', function($id) {
        Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
        $data = Flight::request()->data->getData();

        if (!is_numeric($id) || intval($id) <= 0) {
            Flight::halt(400, "Invalid product ID.");
        }

        // ✅ Check if the product exists
        $existing_product = Flight::get('product_service')->get_product_by_id($id);
        if (!$existing_product) {
            Flight::halt(404, "Product not found.");
        }

        $required_fields = ['name', 'category_id', 'quantity', 'price_each', 'description'];

        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                Flight::halt(400, "Field '$field' is required.");
            }

            if (is_string($data[$field]) && trim($data[$field]) === '') {
                Flight::halt(400, "Field '$field' cannot be empty.");
            }
        }

        if (!is_numeric($data['quantity']) || intval($data['quantity']) < 0) {
            Flight::halt(400, "'quantity' must be a non-negative integer.");
        }

        if (!is_numeric($data['price_each']) || floatval($data['price_each']) <= 0) {
            Flight::halt(400, "'price_each' must be a positive number.");
        }

        $category = Flight::get('category_service')->get_category_by_id($data['category_id']);
        if (!$category) {
            Flight::halt(400, "Category with ID {$data['category_id']} does not exist.");
        }


        // --- If all checks pass, update the product ---
        $product = [
            'name' => trim($data['name']),
            'category_id' => intval($data['category_id']),
            'quantity' => intval($data['quantity']),
            'price_each' => floatval($data['price_each']),
            'description' => trim($data['description'])
        ];

        $updated_product = Flight::get('product_service')->update_product(intval($id), $product);
        MessageHandler::handleServiceResponse($updated_product);
    });



Flight::route('POST /upload_image/@product_id', function($product_id) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
    
    if (!isset($_FILES['product_image'])) {
        Flight::halt(400, 'No file uploaded.');
    }

    $file = $_FILES['product_image'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        Flight::halt(400, 'Only JPG, PNG, or WEBP images are allowed.');
    }

    // S3 konfiguracija
    $bucket = 'cuppatea-uploads';
    $region = 'fra1';
    $endpoint = "https://fra1.digitaloceanspaces.com";

    $s3 = new S3Client([
        'version' => 'latest',
        'region'  => $region,
        'endpoint' => $endpoint,
        'credentials' => [
            'key'    => 'DO801T4YF42P8Y7W3686',
            'secret' => 'Id3nl07Ji3+Q3XUP10twjq2uZEQICP47/6rE7thIn7A',
        ],
    ]);

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_name = uniqid("product_", true) . '.' . $ext;
    $key = "uploads/{$new_name}";
    $url = "https://{$bucket}.{$region}.digitaloceanspaces.com/{$key}";

    try {
        $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => $key,
            'Body'   => fopen($file['tmp_name'], 'rb'),
            'ACL'    => 'public-read',
            'ContentType' => $file['type'],
        ]);

        $product_service = Flight::get('product_service');
        $result = $product_service->add_product_image([
            'product_id' => $product_id,
            'image' => $url
        ]);

        MessageHandler::handleServiceResponse($result, 'Product image uploaded successfully.');
    } catch (Exception $e) {
        Flight::halt(500, 'Upload to cloud failed: ' . $e->getMessage());
    }
});


Flight::route('POST /product_images/@product_id', function($product_id) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);

    $product_service = Flight::get('product_service');
    $existingImageIds = json_decode(Flight::request()->data['existingImageIds'] ?? '[]');

    if (!is_array($existingImageIds)) {
        Flight::halt(400, 'Invalid image ID list.');
    }

    // 1. Briši slike koje nisu na listi
    $allImages = $product_service->get_images_by_product_id($product_id);
    foreach ($allImages as $img) {
        if (!in_array($img['id'], $existingImageIds)) {
            $product_service->delete_product_image($img['id']);
            // Ne možemo brisati fizičku sliku jer je hostovana na cloudu
        }
    }

    // 2. Upload novih slika ako postoje
    if (isset($_FILES['new_images'])) {
        $newImages = $_FILES['new_images'];
        $isSingle = !is_array($newImages['tmp_name']);
        $fileCount = $isSingle ? 1 : count($newImages['tmp_name']);

        // S3 config
        $bucket = 'cuppatea-uploads';
        $region = 'fra1';
        $endpoint = "https://fra1.digitaloceanspaces.com";

        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => $region,
            'endpoint' => $endpoint,
            'credentials' => [
                'key'    => 'DO801T4YF42P8Y7W3686',
                'secret' => 'Id3nl07Ji3+Q3XUP10twjq2uZEQICP47/6rE7thIn7A',
            ],
        ]);

        for ($i = 0; $i < $fileCount; $i++) {
            $tmpName = $isSingle ? $newImages['tmp_name'] : $newImages['tmp_name'][$i];
            $fileType = $isSingle ? $newImages['type'] : $newImages['type'][$i];
            $fileName = $isSingle ? $newImages['name'] : $newImages['name'][$i];

            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($fileType, $allowed)) continue;

            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $new_name = uniqid("product_", true) . '.' . $ext;
            $key = "uploads/{$new_name}";
            $url = "https://{$bucket}.{$region}.digitaloceanspaces.com/{$key}";

            try {
                $s3->putObject([
                    'Bucket' => $bucket,
                    'Key'    => $key,
                    'Body'   => fopen($tmpName, 'rb'),
                    'ACL'    => 'public-read',
                    'ContentType' => $fileType,
                ]);

                $product_service->add_product_image([
                    'product_id' => $product_id,
                    'image' => $url
                ]);
            } catch (Exception $e) {
                // Samo loguj, nastavi dalje s ostalim slikama
                error_log('Upload failed: ' . $e->getMessage());
            }
        }
    }

    Flight::json(["message" => "Product images updated successfully."]);
});



     
 
 });