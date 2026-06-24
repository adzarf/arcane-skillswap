<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\UserSkillRepository;
use App\Models\Booking;

class BookingService
{
    private BookingRepository $repo;
    private UserSkillRepository $userSkillRepo;

    public function __construct(BookingRepository $repo, UserSkillRepository $userSkillRepo)
    {
        $this->repo = $repo;
        $this->userSkillRepo = $userSkillRepo;
    }

    public function requestBooking(int $learnerId, array $data): Booking
    {
        $userSkillId = (int)($data['user_skill_id'] ?? 0);
        if ($userSkillId <= 0) {
            throw new \Exception('user_skill_id is required');
        }

        $userSkill = $this->userSkillRepo->findById($userSkillId);
        if (!$userSkill) {
            throw new \Exception('Skill offering not found');
        }

        $startTime = $data['start_time'] ?? '';
        $endTime = $data['end_time'] ?? '';
        if (empty($startTime) || empty($endTime)) {
            throw new \Exception('start_time and end_time are required');
        }

        $start = strtotime($startTime);
        $end = strtotime($endTime);
        if ($start === false || $end === false || $start >= $end) {
            throw new \Exception('Invalid time range');
        }

        // Calculate amount based on hourly rate
        $hours = ($end - $start) / 3600;
        $amount = $userSkill->hourly_rate * $hours;

        $booking = new Booking([
            'learner_id' => $learnerId,
            'tutor_id' => $userSkill->user_id,
            'user_skill_id' => $userSkillId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
            'amount' => $amount,
        ]);

        $id = $this->repo->create($booking);
        $booking->id = $id;
        return $booking;
    }

    public function getBooking(int $id): Booking
    {
        $booking = $this->repo->findById($id);
        if (!$booking) {
            throw new \Exception('Booking not found');
        }
        return $booking;
    }

    public function acceptBooking(int $id): Booking
    {
        $booking = $this->getBooking($id);
        if ($booking->status !== 'pending') {
            throw new \Exception('Only pending bookings can be accepted');
        }
        $booking->status = 'accepted';
        $this->repo->update($id, $booking);
        return $booking;
    }

    public function declineBooking(int $id): Booking
    {
        $booking = $this->getBooking($id);
        if ($booking->status !== 'pending') {
            throw new \Exception('Only pending bookings can be declined');
        }
        $booking->status = 'declined';
        $this->repo->update($id, $booking);
        return $booking;
    }

    public function confirmBooking(int $id): Booking
    {
        $booking = $this->getBooking($id);
        if ($booking->status !== 'accepted') {
            throw new \Exception('Only accepted bookings can be confirmed');
        }
        $booking->status = 'confirmed';
        $this->repo->update($id, $booking);
        return $booking;
    }

    public function completeBooking(int $id): Booking
    {
        $booking = $this->getBooking($id);
        if ($booking->status !== 'confirmed') {
            throw new \Exception('Only confirmed bookings can be completed');
        }
        $booking->status = 'completed';
        $this->repo->update($id, $booking);
        return $booking;
    }

    public function cancelBooking(int $id): Booking
    {
        $booking = $this->getBooking($id);
        if (in_array($booking->status, ['completed', 'cancelled'], true)) {
            throw new \Exception('Cannot cancel completed or already cancelled bookings');
        }
        $booking->status = 'cancelled';
        $this->repo->update($id, $booking);
        return $booking;
    }

    public function getLearnerBookings(int $learnerId, int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        $bookings = $this->repo->findByLearner($learnerId, $perPage, $offset);
        return [
            'bookings' => $bookings,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function getTutorBookings(int $tutorId, int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        $bookings = $this->repo->findByTutor($tutorId, $perPage, $offset);
        return [
            'bookings' => $bookings,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
