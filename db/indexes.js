// MongoDB Index Creation Script
// This file contains index creation commands for optimal query performance
// Run this script using: mongosh <database_name> < db/indexes.js

// Switch to the database
use('task-test');

// ============================================
// Tasks Collection Indexes
// ============================================

// Index on status field (for filtering by status)
db.tasks.createIndex(
    { status: 1 },
    { 
        name: "idx_status",
        background: true 
    }
);

// Index on priority field (for filtering by priority)
db.tasks.createIndex(
    { priority: 1 },
    { 
        name: "idx_priority",
        background: true 
    }
);

// Compound index on status and priority (for combined filtering)
db.tasks.createIndex(
    { status: 1, priority: 1 },
    { 
        name: "idx_status_priority",
        background: true 
    }
);

// Text index on title and description (for search functionality)
db.tasks.createIndex(
    { 
        title: "text",
        description: "text"
    },
    { 
        name: "idx_text_search",
        background: true,
        weights: {
            title: 10,  // Title has higher weight than description
            description: 5
        }
    }
);

// Index on due_date (for sorting and filtering by due date)
db.tasks.createIndex(
    { due_date: 1 },
    { 
        name: "idx_due_date",
        background: true 
    }
);

// Index on created_at (for default sorting)
db.tasks.createIndex(
    { created_at: -1 },
    { 
        name: "idx_created_at",
        background: true 
    }
);

// Index on updated_at (for sorting by last updated)
db.tasks.createIndex(
    { updated_at: -1 },
    { 
        name: "idx_updated_at",
        background: true 
    }
);

// Compound index for common query pattern: status + created_at
db.tasks.createIndex(
    { status: 1, created_at: -1 },
    { 
        name: "idx_status_created_at",
        background: true 
    }
);

// Index on completed_at (for filtering completed tasks)
db.tasks.createIndex(
    { completed_at: 1 },
    { 
        name: "idx_completed_at",
        background: true,
        sparse: true  // Only index documents that have this field
    }
);

// Compound index for pagination with filters: status + priority + created_at
db.tasks.createIndex(
    { status: 1, priority: 1, created_at: -1 },
    { 
        name: "idx_status_priority_created_at",
        background: true 
    }
);

// ============================================
// Verify Indexes
// ============================================

print("\n=== Created Indexes ===");
db.tasks.getIndexes().forEach(function(index) {
    print("Index: " + index.name + " on " + JSON.stringify(index.key));
});

print("\n=== Index Creation Complete ===");
