<?php
 require_once __DIR__ . '/../services/CategoryService.php';
 require_once __DIR__ . '/../../utils/MessageHandler.php';
 
 use Firebase\JWT\JWT;
 use Firebase\JWT\Key;
 
 Flight::set('category_service', new CategoryService());
 
 Flight::group('/categories', function() {
 

        /**
     * @OA\Get(
     *     path="/categories",
     *     summary="Get all categories",
     *     tags={"Categories"},
     *     security={{"ApiKey": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Living Room")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */

     Flight::route('GET /', function() {
         Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    
         $category_service = Flight::get('category_service');
         $result = $category_service->getCategories();
         MessageHandler::handleServiceResponse($result);
     });
    
         /**
     * @OA\Get(
     *     path="/categories/category/{name}",
     *     summary="Get a category by name",
     *     tags={"Categories"},
     *     security={{"ApiKey": {}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="Name of the category to fetch",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category found",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Living Room")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */

Flight::route('GET /category', function() {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    
    $name = Flight::request()->query['name'];
    if (!$name) {
        Flight::halt(400, 'Category name is required.');
    }

    $category = Flight::get('category_service')->get_category_by_name($name);
    MessageHandler::handleServiceResponse($category);
});
});