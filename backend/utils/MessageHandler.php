<?php
class MessageHandler {
    public static function handleServiceResponse($result, $successMessage = null) {
        if ($result === "Server error") {
            Flight::halt(500, json_encode(['error' => 'Internal server error.']));
        } elseif ($result === "Invalid input") {
            Flight::halt(400, json_encode(['error' => 'Invalid input data.']));
        } else {
            if ($successMessage === null) {
                Flight::json($result);
            } else {
                Flight::json(['message' => $successMessage]);
            }
        }
    }
}
    
    