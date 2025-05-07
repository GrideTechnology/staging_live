<?php

use Illuminate\Database\Seeder;

class ModelHasRoles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('common')->statement("TRUNCATE TABLE model_has_roles");
        DB::connection('common')->statement("INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
                                        (1, 'App\\Models\\Common\\Admin', 1),
                                        (2, 'App\\Models\\Common\\Admin', 2),
                                        (3, 'App\\Models\\Common\\Admin', 3),
                                        (2, 'App\\Models\\Common\\Admin', 4),
                                        (2, 'App\\Models\\Common\\Admin', 5),
                                        (2, 'App\\Models\\Common\\Admin', 6),
                                        (2, 'App\\Models\\Common\\Admin', 7),
                                        (3, 'App\\Models\\Common\\Admin', 8),
                                        (3, 'App\\Models\\Common\\Admin', 9),
                                        (3, 'App\\Models\\Common\\Admin', 10),
                                        (3, 'App\\Models\\Common\\Admin', 11);
                                        ");

    }
}
