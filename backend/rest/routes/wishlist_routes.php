<?php
require_once __DIR__ . '/../services/WishlistService.php';
require_once __DIR__ . '/../../utils/MessageHandler.php';

Flight::set('wishlist_service', new WishlistService());

Flight::group('/wishlist', function () {

    /**
     * @OA\Get(
     *     path="/wishlist",
     *     summary="Get filtered wishlist items based on search and sort criteria",
     *     description="Fetches a list of wishlist items that match the search criteria and are sorted by the given column and order.",
     *     tags={"Wishlist"},
     *     security={{"ApiKey": {}}},
     *     parameters={
     *         @OA\Parameter(
     *             name="search",
     *             in="query",
     *             description="Search term to filter wishlist items",
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
     *             description="Successfully fetched filtered wishlist items",
     *             @OA\JsonContent(
     *         type="array",
     *         @OA\Items(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="product_id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="Cherry Blossom"),
     *                 @OA\Property(property="category_id", type="string", example="2"),
     *                 @OA\Property(property="price", type="string", example="24.99"),
     *                 @OA\Property(property="description", type="string", example="Updated description for the product."),
     *                 @OA\Property(property="cart_quantity", type="string", example="6")
     *             }
     *         )
     *     )

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

        $wishlist = Flight::get('wishlist_service')->get_filtered_wishlist($user_id, $search, $sort_by, $sort_order);
        
        MessageHandler::handleServiceResponse($wishlist);
    });

    /**
     * @OA\Get(
     *     path="/wishlist/summary",
     *     summary="Get wishlist summary",
     *     description="Fetches the wishlist summary for a specific user, including total value and total count.",
     *     tags={"Wishlist"},
     *     security={{"ApiKey": {}}},
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Successfully fetched wishlist summary",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(
     *                     property="total_value",
     *                     type="integer",
     *                     example=149.93999862670898,
     *                     description="Total value of the items in the wishlist"
     *                 ),
     *                 @OA\Property(
     *                     property="total_count",
     *                     type="string",
     *                     example="6",
     *                     description="Total count of the items in the wishlist"
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
        $summary = Flight::get('wishlist_service')->get_wishlist_summary_by_user($user_id);
    
        MessageHandler::handleServiceResponse($summary);
    });

    /**
     * @OA\Post(
     *     path="/wishlist/add",
     *     summary="Add an item to the wishlist",
     *     tags={"Wishlist"},
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
     *         description="Item added to wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Item added to wishlist"
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

        $result = Flight::get('wishlist_service')->add_to_wishlist(
            $user_id,
            intval($data['product_id']),
            intval($quantity)
        );

        MessageHandler::handleServiceResponse($result, 'Item added to wishlist');
    });



    /**
     * @OA\Delete(
     *     path="/wishlist/remove/{product_id}",
     *     tags={"Wishlist"},
     *     summary="Remove an item from the wishlist",
     *     description="Removes a product from the user's wishlist by product ID.",
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
     *         description="Item successfully removed from wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item removed from wishlist")
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
        $result = Flight::get('wishlist_service')->remove_from_wishlist($user_id, $product_id);
        MessageHandler::handleServiceResponse($result, 'Item removed from wishlist');
    });

    /**
     * @OA\Put(
     *     path="/wishlist/update",
     *     tags={"Wishlist"},
     *     summary="Update product quantity in the wishlist",
     *     description="Updates the quantity of a product in the user's wishlist. User ID is retrieved from token/session.",
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
     *         description="Wishlist updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Wishlist updated")
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
        if (!Flight::get('product_service')->product_exists($product_id)) {
            Flight::halt(400, "Product with ID $product_id does not exist.");
        }

        $result = Flight::get('wishlist_service')->update_quantity(
            $user_id,
            intval($data['product_id']),
            intval($data['quantity'])
        );

        MessageHandler::handleServiceResponse($result, 'Wishlist updated');
    });


    /**
     * @OA\Delete(
     *     path="/wishlist/clear",
     *     tags={"Wishlist"},
     *     summary="Remove all items from the wishlist",
     *     description="Deletes all products in the user's wishlist. User ID is retrieved from token/session.",
     *     security={{"ApiKey": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Item successfully removed from wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Items cleared from wishlist")
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
        $result = Flight::get('wishlist_service')->clear_wishlist($user_id);
        MessageHandler::handleServiceResponse($result, 'Items cleared from wishlist');
    });

});