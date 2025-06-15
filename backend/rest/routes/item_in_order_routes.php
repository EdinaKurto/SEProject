<?php

require_once __DIR__ . '/../services/ItemInOrderService.php';
require_once __DIR__ . '/../../utils/MessageHandler.php';

Flight::set('item_in_order_service', new ItemInOrderService());

Flight::group('/item_in_order', function () {

    /**
     * @OA\Post(
     *     path="/item_in_order",
     *     summary="Add an item to an order.",
     *     tags={"Item in Order"},
     *     security={{"ApiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id", "product_id", "quantity"},
     *             @OA\Property(property="order_id", type="integer", example=1, description="ID of the order"),
     *             @OA\Property(property="product_id", type="integer", example=2, description="ID of the product"),
     *             @OA\Property(property="quantity", type="integer", example=6, description="Quantity of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item successfully added to the order",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item added to order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input data.")
     *         )
     *     )
     * )
     */
    Flight::route('POST /', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $data = Flight::request()->data->getData();
        $item_in_order = Flight::get('item_in_order_service')->add_item_in_order($data["order_id"], $data["product_id"], $data["quantity"]);
        MessageHandler::handleServiceResponse($item_in_order, "Item added to order");
    });

});