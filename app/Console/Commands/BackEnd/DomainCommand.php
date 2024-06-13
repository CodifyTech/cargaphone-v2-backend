<?php

namespace App\Console\Commands\BackEnd;

use App\Console\Commands\FrontEnd\DataTypesConverter;
use App\Console\Commands\Input as InputBase;
use App\Domains\Auth\Enums\PermissionActionsEnum;
use Domains\Auth\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Str;

use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\error;

class DomainCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdf:ddd {--frontend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criar um DDD (Domain Driven Design)';

    protected string $domainName;
    protected string $controllerName;
    protected string $bllName;
    protected string $requestName;
    protected string $seederName;
    protected string $serviceName;
    protected string $modelName;
    protected string $resourceName;
    protected string $migrationName;
    protected bool $foreignKey;
    protected array $foreigners = [];
    protected string $schemes;

    private CDFTemplateCommands $cdfTemplateCommand;

    protected InputBase $command;

    private Filesystem $fileSystem;

    const ACTION_DIR = __DIR__."/../../../Domains/%s/%s/%s";

    public function __construct(CDFTemplateCommands $cdfTemplateCommand, InputBase $command)
    {
        parent::__construct();

        $this->cdfTemplateCommand = $cdfTemplateCommand;
        $this->command = $command;
        $this->fileSystem = new Filesystem();
        $this->foreignKey = true;
    }

