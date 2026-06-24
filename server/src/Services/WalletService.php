<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\WalletRepository;
use App\Models\Wallet;

class WalletService
{
    private WalletRepository $repo;
    private float $platformCommission; // e.g., 0.10 for 10%

    public function __construct(WalletRepository $repo, float $platformCommission = 0.10)
    {
        $this->repo = $repo;
        $this->platformCommission = $platformCommission;
    }

    public function getWallet(int $userId): Wallet
    {
        return $this->repo->getOrCreate($userId);
    }

    public function creditTutorEarnings(int $tutorId, float $bookingAmount): void
    {
        $commission = $bookingAmount * $this->platformCommission;
        $tutorEarnings = $bookingAmount - $commission;

        $wallet = $this->repo->getOrCreate($tutorId);
        $this->repo->credit($wallet->id, $tutorEarnings, "Booking payment (10% commission deducted)");
    }

    public function getWalletBalance(int $userId): float
    {
        $wallet = $this->repo->getOrCreate($userId);
        return $this->repo->getBalance($wallet->id);
    }

    public function getTransactionHistory(int $userId, int $page = 1, int $perPage = 50): array
    {
        $wallet = $this->repo->getOrCreate($userId);
        $offset = ($page - 1) * $perPage;
        $transactions = $this->repo->getTransactions($wallet->id, $perPage, $offset);
        return [
            'transactions' => $transactions,
            'balance' => $this->repo->getBalance($wallet->id),
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
