<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
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
    public function getAll(array $filters = [], string $sortBy = 'created_at', string $sortDir = 'desc', int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $query = Task::query();

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'regex', "/{$search}/i")
                  ->orWhere('description', 'regex', "/{$search}/i");
            });
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortDir);

        // Apply pagination
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find task by ID
     *
     * @param string $id
     * @return Task|null
     */
    public function findById(string $id): ?Task
    {
        return Task::find($id);
    }

    /**
     * Create a new task
     *
     * @param array $data
     * @return Task
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Update a task
     *
     * @param string $id
     * @param array $data
     * @return Task|null
     */
    public function update(string $id, array $data): ?Task
    {
        $task = $this->findById($id);
        
        if (!$task) {
            return null;
        }

        $task->update($data);
        $task->refresh();

        return $task;
    }

    /**
     * Delete a task
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $task = $this->findById($id);
        
        if (!$task) {
            return false;
        }

        return $task->delete();
    }
}
