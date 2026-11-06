<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add guard_name to roles if not exists
        if (!Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('guard_name')->default('web')->after('name');
            });
        }

        // Add guard_name to permissions if not exists
        if (Schema::hasTable('permissions') && !Schema::hasColumn('permissions', 'guard_name')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('guard_name')->default('web')->after('name');
            });
        }

        // Create model_has_permissions table
        if (!Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type']);
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->primary(['permission_id', 'model_id', 'model_type']);
            });
        }

        // Create model_has_roles table
        if (!Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type']);
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->primary(['role_id', 'model_id', 'model_type']);
            });
        }

        // Create role_has_permissions table
        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->primary(['permission_id', 'role_id']);
            });
        }

        // Migrate role_user data to model_has_roles
        if (Schema::hasTable('role_user')) {
            DB::table('role_user')->orderBy('user_id')->chunk(100, function($rows) {
                foreach ($rows as $row) {
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id' => $row->role_id,
                        'model_type' => 'App\\Nrgi\\Entities\\User\\User',
                        'model_id' => $row->user_id,
                    ]);
                }
            });
        }

        // Migrate permission_role data to role_has_permissions
        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')->chunk(100, function($rows) {
                foreach ($rows as $row) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $row->permission_id,
                        'role_id' => $row->role_id,
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        if (Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', fn(Blueprint $t) => $t->dropColumn('guard_name'));
        }
        if (Schema::hasColumn('permissions', 'guard_name')) {
            Schema::table('permissions', fn(Blueprint $t) => $t->dropColumn('guard_name'));
        }
    }
};
