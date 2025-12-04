<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class GenerateFromOpenApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-from-openapi {yaml-file : Path to the OpenAPI YAML file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate routes, controllers, form requests, and API resources from OpenAPI YAML specification';

    protected array $spec = [];
    protected array $generatedFiles = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $yamlPath = $this->argument('yaml-file');

        if (! File::exists($yamlPath)) {
            $this->error("YAML file not found: {$yamlPath}");
            return self::FAILURE;
        }

        $this->info("Parsing OpenAPI specification from: {$yamlPath}");

        try {
            $this->spec = Yaml::parseFile($yamlPath);
        } catch (\Exception $e) {
            $this->error("Failed to parse YAML file: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("API: {$this->spec['info']['title']} v{$this->spec['info']['version']}");
        $this->newLine();

        // Generate code
        $this->generateFormRequests();
        $this->generateApiResources();
        $this->generateController();
        $this->generateRoutes();

        // Summary
        $this->newLine();
        $this->info('✓ Generation complete!');
        $this->newLine();
        $this->info('Generated files:');
        foreach ($this->generatedFiles as $file) {
            $this->line("  - {$file}");
        }

        $this->newLine();
        $this->warn('Next steps:');
        $this->line('1. Review and customize generated files as needed');
        $this->line('2. Implement controller method logic');
        $this->line('3. Add generated routes to routes/api.php');
        $this->line('4. Test endpoints using the API documentation');

        return self::SUCCESS;
    }

    protected function generateFormRequests(): void
    {
        $this->info('Generating Form Request classes...');

        $schemas = $this->spec['components']['schemas'] ?? [];

        foreach ($schemas as $schemaName => $schema) {
            if (Str::endsWith($schemaName, 'Request')) {
                $className = $schemaName;
                $this->createFormRequest($className, $schema);
            }
        }
    }

    protected function createFormRequest(string $className, array $schema): void
    {
        $properties = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];

        $rules = [];
        foreach ($properties as $fieldName => $field) {
            $fieldRules = [];

            if (in_array($fieldName, $required)) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'sometimes';
            }

            // Add type validation
            if (isset($field['type'])) {
                switch ($field['type']) {
                    case 'string':
                        $fieldRules[] = 'string';
                        if ($field['format'] === 'email') {
                            $fieldRules[] = 'email';
                        }
                        if (isset($field['minLength'])) {
                            $fieldRules[] = "min:{$field['minLength']}";
                        }
                        if (isset($field['maxLength'])) {
                            $fieldRules[] = "max:{$field['maxLength']}";
                        }
                        break;
                    case 'boolean':
                        $fieldRules[] = 'boolean';
                        break;
                    case 'integer':
                        $fieldRules[] = 'integer';
                        break;
                }
            }

            $rules[$fieldName] = $fieldRules;
        }

        $rulesCode = $this->generateRulesArray($rules);

        $content = <<<PHP
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$className} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
{$rulesCode}
    }
}
PHP;

        $path = app_path("Http/Requests/{$className}.php");
        File::put($path, $content);
        $this->generatedFiles[] = "app/Http/Requests/{$className}.php";
        $this->line("  Created: {$className}");
    }

    protected function generateRulesArray(array $rules): string
    {
        $lines = ['        return ['];

        foreach ($rules as $field => $fieldRules) {
            $rulesStr = implode("', '", $fieldRules);
            $lines[] = "            '{$field}' => ['{$rulesStr}'],";
        }

        $lines[] = '        ];';

        return implode("\n", $lines);
    }

    protected function generateApiResources(): void
    {
        $this->info('Generating API Resource classes...');

        $schemas = $this->spec['components']['schemas'] ?? [];

        foreach ($schemas as $schemaName => $schema) {
            if (Str::endsWith($schemaName, 'Response') && !Str::endsWith($schemaName, 'ErrorResponse')) {
                $className = str_replace('Response', 'Resource', $schemaName);
                $this->createApiResource($className, $schema);
            }
        }
    }

    protected function createApiResource(string $className, array $schema): void
    {
        $properties = $schema['properties'] ?? [];
        $fields = [];

        foreach ($properties as $fieldName => $field) {
            $fields[] = "            '{$fieldName}' => \$this->{$this->snakeCase($fieldName)},";
        }

        $fieldsCode = implode("\n", $fields);

        $content = <<<PHP
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {$className} extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request \$request): array
    {
        return [
{$fieldsCode}
        ];
    }
}
PHP;

        $path = app_path("Http/Resources/{$className}.php");
        File::put($path, $content);
        $this->generatedFiles[] = "app/Http/Resources/{$className}.php";
        $this->line("  Created: {$className}");
    }

    protected function generateController(): void
    {
        $this->info('Generating Controller...');

        $controllerName = Str::studly($this->spec['info']['title'] ?? 'Api') . 'Controller';
        $methods = [];

        foreach ($this->spec['paths'] ?? [] as $path => $pathItem) {
            foreach ($pathItem as $method => $operation) {
                if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
                    $methodName = $operation['operationId'] ?? Str::camel($method . '_' . str_replace('/', '_', $path));
                    $summary = $operation['summary'] ?? 'Handle request';

                    $methods[] = <<<PHP
    /**
     * {$summary}
     */
    public function {$methodName}(Request \$request): JsonResponse
    {
        // TODO: Implement {$methodName} logic
        return response()->json([
            'message' => 'Not implemented yet',
        ], 501);
    }
PHP;
                }
            }
        }

        $methodsCode = implode("\n\n", $methods);

        $content = <<<PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {$controllerName} extends Controller
{
{$methodsCode}
}
PHP;

        $path = app_path("Http/Controllers/{$controllerName}.php");
        File::put($path, $content);
        $this->generatedFiles[] = "app/Http/Controllers/{$controllerName}.php";
        $this->line("  Created: {$controllerName}");
    }

    protected function generateRoutes(): void
    {
        $this->info('Generating route definitions...');

        $routes = [];
        $controllerName = Str::studly($this->spec['info']['title'] ?? 'Api') . 'Controller';

        foreach ($this->spec['paths'] ?? [] as $path => $pathItem) {
            foreach ($pathItem as $method => $operation) {
                if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
                    $methodName = $operation['operationId'] ?? Str::camel($method . '_' . str_replace('/', '_', $path));
                    $routePath = str_replace('/api/', '', $path);
                    $httpMethod = strtoupper($method);

                    // Check if requires auth
                    $requiresAuth = isset($operation['security']) && !empty($operation['security']);

                    $routes[] = [
                        'method' => $httpMethod,
                        'path' => $routePath,
                        'handler' => "[{$controllerName}::class, '{$methodName}']",
                        'requiresAuth' => $requiresAuth,
                    ];
                }
            }
        }

        // Generate route code
        $routeLines = ['// Generated routes - add these to routes/api.php', ''];

        $publicRoutes = array_filter($routes, fn($r) => !$r['requiresAuth']);
        $protectedRoutes = array_filter($routes, fn($r) => $r['requiresAuth']);

        if (!empty($publicRoutes)) {
            $routeLines[] = '// Public routes';
            foreach ($publicRoutes as $route) {
                $method = strtolower($route['method']);
                $routeLines[] = "Route::{$method}('{$route['path']}', {$route['handler']});";
            }
            $routeLines[] = '';
        }

        if (!empty($protectedRoutes)) {
            $routeLines[] = '// Protected routes';
            $routeLines[] = "Route::middleware('auth:sanctum')->group(function () {";
            foreach ($protectedRoutes as $route) {
                $method = strtolower($route['method']);
                $routeLines[] = "    Route::{$method}('{$route['path']}', {$route['handler']});";
            }
            $routeLines[] = '});';
        }

        $routesCode = implode("\n", $routeLines);

        $path = base_path('routes/generated_routes.txt');
        File::put($path, $routesCode);
        $this->generatedFiles[] = 'routes/generated_routes.txt';
        $this->line('  Created: routes/generated_routes.txt');
    }

    protected function snakeCase(string $string): string
    {
        return Str::snake($string);
    }
}
