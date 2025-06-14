<?php
/* header("Access-Control-Allow-Origin: https://cuppatea-frontend-gjxs4.ondigitalocean.app");
header("Access-Control-Allow-Methods: GET,PUT,POST,DELETE,PATCH,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Authentication");
header("Access-Control-Allow-Credentials", "true"); */

require_once __DIR__ . '/../services/CartService.php';
require_once __DIR__ . '/../../utils/MessageHandler.php';

Flight::set('cart_service', new CartService());

Flight::group('/cart', function () {
    /**
     * @OA\Get(
     *     path="/cart",
     *     summary="Get filtered cart items based on search and sort criteria",
     *     description="Fetches a list of cart items that match the search criteria and are sorted by the given column and order.",
     *     tags={"Cart"},
     *     security={{"ApiKey": {}}},
     *     parameters={
     *         @OA\Parameter(
     *             name="search",
     *             in="query",
     *             description="Search term to filter cart items",
     *             required=false,
     *             @OA\Schema(type="string")
     *         ),
     *         @OA\Parameter(
     *             name="sort_by",
     *             in="query",
     *             description="Column to sort by (allowed values: 'name', 'price_each')",
     *             required=false,
     *             @OA\Schema(
     *                 type="string",
     *                 enum={"name", "price_each"},
     *                 default="name"
     *             )
     *         ),
     *         @OA\Parameter(
     *             name="sort_order",
     *             in="query",
     *             description="Order to sort by (allowed values: 'asc', 'desc')",
     *             required=false,
     *             @OA\Schema(
     *                 type="string",
     *                 enum={"asc", "desc"},
     *                 default="asc"
     *             )
     *         )
     *     },
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Successfully fetched filtered cart items",
     *             @OA\JsonContent(
     *                 type="array",
     *                 items=@OA\Items(
     *                     type="object",
     *                     properties={
     *                         @OA\Property(property="product_id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Green Tea"),
     *                         @OA\Property(property="category_id", type="integer", example=2),
     *                         @OA\Property(property="price", type="float", example="Description"),
     *                         @OA\Property(property="description", type="string", example = "Description"),
     *                         @OA\Property(property="cart_quantity", type="integer", example = 3)
     *                     }
     *                 )
     *             )
     *         ),
     *         @OA\Response(
     *             response=400,
     *             description="Invalid input data",
     *             @OA\JsonContent(
     *                 type="object",
     *                 properties={
     *                     @OA\Property(property="error", type="string", example="Invalid input data.")
     *                 }
     *             )
     *         ),
     *         @OA\Response(
     *             response=500,
     *             description="Internal server error",
     *             @OA\JsonContent(
     *                 type="object",
     *                 properties={
     *                     @OA\Property(property="error", type="string", example="Internal server error.")
     *                 }
     *             )
     *         )
     *     }
     * )
     */
    Flight::route('GET /', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $user_id = Flight::get('user')->id; 
        $queryParams = Flight::request()->query;

        $search = isset($queryParams['search']) ? trim($queryParams['search']) : "";
        $sort_by = isset($queryParams['sort_by']) ? strtolower($queryParams['sort_by']) : "name";
        $sort_order = isset($queryParams['sort_order']) ? strtolower($queryParams['sort_order']) : "asc";

        $cart = Flight::get('cart_service')->get_filtered_cart($user_id, $search, $sort_by, $sort_order);
        MessageHandler::handleServiceResponse($cart);
    });

    /**
     * @OA\Get(
     *     path="/cart/summary",
     *     summary="Get cart summary",
     *     description="Fetches the cart summary for a specific user, including total value and total count.",
     *     tags={"Cart"},
     *     security={{"ApiKey": {}}},
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Successfully fetched cart summary",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(
     *                     property="total_value",
     *                     type="integer",
     *                     example = 66.30000114440918,
     *                     description="Total value of the items in the cart"
     *                 ),
     *                 @OA\Property(
     *                     property="total_count",
     *                     type="string",
     *                     example = "3",
     *                     description="Total count of the items in the cart"
     *                 )
     *             )
     *         ),
     *         @OA\Response(
     *             response=500,
     *             description="Internal server error"
     *         )
     *     }
     * )
     */
    Flight::route('GET /summary', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $user_id = Flight::get('user')->id;
        $summary = Flight::get('cart_service')->get_cart_summary_by_user($user_id);
        MessageHandler::handleServiceResponse($summary);
    });
    

    /**
     * @OA\Post(
     *     path="/cart/add",
     *     summary="Add an item to the cart",
     *     tags={"Cart"},
     *     security={{"ApiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(
     *                 property="product_id",
     *                 type="integer",
     *                 example=1
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item added to cart",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Item added to cart"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Invalid input data."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Internal server error."
     *             )
     *         )
     *     )
     * )
     */
