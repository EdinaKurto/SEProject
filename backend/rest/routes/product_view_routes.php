<?php
 require_once __DIR__ . '/../services/ProductViewService.php';
 require_once __DIR__ . '/../../utils/MessageHandler.php';
 
 use Firebase\JWT\JWT;
 use Firebase\JWT\Key;
 
 Flight::set('product_view_service', new ProductViewService());
 
 Flight::group('/product_views', function() {
 
    /**
     * @OA\Post(
     *     path="/product_views/add",
     *     summary="Add or update a product view.",
     *     description="Adds a new product view or updates the timestamp if the product view already exists for the customer.",
     *     tags={"Product Views"},
     *     security={{"ApiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_id", "product_id"},
     *             @OA\Property(property="customer_id", type="integer", example=3, description="ID of the customer"),
     *             @OA\Property(property="product_id", type="integer", example=2, description="ID of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product view successfully added or updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product view added/updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input data.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Internal server error.")
     *         )
     *     )
     * )
     */
     Flight::route('POST /add', function() {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
         $data = Flight::request()->data->getData();
         $customer_id = $data['customer_id'];
         $product_id = $data['product_id'];
         $time = date("Y-m-d H:i:s");
     
         $productViewService = Flight::get('product_view_service');
         $result = $productViewService->addOrUpdateProductView($customer_id, $product_id, $time);
     
         MessageHandler::handleServiceResponse($result);

     });
     
    /**
     * @OA\Get(
     *     path="/product_views",
     *     summary="Get product views for the authenticated user.",
     *     description="Fetches a list of products viewed by the authenticated user, including customer and product details.",
     *     tags={"Product Views"},
     *     security={{"ApiKey": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched product views",
     *         *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(
     *             type="object",
     *             @OA\Property(property="customer_id", type="integer", example=3, description="ID of the customer"),
     *             @OA\Property(property="customer_name", type="string", example="Edina", description="Name of the customer"),
     *             @OA\Property(property="product_id", type="integer", example=2, description="ID of the product"),
     *             @OA\Property(property="product_name", type="string", example="Cherry Blossom", description="Name of the product"),
     *             @OA\Property(property="time", type="string", format="date-time", example="2025-05-04 15:09:10", description="Timestamp of the product view")
     *         )
     *     )

     *     ),
     *    @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Internal server error.")
     *         )
     *     )
     * )
     */
     Flight::route('GET /', function() {
         Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
         $user_id = Flight::get('user')->id;
    
         $productViewService = Flight::get('product_view_service');
         $result = $productViewService->getUserProductViews($user_id);
         MessageHandler::handleServiceResponse($result);
     });
});