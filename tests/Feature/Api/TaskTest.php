<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use WithFaker;

    /**
     * Get authenticated user token for testing
     *
     * @return string
     */
    private function getAuthToken(): string
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'),
            ]
        );

        return auth('api')->login($user);
    }

    /**
     * Test listing tasks with pagination.
     */
    public function test_index_tasks(): void
    {
        // Create some test tasks
        Task::factory()->count(5)->create();

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test filtering tasks by status.
     */
    public function test_index_tasks_filter_by_status(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'completed']);

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tasks?status=completed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        if (count($data) > 0) {
            $this->assertEquals('completed', $data[0]['status']);
        }
    }

    /**
     * Test filtering tasks by priority.
     */
    public function test_index_tasks_filter_by_priority(): void
    {
        Task::factory()->create(['priority' => 'high']);
        Task::factory()->create(['priority' => 'low']);

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tasks?priority=high');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test searching tasks.
     */
    public function test_index_tasks_search(): void
    {
        Task::factory()->create(['title' => 'Test Task']);
        Task::factory()->create(['title' => 'Another Task']);

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tasks?search=Test');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test sorting tasks.
     */
    public function test_index_tasks_sort(): void
    {
        Task::factory()->create(['title' => 'A Task']);
        Task::factory()->create(['title' => 'Z Task']);

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tasks?sort_by=title&sort_dir=asc');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test pagination.
     */
    public function test_index_tasks_pagination(): void
    {
        Task::factory()->count(25)->create();

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tasks?per_page=10&page=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'per_page' => 10,
                    'current_page' => 2,
                ],
            ]);
    }

    /**
     * Test creating a task.
     */
    public function test_store_task(): void
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'pending',
            'priority' => 'medium',
        ];

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'title',
                    'description',
                    'status',
                    'priority',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'New Task',
                ],
            ]);
        
        // Verify _id exists (MongoDB ObjectId)
        $data = $response->json('data');
        $this->assertArrayHasKey('_id', $data);
        $this->assertNotEmpty($data['_id']);
    }

    /**
     * Test creating task with validation error.
     */
    public function test_store_task_validation_error(): void
    {
        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test showing a task.
     */
    public function test_show_task(): void
    {
        $task = Task::factory()->create();

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/tasks/{$task->_id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => true,
            ]);
        
        // Verify the task data
        $data = $response->json('data');
        $this->assertEquals((string) $task->_id, $data['_id']);
        $this->assertEquals($task->title, $data['title']);
    }

    /**
     * Test showing non-existent task.
     */
    public function test_show_task_not_found(): void
    {
        $fakeId = '507f1f77bcf86cd799439011';

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/tasks/{$fakeId}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Task not found',
            ]);
    }

    /**
     * Test updating a task.
     */
    public function test_update_task(): void
    {
        $task = Task::factory()->create(['status' => 'pending']);

        $updateData = [
            'title' => 'Updated Task',
            'status' => 'in_progress',
        ];

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/tasks/{$task->_id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Updated Task',
                    'status' => 'in_progress',
                ],
            ]);
    }

    /**
     * Test updating task with validation error.
     */
    public function test_update_task_validation_error(): void
    {
        $task = Task::factory()->create();

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/tasks/{$task->_id}", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test updating non-existent task.
     */
    public function test_update_task_not_found(): void
    {
        $fakeId = '507f1f77bcf86cd799439011';

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/tasks/{$fakeId}", [
                'title' => 'Updated',
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Task not found',
            ]);
    }

    /**
     * Test deleting a task.
     */
    public function test_destroy_task(): void
    {
        $task = Task::factory()->create();

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/tasks/{$task->_id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);

        $this->assertNull(Task::find($task->_id));
    }

    /**
     * Test deleting non-existent task.
     */
    public function test_destroy_task_not_found(): void
    {
        $fakeId = '507f1f77bcf86cd799439011';

        $token = $this->getAuthToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/tasks/{$fakeId}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Task not found',
            ]);
    }
}