Flight::route('POST /add', function () {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $user_id = Flight::get('user')->id;
    $data = Flight::request()->data->getData();

    if (!isset($data['product_id'])) {
        Flight::halt(400, "'product_id' is required.");
    }

    if (!is_numeric($data['product_id']) || intval($data['product_id']) <= 0) {
        Flight::halt(400, "'product_id' must be a positive number.");
    }

    $quantity = $data['quantity'] ?? 1;

    if (!is_numeric($quantity) || intval($quantity) <= 0) {
        Flight::halt(400, "'quantity' must be a positive number.");
    }

    // --- Sve validno, dodaj u korpu ---
    $result = Flight::get('cart_service')->add_to_cart(
        $user_id,
        intval($data['product_id']),
        intval($quantity)
    );

    MessageHandler::handleServiceResponse($result, 'Item added to cart');
});



    /**
     * @OA\Delete(
     *     path="/cart/remove/{product_id}",
     *     tags={"Cart"},
     *     summary="Remove an item from the cart",
     *     description="Removes a product from the user's cart by product ID.",
     *     security={{"ApiKey": {}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to remove",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item successfully removed from cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item removed from cart")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    Flight::route('DELETE /remove/@product_id', function ($product_id) {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $user_id = Flight::get('user')->id;
        $result = Flight::get('cart_service')->remove_from_cart($user_id, $product_id);
        MessageHandler::handleServiceResponse($result, 'Item removed from cart');
    });

    /**
     * @OA\Put(
     *     path="/cart/update",
     *     tags={"Cart"},
     *     summary="Update product quantity in the cart",
     *     description="Updates the quantity of a product in the user's cart. User ID is retrieved from token/session.",
     *     security={{"ApiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=76)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input data.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Internal server error.")
     *         )
     *     )
     * )
     */
    Flight::route('PUT /update', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

        $user_id = Flight::get('user')->id;
        $data = Flight::request()->data->getData();

        if (!isset($data['product_id'])) {
            Flight::halt(400, "'product_id' is required.");
        }

        if (!is_numeric($data['product_id']) || intval($data['product_id']) <= 0) {
            Flight::halt(400, "'product_id' must be a positive number.");
        }

        if (!isset($data['quantity'])) {
            Flight::halt(400, "'quantity' is required.");
        }

        if (!is_numeric($data['quantity']) || intval($data['quantity']) <= 0) {
            Flight::halt(400, "'quantity' must be a positive number.");
        }

        $product_id = intval($data['product_id']);
        $quantity = intval($data['quantity']);

        if (!Flight::get('product_service')->product_exists($product_id)) {
            Flight::halt(400, "Product with ID $product_id does not exist.");
        }

        $result = Flight::get('cart_service')->update_quantity($user_id, $product_id, $quantity);

        MessageHandler::handleServiceResponse($result, 'Cart updated');
    });



    /**
     * @OA\Delete(
     *     path="/cart/clear",
     *     tags={"Cart"},
     *     summary="Remove all items from the cart",
     *     description="Deletes all products in the user's cart. User ID is retrieved from token/session.",
     *     security={{"ApiKey": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Items successfully cleared from cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart cleared")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    Flight::route('DELETE /clear', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $user_id = Flight::get('user')->id;
        $result = Flight::get('cart_service')->clear_cart($user_id);
        MessageHandler::handleServiceResponse($result, 'Cart cleared');
    });

});