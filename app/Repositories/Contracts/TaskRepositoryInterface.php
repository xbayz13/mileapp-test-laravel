<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
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
    public function getAll(string $userId, array $filters = [], string $sortBy = 'created_at', string $sortDir = 'desc', int $perPage = 15, int $page = 1): LengthAwarePaginator;

    /**
     * Find task by ID
     *
     * @param string $id
     * @param string $userId
     * @return Task|null
     */
    public function findById(string $id, string $userId): ?Task;

    /**
     * Create a new task
     *
     * @param array $data
     * @return Task
     */
    public function create(array $data): Task;

    /**
     * Update a task
     *
     * @param string $id
     * @param array $data
     * @param string $userId
     * @return Task|null
     */
    public function update(string $id, array $data, string $userId): ?Task;

    /**
     * Delete a task
     *
     * @param string $id
     * @param string $userId
     * @return bool
     */
    public function delete(string $id, string $userId): bool;
}
