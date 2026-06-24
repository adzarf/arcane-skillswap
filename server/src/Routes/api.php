<?php
declare(strict_types=1);

use Slim\App;
use App\Controllers\AuthController;
use App\Controllers\SkillController;
use App\Controllers\UserSkillController;
use App\Controllers\TutorDiscoveryController;
use App\Controllers\AvailabilitySlotController;
use App\Controllers\BookingController;
use App\Controllers\WalletController;
use App\Controllers\ReviewController;
use App\Controllers\MessageController;
use App\Controllers\NotificationController;
use App\Controllers\UserController;
use App\Middleware\CorsMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    $container = $app->getContainer();

    // Global Middlewares
    $app->add(new CorsMiddleware());
    $app->add(new RateLimitMiddleware(200, 60));

    // ==================== AUTH ENDPOINTS ====================
    $app->post('/api/auth/register', AuthController::class . ':register');
    $app->post('/api/auth/login', AuthController::class . ':login');
    $app->post('/api/auth/refresh', AuthController::class . ':refresh');
    $app->post('/api/auth/logout', AuthController::class . ':logout');

    // ==================== USER ENDPOINTS ====================
    $app->get('/api/users/me', UserController::class . ':getCurrentUser')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/users/{id}', UserController::class . ':getProfile');
    $app->patch('/api/users/me', UserController::class . ':update')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->post('/api/users/change-password', UserController::class . ':changePassword')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));

    // ==================== SKILL ENDPOINTS ====================
    $app->post('/api/skills', SkillController::class . ':create')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/skills', SkillController::class . ':list');
    $app->get('/api/skills/search', SkillController::class . ':search');
    $app->get('/api/skills/filter', SkillController::class . ':filterByCategory');
    $app->get('/api/skills/{id}', SkillController::class . ':get');
    $app->patch('/api/skills/{id}', SkillController::class . ':update')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->delete('/api/skills/{id}', SkillController::class . ':delete')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));

    // ==================== USER SKILLS ENDPOINTS ====================
    $app->post('/api/user-skills', UserSkillController::class . ':create')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/user-skills/{id}', UserSkillController::class . ':get');
    $app->get('/api/users/{user_id}/skills', UserSkillController::class . ':getByUser');
    $app->patch('/api/user-skills/{id}', UserSkillController::class . ':update')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->delete('/api/user-skills/{id}', UserSkillController::class . ':delete')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));

    // ==================== TUTOR DISCOVERY ENDPOINTS ====================
    $app->get('/api/tutors/search', TutorDiscoveryController::class . ':search');

    // ==================== AVAILABILITY SLOTS ENDPOINTS ====================
    $app->post('/api/availability-slots', AvailabilitySlotController::class . ':create')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/availability-slots/{id}', AvailabilitySlotController::class . ':get');
    $app->get('/api/users/{user_id}/availability-slots', AvailabilitySlotController::class . ':getByUser');
    $app->patch('/api/availability-slots/{id}', AvailabilitySlotController::class . ':update')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->delete('/api/availability-slots/{id}', AvailabilitySlotController::class . ':delete')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));

    // ==================== BOOKING ENDPOINTS ====================
    $app->post('/api/bookings', BookingController::class . ':create')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/bookings/{id}', BookingController::class . ':get');
    $app->get('/api/bookings/learner', BookingController::class . ':getLearnerBookings')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/bookings/tutor', BookingController::class . ':getTutorBookings')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->patch('/api/bookings/{id}/accept', BookingController::class . ':accept')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->patch('/api/bookings/{id}/decline', BookingController::class . ':decline')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->patch('/api/bookings/{id}/confirm', BookingController::class . ':confirm')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->patch('/api/bookings/{id}/complete', BookingController::class . ':complete')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->patch('/api/bookings/{id}/cancel', BookingController::class . ':cancel')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));

    // ==================== WALLET ENDPOINTS ====================
    $app->get('/api/wallet', WalletController::class . ':getBalance')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/wallet/transactions', WalletController::class . ':getTransactions')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));

    // ==================== REVIEW ENDPOINTS ====================
    $app->post('/api/reviews', ReviewController::class . ':create')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/reviews/{id}', ReviewController::class . ':get');
    $app->get('/api/tutors/{tutor_id}/reviews', ReviewController::class . ':getTutorReviews');

    // ==================== MESSAGE ENDPOINTS ====================
    $app->post('/api/messages', MessageController::class . ':send')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/messages/{id}', MessageController::class . ':get');
    $app->get('/api/conversations/{other_user_id}', MessageController::class . ':getConversation')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/messages/unread-count', MessageController::class . ':getUnreadCount')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->patch('/api/messages/{id}/read', MessageController::class . ':markAsRead');
    $app->patch('/api/conversations/{sender_id}/read', MessageController::class . ':markConversationAsRead')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));

    // ==================== NOTIFICATION ENDPOINTS ====================
    $app->get('/api/notifications', NotificationController::class . ':list')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->get('/api/notifications/{id}', NotificationController::class . ':get');
    $app->get('/api/notifications/unread-count', NotificationController::class . ':getUnreadCount')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
    $app->patch('/api/notifications/{id}/read', NotificationController::class . ':markAsRead');
    $app->patch('/api/notifications/read-all', NotificationController::class . ':markAllAsRead')->add(new AuthMiddleware($container->get(\App\Helpers\JwtHelper::class)));
};
