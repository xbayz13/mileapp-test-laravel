# Task Management System

**Author:** Muhammad Ikhsan Pahdian

A full-stack Task Management System built with Laravel 12 (PHP 8.2+) and Vue.js 3, featuring a clean architecture, MongoDB database, and comprehensive task management capabilities.

## Overview

This project demonstrates a production-ready task management application with multiple view modes (Dashboard, List, Kanban), advanced filtering, JWT authentication, and optimized database queries.

## Features

- **Task CRUD Operations** - Create, read, update, and delete tasks with status, priority, and due dates
- **Multiple View Modes** - Dashboard with statistics, paginated list view, and drag-and-drop Kanban board
- **Advanced Filtering** - Filter by status, priority, and full-text search across title/description
- **JWT Authentication** - Stateless authentication with token refresh
- **Responsive UI** - Interface with Tailwind CSS, dark mode, and mobile support

## Tech Stack

**Backend:**
- Laravel 12 (PHP 8.2+)
- MongoDB (via `mongodb/laravel-mongodb`)
- JWT Authentication (`tymon/jwt-auth`)

**Frontend:**
- Vue.js 3 (Composition API)
- Pinia (State Management)
- Vue Router
- Tailwind CSS
- Radix Vue (UI Components)

## Architecture

### Backend Architecture: Layered with Repository Pattern

```
Controller → Service → Repository → Model
```

**Key Design Decisions:**

1. **Layered Architecture** - Clear separation: Controllers handle HTTP, Services contain business logic, Repositories abstract data access
2. **Dependency Injection** - Services depend on interfaces (`TaskRepositoryInterface`), enabling easy testing and swapping implementations
3. **Form Request Validation** - Custom `FormRequest` classes (`StoreTaskRequest`, `UpdateTaskRequest`) with centralized validation rules
4. **API Resources** - `TaskResource` transforms model data for consistent API responses
5. **MongoDB** - Chosen for schema flexibility, document structure fit, and horizontal scalability

### Frontend Architecture: Composition API & Composables

- **Stores (Pinia)**: State management (`task.js`, `auth.js`)
- **Services**: API communication layer (`taskService.js`, `authService.js`)
- **Composables**: Reusable logic (`useTaskFilters`, `usePagination`, `useDateFormat`)
- **Components**: UI components following single responsibility principle

## Database Indexes

MongoDB indexes are defined in `db/indexes.js` and created with `background: true` for non-blocking operations.

The indexing strategy optimizes the most common query patterns in the application:

**Single Field Indexes:**
- **`idx_status`** - Most frequent filter: Kanban board groups tasks by status, dashboard counts by status, task list filters by status
- **`idx_priority`** - Common filter: Users frequently filter tasks by priority (low/medium/high) in the task list view
- **`idx_due_date`** - Critical for sorting and date-based queries: Dashboard shows "due today" and "past due" tasks, tasks sorted by due date
- **`idx_created_at`** - Default sorting: Tasks displayed newest first, efficient pagination when sorted by creation date
- **`idx_updated_at`** - Alternative sorting: Users may sort by last updated, useful for "recent activity" features
- **`idx_completed_at`** - Sparse index: Only indexes completed tasks, enables efficient filtering and analytics on completed tasks

**Compound Indexes:**
- **`idx_status_priority`** - Optimizes combined filters: Users often filter by both status AND priority simultaneously
- **`idx_status_created_at`** - Common pattern: Filter by status (Kanban columns), then sort by date (newest first within each column)
- **`idx_status_priority_created_at`** - Complex queries: Filter by status + priority, then sort by date - optimizes paginated task list with multiple filters

**Text Index:**
- **`idx_text_search`** - Full-text search: Users search across title (weight: 10) and description (weight: 5), with title matches ranked higher

**Index Strategy Benefits:**
- Optimizes all common query patterns (filtering, sorting, pagination)
- Supports Kanban board grouping and dashboard statistics efficiently
- Enables fast full-text search across task content
- Balances read performance gains with minimal write overhead

## Key Strengths

1. **Clean Code** - SOLID principles, clear separation of concerns
2. **Performance** - Comprehensive indexing strategy, pagination (max 100 items), efficient queries
3. **Security** - Input validation, JWT authentication, MongoDB ObjectId validation, password hashing
4. **Maintainability** - Modular structure, interface-based design, comprehensive type hints
5. **Scalability** - Repository pattern allows caching layer, stateless API for multi-server deployment
6. **Developer Experience** - Full type hints, PHPDoc comments, consistent naming conventions

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/     # API controllers
│   ├── Requests/            # Form request validation
│   └── Resources/            # API resource transformers
├── Models/                  # Eloquent models (MongoDB)
├── Repositories/            # Data access layer
│   └── Contracts/           # Repository interfaces
└── Services/                # Business logic layer

resources/js/
├── stores/                  # Pinia stores
├── services/                # API service layer
├── composables/             # Reusable Vue composables
├── components/              # Vue components
└── views/                   # Page views

db/
└── indexes.js               # MongoDB index definitions
```

## Installation

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Create MongoDB indexes
mongosh <database_name> < db/indexes.js

# Build assets
npm run build

# Start development server
php artisan serve
npm run dev
```

## License

MIT License
