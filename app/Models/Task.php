<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    
    protected $collection = 'tasks';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return '_id';
    }

    /**
     * The user who created the task.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Ensure _id is present in the array
        if (isset($array['id']) && !isset($array['_id'])) {
            $array['_id'] = $array['id'];
        }
        
        return $array;
    }
}
