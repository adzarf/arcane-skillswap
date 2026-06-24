<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(User $user): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name, bio, profile_photo, faculty, year, is_active, created_at, updated_at) VALUES (:email, :password_hash, :first_name, :last_name, :bio, :profile_photo, :faculty, :year, :is_active, NOW(), NOW())');
        $stmt->execute([
            ':email' => $user->email,
            ':password_hash' => $user->password_hash,
            ':first_name' => $user->first_name,
            ':last_name' => $user->last_name,
            ':bio' => $user->bio,
            ':profile_photo' => $user->profile_photo,
            ':faculty' => $user->faculty,
            ':year' => $user->year,
            ':is_active' => $user->is_active ? 1 : 0,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (! $row) {
            return null;
        }

        return new User($row);
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (! $row) {
            return null;
        }

        return new User($row);
    }

    public function update(int $id, User $user): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, bio = :bio, profile_photo = :profile_photo, faculty = :faculty, year = :year, password_hash = :password_hash, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':first_name' => $user->first_name,
            ':last_name' => $user->last_name,
            ':bio' => $user->bio,
            ':profile_photo' => $user->profile_photo,
            ':faculty' => $user->faculty,
            ':year' => $user->year,
            ':password_hash' => $user->password_hash,
        ]);
    }
}
