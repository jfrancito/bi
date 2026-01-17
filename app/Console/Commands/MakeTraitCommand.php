<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeTraitCommand extends Command
{
    protected $signature = 'make:trait {name}';
    protected $description = 'Create a new trait';

    public function handle()
    {
        $name = $this->argument('name');
        $path = app_path("Traits/{$name}.php");

        // Crear directorio si no existe
        if (!File::exists(app_path('Traits'))) {
            File::makeDirectory(app_path('Traits'), 0755, true);
        }

        // Verificar si el trait ya existe
        if (File::exists($path)) {
            $this->error("Trait {$name} already exists!");
            return 1;
        }

        // Contenido del trait
        $stub = <<<EOT
        <?php

        namespace App\Traits;

        trait {$name}
        {
            // Add your methods here
        }
        EOT;

        // Crear el archivo
        File::put($path, $stub);

        $this->info("Trait {$name} created successfully at {$path}");
        return 0;
    }
}