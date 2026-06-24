<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use PDO;

class WalletRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getOrCreate(int $userId): Wallet
    {
        $stmt = $this->pdo->prepare('SELECT * FROM wallets WHERE user_id = :user_id LIMIT 1');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new Wallet($row);
        }

        $stmt = $this->pdo->prepare('INSERT INTO wallets (user_id, balance, currency, updated_at) VALUES (:user_id, 0.0, :currency, NOW())');
        $stmt->execute([':user_id' => $userId, ':currency' => 'USD']);
        $id = (int)$this->pdo->lastInsertId();

        return new Wallet([
            'id' => $id,
            'user_id' => $userId,
            'balance' => 0.0,
            'currency' => 'USD',
        ]);
    }

    public function credit(int $walletId, float $amount, string $description = ''): WalletTransaction
    {
        $stmt = $this->pdo->prepare('UPDATE wallets SET balance = balance + :amount WHERE id = :wallet_id');
        $stmt->execute([':wallet_id' => $walletId, ':amount' => $amount]);

        $stmt = $this->pdo->prepare('INSERT INTO wallet_transactions (wallet_id, amount, type, description, created_at) VALUES (:wallet_id, :amount, :type, :description, NOW())');
        $stmt->execute([':wallet_id' => $walletId, ':amount' => $amount, ':type' => 'credit', ':description' => $description]);

        $id = (int)$this->pdo->lastInsertId();
        return new WalletTransaction([
            'id' => $id,
            'wallet_id' => $walletId,
            'amount' => $amount,
            'type' => 'credit',
            'description' => $description,
        ]);
    }

    public function debit(int $walletId, float $amount, string $description = ''): WalletTransaction
    {
        $stmt = $this->pdo->prepare('UPDATE wallets SET balance = balance - :amount WHERE id = :wallet_id');
        $stmt->execute([':wallet_id' => $walletId, ':amount' => $amount]);

        $stmt = $this->pdo->prepare('INSERT INTO wallet_transactions (wallet_id, amount, type, description, created_at) VALUES (:wallet_id, :amount, :type, :description, NOW())');
        $stmt->execute([':wallet_id' => $walletId, ':amount' => $amount, ':type' => 'debit', ':description' => $description]);

        $id = (int)$this->pdo->lastInsertId();
        return new WalletTransaction([
            'id' => $id,
            'wallet_id' => $walletId,
            'amount' => $amount,
            'type' => 'debit',
            'description' => $description,
        ]);
    }

    public function getBalance(int $walletId): float
    {
        $stmt = $this->pdo->prepare('SELECT balance FROM wallets WHERE id = :wallet_id');
        $stmt->execute([':wallet_id' => $walletId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['balance'] : 0.0;
    }

    public function getTransactions(int $walletId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM wallet_transactions WHERE wallet_id = :wallet_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':wallet_id', $walletId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => new WalletTransaction($row), $rows);
    }
}
