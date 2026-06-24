<?php

declare(strict_types=1);

use App\Config\Database;
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
use App\Helpers\JwtHelper;
use App\Repositories\UserRepository;
use App\Repositories\SkillRepository;
use App\Repositories\UserSkillRepository;
use App\Repositories\TutorRepository;
use App\Repositories\AvailabilitySlotRepository;
use App\Repositories\BookingRepository;
use App\Repositories\WalletRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\MessageRepository;
use App\Repositories\NotificationRepository;
use App\Services\AuthService;
use App\Services\SkillService;
use App\Services\UserSkillService;
use App\Services\TutorDiscoveryService;
use App\Services\AvailabilitySlotService;
use App\Services\BookingService;
use App\Services\WalletService;
use App\Services\ReviewService;
use App\Services\MessageService;
use App\Services\NotificationService;
use App\Services\UserService;
use DI\ContainerBuilder;
use PDO;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => (getenv('APP_ENV') === 'development'),
        ],

        // Database
        PDO::class => function () {
            $db = new Database([
                'host' => getenv('DB_HOST') ?: 'localhost',
                'port' => (int)(getenv('DB_PORT') ?: '3306'),
                'database' => getenv('DB_DATABASE') ?: 'skillswap',
                'username' => getenv('DB_USERNAME') ?: 'root',
                'password' => getenv('DB_PASSWORD') ?: '',
            ]);
            return $db->getPdo();
        },

        // JWT Helper
        JwtHelper::class => function () {
            return new JwtHelper([
                'secret' => getenv('JWT_SECRET') ?: 'dev-secret-key',
                'issuer' => getenv('JWT_ISSUER') ?: 'skillswap.local',
                'audience' => getenv('JWT_AUDIENCE') ?: 'skillswap.local',
            ]);
        },

        // Repositories
        UserRepository::class => function (ContainerInterface $c) {
            return new UserRepository($c->get(PDO::class));
        },
        SkillRepository::class => function (ContainerInterface $c) {
            return new SkillRepository($c->get(PDO::class));
        },
        UserSkillRepository::class => function (ContainerInterface $c) {
            return new UserSkillRepository($c->get(PDO::class));
        },
        TutorRepository::class => function (ContainerInterface $c) {
            return new TutorRepository($c->get(PDO::class));
        },
        AvailabilitySlotRepository::class => function (ContainerInterface $c) {
            return new AvailabilitySlotRepository($c->get(PDO::class));
        },
        BookingRepository::class => function (ContainerInterface $c) {
            return new BookingRepository($c->get(PDO::class));
        },
        WalletRepository::class => function (ContainerInterface $c) {
            return new WalletRepository($c->get(PDO::class));
        },
        ReviewRepository::class => function (ContainerInterface $c) {
            return new ReviewRepository($c->get(PDO::class));
        },
        MessageRepository::class => function (ContainerInterface $c) {
            return new MessageRepository($c->get(PDO::class));
        },
        NotificationRepository::class => function (ContainerInterface $c) {
            return new NotificationRepository($c->get(PDO::class));
        },

        // Services
        AuthService::class => function (ContainerInterface $c) {
            return new AuthService(
                $c->get(UserRepository::class),
                $c->get(JwtHelper::class),
                $c->get(PDO::class),
                (int)(getenv('JWT_ACCESS_TTL') ?: '900'),
                (int)(getenv('JWT_REFRESH_TTL') ?: '604800'),
            );
        },
        SkillService::class => function (ContainerInterface $c) {
            return new SkillService($c->get(SkillRepository::class));
        },
        UserSkillService::class => function (ContainerInterface $c) {
            return new UserSkillService($c->get(UserSkillRepository::class));
        },
        TutorDiscoveryService::class => function (ContainerInterface $c) {
            return new TutorDiscoveryService($c->get(TutorRepository::class));
        },
        AvailabilitySlotService::class => function (ContainerInterface $c) {
            return new AvailabilitySlotService($c->get(AvailabilitySlotRepository::class));
        },
        BookingService::class => function (ContainerInterface $c) {
            return new BookingService(
                $c->get(BookingRepository::class),
                $c->get(UserSkillRepository::class),
            );
        },
        WalletService::class => function (ContainerInterface $c) {
            $commission = (float)(getenv('PLATFORM_COMMISSION') ?: '0.10');
            return new WalletService($c->get(WalletRepository::class), $commission);
        },
        ReviewService::class => function (ContainerInterface $c) {
            return new ReviewService(
                $c->get(ReviewRepository::class),
                $c->get(BookingRepository::class),
            );
        },
        MessageService::class => function (ContainerInterface $c) {
            return new MessageService($c->get(MessageRepository::class));
        },
        NotificationService::class => function (ContainerInterface $c) {
            return new NotificationService($c->get(NotificationRepository::class));
        },
        UserService::class => function (ContainerInterface $c) {
            return new UserService(
                $c->get(UserRepository::class),
                $c->get(ReviewRepository::class),
            );
        },

        // Controllers
        AuthController::class => function (ContainerInterface $c) {
            return new AuthController($c->get(AuthService::class));
        },
        SkillController::class => function (ContainerInterface $c) {
            return new SkillController($c->get(SkillService::class));
        },
        UserSkillController::class => function (ContainerInterface $c) {
            return new UserSkillController($c->get(UserSkillService::class));
        },
        TutorDiscoveryController::class => function (ContainerInterface $c) {
            return new TutorDiscoveryController($c->get(TutorDiscoveryService::class));
        },
        AvailabilitySlotController::class => function (ContainerInterface $c) {
            return new AvailabilitySlotController($c->get(AvailabilitySlotService::class));
        },
        BookingController::class => function (ContainerInterface $c) {
            return new BookingController($c->get(BookingService::class));
        },
        WalletController::class => function (ContainerInterface $c) {
            return new WalletController($c->get(WalletService::class));
        },
        ReviewController::class => function (ContainerInterface $c) {
            return new ReviewController($c->get(ReviewService::class));
        },
        MessageController::class => function (ContainerInterface $c) {
            return new MessageController($c->get(MessageService::class));
        },
        NotificationController::class => function (ContainerInterface $c) {
            return new NotificationController($c->get(NotificationService::class));
        },
        UserController::class => function (ContainerInterface $c) {
            return new UserController($c->get(UserService::class));
        },
    ]);
};
