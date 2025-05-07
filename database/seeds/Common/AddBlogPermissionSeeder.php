<?php
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddBlogPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $admin = Role::where('name', 'ADMIN')->first();
        DB::table('permissions')->insert([
            ['name' => 'blog-list', 'display_name' => 'Blogs', 'guard_name' => 'admin', 'group_name' => 'Others'],
            ['name' => 'blog-history', 'display_name' => 'Blog History', 'guard_name' => 'admin', 'group_name' => 'Others'],
            ['name' => 'blog-create', 'display_name' => 'Create Blog', 'guard_name' => 'admin', 'group_name' => 'Others'],
            ['name' => 'blog-edit', 'display_name' => 'Edit Blog', 'guard_name' => 'admin', 'group_name' => 'Others'],
            ['name' => 'blog-delete', 'display_name' => 'Delete Blog', 'guard_name' => 'admin', 'group_name' => 'Others']
        ]);

        $admin_permissions = Permission::select('id')->get();
        $admin->givePermissionTo($admin_permissions->toArray());

    }
}
