<?php
/* header("Access-Control-Allow-Origin: https://cuppatea-frontend-gjxs4.ondigitalocean.app");
header("Access-Control-Allow-Methods: GET,PUT,POST,DELETE,PATCH,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Authentication");
header("Access-Control-Allow-Credentials", "true"); */

require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../../utils/MessageHandler.php';


Flight::set('order_service', new OrderService());

Flight::group('/order', function () {

    /**
     * @OA\Get(
     *     path="/order/all",
     *     summary="Get all orders for the authenticated user.",
     *     description="Fetches a list of all orders placed by the authenticated user, including order details.",
     *     tags={"Order"},
     *     security={{"ApiKey": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched all orders",
     *         @OA\JsonContent(
     *             type="array",
     *             *     @OA\Items(
     *         type="object",
     *         @OA\Property(property="order_id", type="integer", example=2, description="ID of the order"),
     *         @OA\Property(property="order_date", type="string", example="2025-04-06 18:04:50", description="Date and time when the order was placed"),
     *         @OA\Property(property="product_names", type="string", example="Green Tea,Cherry Blossom", description="Comma-separated names of the products in the order"),
     *         @OA\Property(property="quantities", type="string", example="6,6", description="Comma-separated quantities of the products in the order"),
     *         @OA\Property(property="total_price", type="number", format="float", example=282.54000091552734, description="Total price of the order"),
     *         @OA\Property(property="status_name", type="string", example="Delivered", description="Status of the order")
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
    Flight::route('GET /all', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $user_id = Flight::get('user')->id; 
    
        $order_details = Flight::get('order_service')->get_orders_by_user($user_id);
        
        MessageHandler::handleServiceResponse($order_details);
    });

    /**
     * @OA\Get(
     *     path="/order/count_pending",
     *     summary="Count all pending orders for the authenticated user.",
     *     description="Returns the total number of pending orders for the authenticated user.",
     *     tags={"Order"},
     *     security={{"ApiKey": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully counted pending orders",
     *         @OA\JsonContent(
     *             type="string",
     *             example="3",
     *             description="The total number of pending orders"
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
    Flight::route('GET /count_pending', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $user_id = Flight::get('user')->id; 
        $summary = Flight::get('order_service')->count_pending_orders($user_id);
        MessageHandler::handleServiceResponse($summary);
    });

    /**
     * @OA\Get(
     *     path="/order/count_delivered",
     *     summary="Count all delivered orders for the authenticated user.",
     *     description="Returns the total number of delivered orders for the authenticated user.",
     *     tags={"Order"},
     *     security={{"ApiKey": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully counted delivered orders",
     *         @OA\JsonContent(
     *             type="string",
     *             example="5",
     *             description="The total number of delivered orders"
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
    Flight::route('GET /count_delivered', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $user_id = Flight::get('user')->id; 
        $summary = Flight::get('order_service')->count_delivered_orders($user_id);
        MessageHandler::handleServiceResponse($summary);
    });

    /**
     * @OA\Get(
     *     path="/order/count_all",
     *     summary="Count all orders for the authenticated user.",
     *     description="Returns the total number of all orders for the authenticated user.",
     *     tags={"Order"},
     *     security={{"ApiKey": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully counted all orders",
     *         @OA\JsonContent(
     *             type="string",
     *             example="10",
     *             description="The total number of all orders"
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
    Flight::route('GET /count_all', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $user_id = Flight::get('user')->id; 
        $summary = Flight::get('order_service')->count_total_orders($user_id);
        MessageHandler::handleServiceResponse($summary);
    });
    
    /**
     * @OA\Post(
     *     path="/order/add",
     *     summary="Add a new order.",
     *     description="Creates a new order for the authenticated user.",
     *     tags={"Order"},
     *     security={{"ApiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "surname", "address", "city", "country", "phone_number"},
     *             @OA\Property(property="name", type="string", example="Anja", description="Customer's first name"),
     *             @OA\Property(property="surname", type="string", example="Sehovac", description="Customer's last name"),
     *             @OA\Property(property="address", type="string", example="Test revolucije bb", description="Customer's address"),
     *             @OA\Property(property="city", type="string", example="Sarajevo", description="Customer's city"),
     *             @OA\Property(property="country", type="string", example="Bosnia and Herzegovina", description="Customer's country"),
     *             @OA\Property(property="phone_number", type="string", example="+387 63 333 333", description="Customer's phone number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Purchase made successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input data")
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
    Flight::route('POST /add', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        
        $user_id = Flight::get('user')->id;
        $data = Flight::request()->data->getData();


        $required_fields = ['name', 'surname', 'address', 'city', 'country', 'phone_number'];

        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                Flight::halt(400, "Field '$field' is required.");
            }

            if (trim($data[$field]) === '') {
                Flight::halt(400, "Field '$field' cannot be empty.");
            }
        }
        $cart_items = Flight::get('cart_service')->get_cart_by_user($user_id);
        if (empty($cart_items)) {
            Flight::halt(400, "Your cart is empty. Please add products before placing an order.");
        }

        if (!preg_match('/^\+[0-9]+$/', $data['phone_number'])) {
        Flight::halt(400, "Phone number must start with '+' and contain only digits after it.");
    }



        $result = Flight::get('order_service')->add_order($user_id, $data);
        MessageHandler::handleServiceResponse($result, 'Purchase made successfully!');
    });

    /**
     * @OA\Delete(
     *     path="/order/remove/{order_id}",
     *     summary="Delete an order by ID.",
     *     description="Deletes an order for the authenticated user based on the provided order ID.",
     *     tags={"Order"},
     *     security={{"ApiKey": {}}},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="ID of the order to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order successfully deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order removed.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid order ID")
     *         )
     *     ),
     * )
     */
    Flight::route('DELETE /remove/@order_id', function ($order_id) {
        Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
        $user_id = Flight::get('user')->id;
        $result = Flight::get('order_service')->delete_order($order_id);
        MessageHandler::handleServiceResponse($result, 'Order removed.');
    });

    /**
     * @OA\Put(
     *     path="/order/update",
     *     summary="Update the status of an order.",
     *     description="Updates the status of an existing order for the authenticated user.",
     *     tags={"Order"},
     *     security={{"ApiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id", "new_status_id"},
     *             @OA\Property(property="order_id", type="integer", example=1, description="ID of the order to update"),
     *             @OA\Property(property="new_status_id", type="integer", example=2, description="New status ID for the order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order successfully updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input data")
     *         )
     *     ),
     * )
     */
    Flight::route('PUT /update', function () {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);

    $user_id = Flight::get('user')->id;
    $data = Flight::request()->data->getData();

    if (!isset($data["order_id"]) || !isset($data["new_status_id"])) {
        Flight::halt(400, "Both 'order_id' and 'new_status_id' are required.");
    }

    if (!is_numeric($data["order_id"]) || intval($data["order_id"]) <= 0) {
        Flight::halt(400, "'order_id' must be a valid positive number.");
    }

    if (!is_numeric($data["new_status_id"]) || intval($data["new_status_id"]) <= 0) {
        Flight::halt(400, "'new_status_id' must be a valid positive number.");
    }

    $status = Flight::get('order_service')->get_status_by_id($data["new_status_id"]);
    if (!$status) {
        Flight::halt(404, "Status with ID {$data['new_status_id']} does not exist.");
}


    $result = Flight::get('order_service')->update_order_status(
        intval($data["order_id"]),
        intval($data["new_status_id"])
    );

    MessageHandler::handleServiceResponse($result, 'Order updated');
});


    /**
 * @OA\Get(
 *     path="/order/all_orders",
 *     summary="Get all orders for all users (admin only).",
 *     description="Fetches all orders from all users. Admin only.",
 *     tags={"Order"},
 *     security={{"ApiKey": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of all orders for all users",
 *         @OA\JsonContent(type="array", @OA\Items(
 *             @OA\Property(property="order_id", type="integer", example=1),
 *             @OA\Property(property="user_name", type="string", example="Ilma"),
 *             @OA\Property(property="user_email", type="string", example="ilma@gmail.com"),
 *             @OA\Property(property="order_date", type="string", example="2025-05-30 15:00:00"),
 *             @OA\Property(property="product_names", type="string", example="Candle,Soap"),
 *             @OA\Property(property="quantities", type="string", example="2,3"),
 *             @OA\Property(property="total_price", type="number", example=78.50),
 *             @OA\Property(property="status_name", type="string", example="Pending")
 *         ))
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Access denied"
 *     )
 * )
 */
Flight::route('GET /all_orders', function () {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
    $orders = Flight::get('order_service')->get_all_orders();
    MessageHandler::handleServiceResponse($orders);
});

Flight::route('GET /statuses', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
        $statuses = Flight::get('order_service')->get_order_statuses();
        MessageHandler::handleServiceResponse($statuses);
    });


});