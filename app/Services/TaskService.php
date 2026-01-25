<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Get all tasks with filters, sorting, and pagination
     *
     * @param string $userId
     * @param array $filters
     * @param string $sortBy
     * @param string $sortDir
     * @param int $perPage
     * @param int $page
     * @return LengthAwarePaginator
     */
    public function getAllTasks(string $userId, array $filters = [], string $sortBy = 'created_at', string $sortDir = 'desc', int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        // Validate per_page limit
        $perPage = min($perPage, 100);
        $page = max($page, 1);

        return $this->taskRepository->getAll($userId, $filters, $sortBy, $sortDir, $perPage, $page);
    }

    /**
     * Get task by ID
     *
     * @param string $id
     * @param string $userId
     * @return Task|null
     */
    public function getTaskById(string $id, string $userId): ?Task
    {
        return $this->taskRepository->findById($id, $userId);
    }

    /**
     * Create a new task
     *
     * @param array $data
     * @param string $userId
     * @return Task
     */
    public function createTask(array $data, string $userId): Task
    {
        $data['user_id'] = $userId;
        $data['status'] = $data['status'] ?? 'pending';
        $data['priority'] = $data['priority'] ?? 'medium';

        // Convert due_date to DateTime if provided
        if (isset($data['due_date']) && $data['due_date']) {
            $data['due_date'] = new \DateTime($data['due_date']);
        }

        return $this->taskRepository->create($data);
    }

    /**
     * Update a task
     *
     * @param string $id
     * @param array $data
     * @param string $userId
     * @return Task|null
     */
    public function updateTask(string $id, array $data, string $userId): ?Task
    {
        $task = $this->taskRepository->findById($id, $userId);

        if (!$task) {
            return null;
        }

        // Handle status change logic
        if (isset($data['status'])) {
            if ($data['status'] === 'completed' && !$task->completed_at) {
                $data['completed_at'] = now();
            } elseif ($data['status'] !== 'completed') {
                $data['completed_at'] = null;
            }
        }

        // Convert due_date to DateTime if provided
        if (isset($data['due_date'])) {
            $data['due_date'] = $data['due_date']
                ? new \DateTime($data['due_date'])
                : null;
        }

        return $this->taskRepository->update($id, $data, $userId);
    }

    /**
     * Delete a task
     *
     * @param string $id
     * @param string $userId
     * @return bool
     */
    public function deleteTask(string $id, string $userId): bool
    {
        return $this->taskRepository->delete($id, $userId);
    }
}
