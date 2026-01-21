<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {
    }

    /**
     * Display a listing of tasks with filter, sort, and pagination.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [];
        
        if ($request->has('status')) {
            $filters['status'] = $request->input('status');
        }
        
        if ($request->has('priority')) {
            $filters['priority'] = $request->input('priority');
        }
        
        if ($request->has('search')) {
            $filters['search'] = $request->input('search');
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);

        $tasks = $this->taskService->getAllTasks($filters, $sortBy, $sortDir, $perPage, $page);

        return response()->json([
            'success' => true,
            'message' => 'Tasks retrieved successfully',
            'data' => TaskResource::collection($tasks->items()),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'last_page' => $tasks->lastPage(),
                'from' => $tasks->firstItem(),
                'to' => $tasks->lastItem(),
            ],
        ], 200);
    }

    /**
     * Store a newly created task.
     * 
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * Display the specified task.
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if (!ValidationHelper::isValidMongoObjectId($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid task ID format',
            ], 400);
        }

        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task retrieved successfully',
            'data' => new TaskResource($task),
        ], 200);
    }

    /**
     * Update the specified task.
     * 
     * @param UpdateTaskRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, string $id): JsonResponse
    {
        if (!ValidationHelper::isValidMongoObjectId($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid task ID format',
            ], 400);
        }

        $task = $this->taskService->updateTask($id, $request->validated());

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => new TaskResource($task),
        ], 200);
    }

    /**
     * Remove the specified task.
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        if (!ValidationHelper::isValidMongoObjectId($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid task ID format',
            ], 400);
        }

        $deleted = $this->taskService->deleteTask($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ], 200);
    }
}
