<?php
/* header("Access-Control-Allow-Origin: https://cuppatea-frontend-gjxs4.ondigitalocean.app");
header("Access-Control-Allow-Methods: GET,PUT,POST,DELETE,PATCH,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Authentication");
header("Access-Control-Allow-Credentials", "true"); */

require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../../utils/MessageHandler.php';
require_once __DIR__ . '/../../data/roles.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Aws\S3\S3Client;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Flight::set('user_service', new UserService());

Flight::group('/users', function() {
    
    /**
     * @OA\Get(
     *     path="/users/current",
     *     summary="Get the currently authenticated user's details.",
     *     description="Fetches the details of the currently authenticated user based on the user ID retrieved from the session or token.",
     *     tags={"User"},
     *     security={{"ApiKey": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched user details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", example="3", description="User ID"),
     *             @OA\Property(property="name", type="string", example="Anja", description="User's full name"),
     *             @OA\Property(property="email", type="string", example="anja.sehovac@stu.ibu.edu.ba", description="User's email address"),
     *             @OA\Property(property="date_of_birth", type="string", example="2003-01-27", description="User's date of birth"),
     *             @OA\Property(property="username", type="string", example="anja", description="User's username"),
     *             @OA\Property(property="image", type="string", nullable=true, example=null, description="URL of the user's profile image"),
     *             @OA\Property(property="role_id", type="string", example="1", description="Role ID of the user"),
     *             @OA\Property(property="address", type="string", example="test revolucije bb", description="User's address")
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
    Flight::route('GET /current', function() {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $current_user_id = Flight::get('user')->id;
        error_log("Current User ID: " . $current_user_id);     
        $user = Flight::get('user_service')->get_user_by_id($current_user_id);
        unset($user['password']);
        MessageHandler::handleServiceResponse($user);
    });

    /**
     * @OA\Put(
     *     path="/users/update",
     *     summary="Update the currently authenticated user's details.",
     *     description="Updates the details of the currently authenticated user based on the provided data.",
     *     tags={"User"},
     *     security={{"ApiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Anja Sehovac", description="Updated name of the user"),
     *             @OA\Property(property="email", type="string", example="anja.sehovac@stu.ibu.edu.ba", description="Updated email of the user"),
     *             @OA\Property(property="date_of_birth", type="string", example="2003-06-12", description="Updated date of birth of the user"),
     *             @OA\Property(property="username", type="string", example="anja", description="Updated username of the user"),
     *             @OA\Property(property="image", type="string", nullable=true, example=null, description="Updated profile image URL of the user"),
     *             @OA\Property(property="address", type="string", example="test revolucije bb", description="Updated address of the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated user details",
     *         @OA\JsonContent(
     *             type="object",

     *     @OA\Property(property="name", type="string", example="Anja Sehovac", description="Name of the user"),
     *     @OA\Property(property="email", type="string", example="anja.sehovac@stu.ibu.edu.ba", description="Email address of the user"),
     *     @OA\Property(property="date_of_birth", type="string", format="date", example="2003-06-12", description="Date of birth of the user"),
     *     @OA\Property(property="username", type="string", example="anja", description="Username of the user"),
     *     @OA\Property(property="image", type="string", nullable=true, example=null, description="Profile image URL of the user"),
     *     @OA\Property(property="address", type="string", example="test revolucije bb", description="Address of the user")

     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input")
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
    Flight::route('PUT /update', function () {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

        $current_user_id = Flight::get('user')->id;
        $data = Flight::request()->data->getData();

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                Flight::halt(400, "Invalid email format.");
            }
        }

        $string_fields = ['name', 'username', 'address'];
        foreach ($string_fields as $field) {
            if (isset($data[$field]) && trim($data[$field]) === '') {
                Flight::halt(400, "Field '$field' cannot be empty.");
            }
        }

        if (isset($data['date_of_birth'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['date_of_birth']);
            if (!$date || $date->format('Y-m-d') !== $data['date_of_birth']) {
                Flight::halt(400, "Invalid date format for 'date_of_birth'. Expected YYYY-MM-DD.");
            }
        }

        $user = Flight::get('user_service')->update_user($current_user_id, $data);
        MessageHandler::handleServiceResponse($user);
    });


    /**
     * @OA\Delete(
     *     path="/users/delete/{user_id}",
     *     summary="Delete a user by ID.",
     *     description="Deletes a user from the system based on the provided user ID.",
     *     tags={"User"},
     *     security={{"ApiKey": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to delete",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted the user",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You have successfully deleted the user")
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
    Flight::route('DELETE /delete/@user_id', function ($user_id) {
        Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
        $user_service = new UserService();
        $result = $user_service->delete_user($user_id);
        MessageHandler::handleServiceResponse($result, "You have successfully deleted the user");
    });

/*     Flight::route('POST /upload_image', function () {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $user_id = Flight::get('user')->id;

    if (!isset($_FILES['profile_picture'])) {
        Flight::halt(400, 'No file uploaded.');
    }

    $file = $_FILES['profile_picture'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        Flight::halt(400, 'Only JPG, PNG, or WEBP images are allowed.');
    }

    $uploads_dir = __DIR__ . '/../../uploads/';
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_name = uniqid("profile_", true) . '.' . $ext;
    $target_path = $uploads_dir . $new_name;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        Flight::halt(500, 'Failed to move uploaded file.');
    }

    // Save relative image path to DB
    $relative_url = '/uploads/' . $new_name;
    Flight::get('user_service')->update_user($user_id, ['image' => $relative_url]);

    echo json_encode(['status' => 'success', 'image_url' => $relative_url]);
}); */

Flight::route('POST /upload_image', function () {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $user_id = Flight::get('user')->id;

    if (!isset($_FILES['profile_picture'])) {
        Flight::halt(400, 'No file uploaded.');
    }

    $file = $_FILES['profile_picture'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        Flight::halt(400, 'Only JPG, PNG, or WEBP images are allowed.');
    }

    $bucket = 'cuppatea-uploads';
$region = 'fra1';
$endpoint = "https://fra1.digitaloceanspaces.com"; // ✅ FIXED

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
$new_name = uniqid("profile_", true) . '.' . $ext;
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

    Flight::get('user_service')->update_user($user_id, ['image' => $url]);
    echo json_encode(['status' => 'success', 'image_url' => $url]);

} catch (Exception $e) {
    Flight::halt(500, 'Upload to cloud failed: ' . $e->getMessage());
}


});

});