    public function handle(): void
    {
        $this->domainName = text(
            label: 'Domain',
            placeholder: 'Digite o nome do Domain',
            required: 'Por favor, digite o nome do Domain'
        );

        $this->controllerName = text(
            label: 'Controller',
            placeholder: 'Digite o nome da Controller',
            default: "{$this->domainName}Controller",
            required: 'Por favor, digite o nome da controller',
        );

        $this->bllName = text(
            label: 'BLL',
            placeholder: 'Digite o nome da BLL',
            default: "{$this->domainName}BLL",
            required: 'Por favor, digite o nome da BLL',
        );

        $this->serviceName = text(
            label: 'Service',
            placeholder: 'Digite o nome da Service',
            default: "{$this->domainName}Service",
            required: 'Por favor, digite o nome da service',
        );

        $this->requestName = text(
            label: 'Service',
            placeholder: 'Digite o nome do Request',
            default: "{$this->domainName}Request",
            required: 'Por favor, digite o nome do Request',
        );

        $this->seederName = text(
            label: 'Seeder',
            placeholder: 'Digite o nome do Seeder',
            default: "{$this->domainName}Seeder",
            required: 'Por favor, digite o nome do Seeder',
        );

        $this->resourceName = text(
            label: 'Resource',
            placeholder: 'Digite o nome da Resource',
            default: "{$this->domainName}Resource",
            required: 'Por favor, digite o nome da Resource',
        );

        $this->modelName = text(
            label: 'Model',
            placeholder: 'Digite o nome da Model',
            default: $this->domainName,
            required: 'Por favor, digite o nome da Model',
        );

        $this->migrationName = text(
            label: 'Migration',
            placeholder: 'Digite o nome da Migration',
            default: "create_".strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $this->domainName))."_table",
            required: 'Por favor, digite o nome da Migration',
        );

        $this->schemes = text(
            label: 'Schema da Migration | ex: [title=string,100,req;price=float]',
            placeholder: 'Digite o Schema da Migration',
            required: 'Por favor, digite o schema.',
            validate: function($value) {
                $columns = explode(';', rtrim($value, ';'));
                foreach ($columns as $column) {
                    @list($table, $params) = explode('=', $column);
                    @list($type, $option, $required) = explode(',', $params ?? '');

                    if(!$table || !$type){
                        return 'Por favor, digite o schema corretamente.';
                    }

                    if ($required && strtolower($required) !== 'req') {
                        return 'O terceiro parâmetro deve ser "req".';
                    }
                }
                return true;
            }
        );

        $count = 0;
        while($this->foreignKey){
            $messageTemplate = 'Deseja %s chave estrangeira a essa tabela?';
            $this->foreignKey = confirm(
                label: sprintf($messageTemplate, $count > 0 ? 'adicionar outra' : 'adicionar uma'),
                default: false,
                hint: 'Selecione o nome do Domain da chave estrangeira',
            );

            if($this->foreignKey){
                $domainsDir = [];

                foreach(collect(scandir(app_path()."/Domains")) as $dir){
                    if(!in_array($dir, array(".", "..", "Shared"))){
                        $domainsDir[] = $dir;
                    }
                }

                $this->foreigners[$count]['domain'] = select(
                    label: 'Selecione o domain da chave estrangeira',
                    options: $domainsDir,
                    hint: 'Selecione a tabela',
                );

                $modelsDir = [];

                foreach(collect(scandir(app_path()."/Domains/{$this->foreigners[$count]['domain']}/Models")) as $dir){
                    if(!in_array($dir, array(".", ".."))){
                        $modelsDir[] = explode('.', $dir)[0];
                    }
                }

                $this->foreigners[$count]['model'] = select(
                    label: 'Selecione a model estrangeira',
                    options: $modelsDir,
                    default: $modelsDir[0],
                    hint: 'Selecione a tabela',
                );

                $controllersDir = [];

                foreach(collect(scandir(app_path()."/Domains/{$this->foreigners[$count]['domain']}/Controllers")) as $dir){
                    if(!in_array($dir, array(".", ".."))){
                        $controllersDir[] = explode('.', $dir)[0];
                    }
                }

                $this->foreigners[$count]['controller'] = select(
                    label: 'Selecione a Controller estrangeira?',
                    options: $controllersDir,
                    default: $controllersDir[0],
                    hint: 'Selecione a tabela',
                );

                $bllsDir = [];

                foreach(collect(scandir(app_path()."/Domains/{$this->foreigners[$count]['domain']}/BLL")) as $dir){
                    if(!in_array($dir, array(".", ".."))){
                        $bllsDir[] = explode('.', $dir)[0];
                    }
                }

                $this->foreigners[$count]['bll'] = select(
                    label: 'Selecione a BLL estrangeira?',
                    options: $bllsDir,
                    default: $bllsDir[0],
                    hint: 'Selecione a tabela',
                );

                $servicesDir = [];

                foreach(collect(scandir(app_path()."/Domains/{$this->foreigners[$count]['domain']}/Services")) as $dir){
                    if(!in_array($dir, array(".", ".."))){
                        $servicesDir[] = explode('.', $dir)[0];
                    }
                }

                $this->foreigners[$count]['service'] = select(
                    label: 'Selecione a Service estrangeira?',
                    options: $servicesDir,
                    default: $servicesDir[0],
                    hint: 'Selecione a tabela',
                );

                $this->foreigners[$count]['req'] = confirm(
                    label: 'Está chave estrangeira é obrigatória?',
                    default: false,
                );
            }
            $count++;
        }

        $dirDomainName = app_path()."\Domains\\$this->domainName";

        if(!file_exists($dirDomainName))
        {
            info("Criando Domain: ($this->domainName)");

            mkdir($dirDomainName, 0700);

            foreach ($this->directories() as $folder){
                note("Pasta [$folder] criada com sucesso.");
                mkdir(app_path()."\Domains\\$this->domainName\\$folder", 0700, true);
            }

            $scheme = [];
            $foreigners = [];
            $templateForeignersScheme = [];
            $fillable = [];
            $requests = [];

            $fkMethodsBLL = [];
            $fkMethodsController = [];
            $fkMethodsService = [];
            $fkRoutes = [];
            $fksFront = [];

            $templateScheme = explode(';', rtrim($this->schemes, ';'));

            if($this->foreigners > 0) {
                foreach ($this->foreigners as $foreign){
                    $domainNamespace = 'Domains\\' . $foreign['domain'] . '\\Models\\' . $foreign['model'];

                    $modelInstance = new $domainNamespace();
                    $nomeDaTabela = $modelInstance->getTable();

                    $templateForeignersScheme[] = $foreign['req'] ?
                        Str::camel($foreign['model'])."_id=uuid,null,req" :
                        Str::camel($foreign['model'])."_id=uuid";

                    $builder = new Migration( 'id', Str::camel($foreign['model'])."_id", $nomeDaTabela, true, true);
                    $foreigners[] = $builder->builderColumn();

                    $fkMethodsBLL[] = $this->createFKBLLContent($foreign['model'], $this->serviceName);
                    $fkMethodsController[] = $this->createFKControllerContent($foreign['model'], $this->bllName);
                    $fkMethodsService[] = $this->createFKServiceContent($foreign['model'], '\Domains\\' . $foreign['domain'] . '\\Models\\' . $foreign['model']);
                    $fkRoutes[] = $this->createFKRoutes($this->domainName, $foreign['model'], 'Domains\\' . $this->domainName . '\\Controllers\\' . $this->controllerName);

                    $fksFront[] = [
                        'fkName' => $foreign['model'],
                        'fkNameCamel' => Str::camel($foreign['model']),
                        'fkNameSlug' => Str::slug($foreign['model']),
                        'fkRoute' => $this->domainName,
                        'fkMethod' => Str::camel("listar{$foreign['model']}"),
                        'fkRequired' => $foreign['req'],
                        'fkNamespace' => '\Domains\\' . $foreign['domain'] . '\\Models\\' . $foreign['model'],
                        'fkAttribute' => Str::camel($foreign['model']).'_id',
                    ];
                }
            }

            $columns = explode(';', implode(';', array_merge($templateScheme, $templateForeignersScheme)));
            foreach ($columns as $column) {
                @list($table, $params) = explode('=', $column);
                @list($type, $something, $required) = explode(',', $params ?? '');

                $builderMigration = new Migration($table, $type ?? null, $something ?? null, isset($required));
                $builderRequest = new Request($type ?? null, $something ?? null, isset($required));

                $scheme[] = $builderMigration->builderColumn();
                $requests[] = sprintf('"%s" => "%s"', $table, $builderRequest->builderRequest());
                $fillable[] = "'$table',";
            }

            $this->addRoute($this->domainName,'Domains\\' . $this->domainName . '\\Controllers\\' . $this->controllerName, $fkRoutes);
            $this->cdfTemplateCommand->generateStubController($this->domainName, $this->controllerName, $this->bllName, $this->requestName, $fkMethodsController);
            $this->cdfTemplateCommand->generateStubBLL($this->domainName, $this->bllName, $this->serviceName, $fkMethodsBLL);
            $this->cdfTemplateCommand->generateStubServices($this->domainName, $this->serviceName, $this->modelName, $fkMethodsService);
            $this->cdfTemplateCommand->generateStubRequest($this->domainName, $this->requestName, $requests);
            $this->cdfTemplateCommand->generateStubSeeders($this->domainName, $this->seederName);
            $this->cdfTemplateCommand->generateStubResource($this->domainName, $this->resourceName);
            $this->cdfTemplateCommand->generateStubModel($this->domainName, $this->modelName, $fillable, str_replace("create_", "", str_replace("_table", "", $this->migrationName)));

            $this->createForeignKeys();

            $this->cdfTemplateCommand->generateStubMigration($this->domainName, str_replace("create_", "", str_replace("_table", "", $this->migrationName)), $scheme, $foreigners);

            $this->createAbility();

            info("O domain: ($this->domainName) foi criado com sucesso!");

            if($this->option('frontend')){
                $attributes = [];
                $attributesDefault = [];
                $rules = [];

                foreach ($templateScheme as $column) {
                    @list($filed, $params) = explode('=', $column);
                    @list($type, $something, $required) = explode(',', $params ?? '');
                    $attributes[] = "$filed: ".DataTypesConverter::phpToTsDataType($type);
                    $attributesDefault[] = "$filed: ".DataTypesConverter::phpToTsDataTypeValue($type);
                    if($required){
                        $rules[$filed][] = 'rules.requiredValidator';
                    }
                }

                $this->call('cdf:frontend', [
                    'crudName' => $this->domainName,
                    'attributes' => implode("\n", $attributes),
                    'attributesDefault' => implode(",", $attributesDefault),
                    'rules' => $rules,
                    'foreigners' => $fksFront,
                ]);
            }
        }
        else{
            error("O domain ($this->domainName) já existe!");
        }
    }

    public function createFKRoutes($routeName, $fkName, $controllerName)
    {
        $routeName = Str::lower($routeName);
        $fkNameCamel = Str::camel("listar$fkName");
        $fkNameSlug = Str::slug($fkName);
        $fkRouteTemplate = "\tRoute::get('%s', [%s, '%s']);";
        return sprintf($fkRouteTemplate, "{$routeName}/listar/{$fkNameSlug}", "{$controllerName}::class", $fkNameCamel);
    }

    public function createFKControllerContent($fkName, $bllName)
    {
        $bllName = Str::camel($bllName);
        $methodName = Str::camel("listar$fkName");
        return "public function $methodName(Request \$request) {\n\t\t\$options = \$request->all();\n\t\treturn \$this->$bllName->$methodName(\$options);\n\t}";
    }

    private function createFKBLLContent($fkName, $serviceName)
    {
        $serviceName = Str::camel($serviceName);
        $methodName = Str::camel("listar$fkName");
        return "public function $methodName(\$options) {\n\t\treturn \$this->$serviceName->$methodName(\$options);\n\t}";
    }

    private function createFKServiceContent($fkName, $namespaceFk)
    {
        $methodName = Str::camel("listar$fkName");
        return "public function $methodName(\$options) {\n\t\t\$data = $namespaceFk::query()->paginate(\$options['per_page'] ?? 15);\n\t\treturn [\n\t\t\t'data' => \$data->items(),\n\t\t\t'total' => \$data->total(),\n\t\t\t'page' => \$data->currentPage(),\n\t\t];\n\t}";
    }

    private function createAbility(): void
    {
        foreach (collect(PermissionActionsEnum::cases())->except(['block', 'manage'])->toArray() as $action){
            if(!Permission::where('name', strtolower($this->domainName)." $action->value")->exists()){
                Permission::create([
                    'title' => "$this->domainName",
                    'name' => strtolower($this->domainName)." $action->value",
                ]);
            }
        }
    }

    private function createForeignKeys(): void
    {
        if($this->foreigners > 0)
        {
            foreach($this->foreigners as $foreign){
                $this->updateModel($foreign['model'], $this->modelName, $foreign['domain'], $this->domainName, 'hasMany');
                $this->updateModel($this->modelName, $foreign['model'], $this->domainName, $foreign['domain'], 'belongsTo');
            }
        }
    }

    protected function updateModel($modelName, $modelRelation, $domainName, $domainRelation, $relationship): void
    {
        $modelDir = sprintf(self::ACTION_DIR, $domainName, 'Models', "$modelName.php");
        $content = Str::substr($this->fileSystem->get($modelDir), 0, -2);

        list($importAdded, $importRelationsUsing, $checkTo, $class) = $this->getFileContentProperties($modelDir, $modelName, $domainName, $relationship);

        $modelContent = $this->updateModelContent($content, $modelRelation, $domainRelation, $importAdded, $importRelationsUsing, $relationship, $class);

        $domainNamespace = 'Domains\\' . $domainRelation . '\\Models\\' . $modelRelation;
        $modelInstance = new $domainNamespace();
        $modelNamePlural =  ucwords($modelInstance->getTable());

        $methodName = $relationship == 'hasMany' ? Str::camel($modelNamePlural) : Str::camel($modelRelation);

        $modelContent = $this->addRelationMethod($modelContent, $checkTo, $modelRelation, $relationship, $methodName);

        if(!$importAdded || !$checkTo){
            $this->fileSystem->put($modelDir, $modelContent);
        }
    }

    protected function getFileContentProperties(string $modelDir, string $modelName, $domainName, $relationship): array
    {
        $fileContent = $this->fileSystem->lines($modelDir, true);
        $importRelationsUsing = false;
        $importAdd = false;
        $checkTo = false;
        $class = '';

        $domainNamespace = 'Domains\\' . $domainName . '\\Models\\' . $modelName;
        $modelInstance = new $domainNamespace();
        $modelNamePlural =  ucwords($modelInstance->getTable());

        $templateMethod = "public function %s(): %s";
        $templateRelationUsing = 'use Illuminate\Database\Eloquent\Relations\\%s;';

        foreach ($fileContent as $line) {
            if (!$importAdd && str_contains($line, "use Domains\\$domainName\\Models\\$modelName;")) {
                $importAdd = true;
            }

            if (!$importRelationsUsing && str_contains($line, sprintf($templateRelationUsing, Str::studly($relationship)))) {
                $importRelationsUsing = true;
            }

            if (!$checkTo &&
                str_contains(trim($line), sprintf($templateMethod, $relationship == 'hasMany' ? Str::camel($modelNamePlural) : Str::camel($modelName), Str::studly($relationship)))) {
                $checkTo = true;
            }

            if ($importAdd && $importRelationsUsing && $checkTo) {
                break;
            }

            if (!$class && str_contains($line, 'class')) {
                $class = trim($line);
                break;
            }
        }

        return [$importAdd, $importRelationsUsing, $checkTo, $class];
    }

    protected function updateModelContent(string $modelContent, $modelName, $domainName, bool $importAdded, bool $importRelationsUsing, $relationship, string $class): array|string
    {
        $useTemplate = [];

        if(!$importAdded && $relationship === "belongsTo" || $relationship === "hasMany"){
            $useTemplate[] = "use Domains\\$domainName\\Models\\$modelName;";
        }
        if(!$importRelationsUsing){
            $useTemplateRelation = "use Illuminate\Database\Eloquent\Relations\\%s;\n";
            $useTemplate[] = sprintf($useTemplateRelation, Str::studly($relationship));
        }
        $useTemplate[] = $class;

        $useTemplateString = "";
        foreach ($useTemplate as $index => $item) {
            $useTemplateString .= $item;
            if ($index < count($useTemplate) - 1) {
                $useTemplateString .= "\n";
            }
        }

        return str_replace("$class", $useTemplateString, $modelContent);
    }

    protected function addRelationMethod(string $modelContent, bool $relationChecked, string $relationSlug, string $relationMethod, string $methodName): string
    {
        if(!$relationChecked){
            $template = "\tpublic function %s(): %s\n\t{\n \t\treturn \$this->%s(%s);\n\t}";
            $relation = sprintf($template, $methodName, Str::studly($relationMethod), $relationMethod, "$relationSlug::class");
            $modelContent .= "\n$relation\n}";
        }
        return $modelContent;
    }

    protected function addRoute($routeName, $controllerName, $fkRoutes): void
    {
        $routeName = Str::slug(strtolower($routeName));
        $apiResource = Str::camel(strtolower($routeName));

        // Caminho para o arquivo api.php
        $filePath = base_path('routes/api.php');

        // Ler o conteúdo do arquivo
        $content = file_get_contents($filePath);

        // Localizar o início do grupo de middleware
        $middlewareStart = strpos($content, "Route::middleware(['auth:sanctum'])->group(function () {");

        // Localizar o final do grupo de middleware
        $middlewareEnd = strrpos($content, '});', $middlewareStart);

        // Adicionar a nova rota dentro do grupo de middleware
        $comment = "\n\t/*\n\t * Route: {$routeName}\n\t * Created at: " . date('Y-m-d H:i:s') . "\n\t */";
        $newRoute = "\n\tRoute::prefix('{$routeName}')->apiResource('{$apiResource}', {$controllerName}::class);\n";
        $newRoute .= "\tRoute::get('{$routeName}/pesquisarpor/{field}/{value}/{relation?}', [{$controllerName}::class, 'search']);\n";
        $newRoute .= implode("\n", $fkRoutes);
        $newRoute .= "\n";
        $content = substr_replace($content, $comment . $newRoute, $middlewareEnd, 0);

        // Gravar o conteúdo de volta no arquivo
        file_put_contents($filePath, $content);
    }

    public function directories(): array
    {
        return [
            'BLL',
            'Controllers',
            'Emails',
            'Enums',
            'Exceptions',
            "Migrations/".config('cdf.api_version')."/alter",
            "Migrations/".config('cdf.api_version')."/create",
            'Models',
            'Policies',
            'Requests',
            'Resources',
            'Rules',
            'Seeders',
            'Services',
        ];
    }
}
