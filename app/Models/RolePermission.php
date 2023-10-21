<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermission extends Model
{
    use HasFactory;

    protected $table = 'role_has_permissions';

    protected $fillable = ['role_id','permission_id'];

    public $timestamps = false;




}
