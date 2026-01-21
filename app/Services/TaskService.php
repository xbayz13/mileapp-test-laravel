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
     * @param array $filters
     * @param string $sortBy
     * @param string $sortDir
     * @param int $perPage
     * @param int $page
     * @return LengthAwarePaginator
     */
    public function getAllTasks(array $filters = [], string $sortBy = 'created_at', string $sortDir = 'desc', int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        // Validate per_page limit
        $perPage = min($perPage, 100);
        $page = max($page, 1);

        return $this->taskRepository->getAll($filters, $sortBy, $sortDir, $perPage, $page);
    }

    /**
     * Get task by ID
     *
     * @param string $id
     * @return Task|null
     */
    public function getTaskById(string $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }

    /**
     * Create a new task
     *
     * @param array $data
     * @return Task
     */
    public function createTask(array $data): Task
    {
        // Set default values
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
     * @return Task|null
     */
    public function updateTask(string $id, array $data): ?Task
    {
        $task = $this->taskRepository->findById($id);

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

        return $this->taskRepository->update($id, $data);
    }

    /**
     * Delete a task
     *
     * @param string $id
     * @return bool
     */
    public function deleteTask(string $id): bool
    {
        return $this->taskRepository->delete($id);
    }
}
