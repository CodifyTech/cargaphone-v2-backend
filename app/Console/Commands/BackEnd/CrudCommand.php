<?php

namespace App\Console\Commands\BackEnd;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Str;
use App\Console\Commands\Input as InputBase;

use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\error;

class CrudCommand extends Command
{
    protected $signature = 'cdf:crud';

    protected $description = 'Criar um crud';

    protected string $domainName;
    protected string $crudName;
    protected string $serviceName;
    protected string $modelName;
    protected string $migrationName;
    protected bool $foreignKey;
    protected string $domainNameForeignKey;
    protected array $foreigners = [];
    protected string $schemes;
    private CDFTemplateCommands $cdfTemplateCommand;

    protected InputBase $command;

    private Filesystem $fileSystem;

    const string ACTION_DIR = __DIR__."/../../../Domains/%s/%s/%s";

    public function __construct(CDFTemplateCommands $cdfTemplateCommand, InputBase $command)
    {
        parent::__construct();

        $this->cdfTemplateCommand = $cdfTemplateCommand;
        $this->command = $command;
        $this->fileSystem = new Filesystem();
        $this->foreignKey = true;
    }

    /**
     * Handle the request.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $this->gatherInput();

        $dirDomainName = app_path()."\Domains\\$this->domainName";

        $scheme = [];
        $foreigners = [];
        $templateForeignersScheme = [];
        $fillable = [];
        $fkMethodsService = [];
        $templateScheme = explode(';', rtrim($this->schemes, ';'));

        if($this->foreigners > 0) {
            foreach ($this->foreigners as $foreign){
                $domainNamespace = "Domains\\{$foreign['domain']}\\Models\\{$foreign['model']}";
                $modelInstance = new $domainNamespace();
                $nomeDaTabela = $modelInstance->getTable();

                $templateForeignersScheme[] = isset($foreign['req']) ?
                    Str::camel($foreign['model'])."_id=uuid,null,req" :
                    Str::camel($foreign['model'])."_id=uuid";

                $builder = new Migration( 'id', Str::camel($foreign['model'])."_id", $nomeDaTabela, true, true);
                $foreigners[] = $builder->builderColumn();
                $fkMethodsService[] = $this->createFKServiceContent($foreign['model'], '\Domains\\' . $foreign['domain'] . '\\Models\\' . $foreign['model']);
            }
        }

        $columns = explode(';', implode(';', array_merge($templateScheme, $templateForeignersScheme)));
        foreach ($columns as $column) {
            @list($table, $params) = explode('=', $column);
            @list($type, $something, $required) = explode(',', $params ?? '');

            $builderMigration = new Migration($table, $type ?? null, $something ?? null, isset($required));

            $scheme[] = $builderMigration->builderColumn();
            $fillable[] = "'$table',";
        }

        try {
            if(file_exists($dirDomainName))
            {
                /** Criando Service */
                $this->createService($fkMethodsService);

                /** Criando Model */
                $this->createModel($fillable);

                /** Criando ForeignKeys */
                $this->createForeignKeys();

                /** Criando Migration */
                $this->createMigration($scheme, $foreigners);
            }else {
                error("O diretório $this->migrationName não existe");
            }
        } catch (Exception $e){
            error($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function gatherInput(): void
    {
        $domainsDir = [];

        foreach(collect(scandir(app_path()."/Domains")) as $dir){
            if(!in_array($dir, array(".", "..", "Shared"))){
                $domainsDir[] = $dir;
            }
        }

        $this->domainName = select(
            label: 'Domain',
            options: $domainsDir,
        );

        $this->foreigners[0]['domain'] = $this->domainName;

        $modelsDir = [];

        foreach(collect(scandir(app_path()."/Domains/{$this->foreigners[0]['domain']}/Models")) as $dir){
            if(!in_array($dir, array(".", ".."))){
                $modelsDir[] = explode('.', $dir)[0];
            }
        }

        $this->foreigners[0]['model'] = select(
            label: 'Selecione a model do domain',
            options: $modelsDir,
        );

        $this->crudName = text(
            label: 'Crud',
            placeholder: 'Digite o nome do crud',
            required: 'Por favor, digite o nome do crud'
        );

        $this->serviceName = text(
            label: 'Service',
            placeholder: 'Digite o nome da Service',
            default: "{$this->crudName}Service",
            required: 'Por favor, digite o nome da service',
        );

        $this->modelName = text(
            label: 'Model',
            placeholder: 'Digite o nome da Model',
            default: $this->crudName,
            required: 'Por favor, digite o nome da Model',
        );

        $this->migrationName = text(
            label: 'Migration',
            placeholder: 'Digite o nome da Migration',
            default: "create_".strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $this->crudName))."_table",
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

        $count = 1;
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
                    hint: 'Selecione a tabela',
                );
            }
            $count++;
        }

        $this->serviceName = Str::match('/Service/', $this->serviceName) ? "$this->serviceName" : "{$this->serviceName}Service";
    }

    private function createService($fkMethodsService): void
    {
        if(!file_exists(sprintf(self::ACTION_DIR, $this->domainName, "Services", "$this->serviceName.php"))){
            $this->cdfTemplateCommand->generateStubServices($this->domainName, $this->serviceName, $this->modelName, $fkMethodsService);

            /**
             * Injetando Service na BLL
             */
            $bllDir = app_path()."\\Domains\\$this->domainName\\BLL\\{$this->domainName}BLL.php";

            $content = $this->fileSystem->get($bllDir);
            $fileContent = $this->fileSystem->lines($bllDir, true);
            $lines = $fileContent->all();
            extract($this->analyzeLines($lines));

            $contentNew = $content;

            if(!$constructorAdd) {
                $contentNew = $this->addConstructor($constructorOld, $content);
            }

            if(!$importAdd) {
                $contentNew = $this->replaceImports($contentNew);
            }

            if(!$constructorAdd || !$importAdd) {
                file_put_contents($bllDir, $contentNew);
            }

            info("A service: ($this->serviceName) foi criada e injetada na BLL ({$this->domainName}BLL)!");
        } else {
            error("A service: ($this->serviceName) já existe!");
        }
    }

    private function createModel($fillable): void
    {
        if(!file_exists(sprintf(self::ACTION_DIR, $this->domainName, "Models", "$this->modelName.php"))){
            $this->cdfTemplateCommand->generateStubModel($this->domainName, $this->modelName, $fillable, str_replace("create_", "", str_replace("_table", "", $this->migrationName)));
            info("A model: ($this->modelName) foi criada com sucesso!");
        } else {
            error("A model: ($this->modelName) já existe!");
        }
    }

    /**
     * @throws FileNotFoundException
     */
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

    private function createFKServiceContent($fkName, $namespaceFk)
    {
        $methodName = Str::camel("listar$fkName");
        return "public function $methodName(\$options) {\n\t\t\$data = $namespaceFk::query()->paginate(\$options['per_page'] ?? 15);\n\t\treturn [\n\t\t\t'data' => \$data->items(),\n\t\t\t'total' => \$data->total(),\n\t\t\t'page' => \$data->currentPage(),\n\t\t];\n\t}";
    }

    private function createMigration($scheme, $foreigners): void
    {
        if(!file_exists(sprintf(self::ACTION_DIR, $this->domainName, "/Migrations/".config('cdf.api_version', 'v1')."/create", "$this->migrationName.php"))){
            $this->cdfTemplateCommand->generateStubMigration($this->domainName, str_replace("create_", "", str_replace("_table", "", $this->migrationName)), $scheme, $foreigners);
            info("A migration: ($this->migrationName) foi criada com sucesso!");
        } else {
            error("A migration: ($this->migrationName) já existe!");
        }
    }

    /**
     * Analyzes the given lines to identify certain patterns.
     *
     * @param array $lines An array of lines to be analyzed.
     * @return array An associative array containing the analysis results:
     *               - 'constructorOld': A string representing the old constructor line.
     *               - 'constructorAdd': A boolean indicating whether the constructor needs to be added.
     *               - 'importAdd': A boolean indicating whether the import needs to be added.
     */
    private function analyzeLines(array $lines): array
    {
        $constructorOld = '';
        $constructorAdd = false;
        $importAdd = false;

        foreach ($lines as $line) {
            if(str_contains($line, "use Domains\\$this->domainName\\Services\\$this->serviceName;")){
                $importAdd = true;
            }

            if (str_contains($line, '__construct')) {
                $constructorOld = trim($line);
                if(str_contains($line, $this->serviceName)){
                    $constructorAdd = true;
                }
                break;
            }
        }

        return ['constructorOld' => $constructorOld,'constructorAdd' => $constructorAdd, 'importAdd' => $importAdd ];
    }

    /**
     * Adds a constructor to the given content.
     *
     * @param string $constructorOld The old constructor.
     * @param string $content The content to update.
     * @return string|array The content after adding the constructor.
     */
    private function addConstructor(string $constructorOld, string $content): array|string
    {
        $constructorReplace = ", %s $%s)";
        $constructorNew = str_replace(')', sprintf($constructorReplace, $this->serviceName, Str::camel($this->serviceName)), $constructorOld);
        return str_replace($constructorOld, $constructorNew, $content);
    }

    /**
     * Replaces specified import statements in the given content.
     *
     * @param string $content The content to replace import statements in.
     * @return array|string The content after replacing the import statements.
     */
    private function replaceImports(string $content): array|string
    {
        $importReplace = "use Domains\Shared\BLL\BaseBLL;\nuse Domains\\$this->domainName\\Services\\$this->serviceName;";
        return str_replace("use Domains\Shared\BLL\BaseBLL;", $importReplace, $content);
    }

    /**
     * @throws FileNotFoundException
     */
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

    /**
     * Retrieves the content properties of a file.
     *
     * @param string $modelDir The path of the model file.
     *
     * @return array An array containing the following properties:
     *               - bool $importAdd Indicates whether an import has been added or not.
     *               - bool $checkBelongsTo Indicates whether the file contains a belongsTo statement.
     *               - string $class The class template found in the file.
     * @throws FileNotFoundException
     */
    protected function getFileContentProperties(string $modelDir, string $modelName, $domainName, $relationship): array
    {
        $fileContent = $this->fileSystem->lines($modelDir, true);
        $importRelationsUsing = false;
        $importAdd = false;
        $checkTo = false;
        $class = '';

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
                str_contains(trim($line), sprintf($templateMethod, $relationship == 'hasMany' ? Str::plural(Str::camel($modelName)) : Str::camel($modelName), Str::studly($relationship)))) {
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

    /**
     * Updates the content of the model based on the given parameters.
     *
     * @param string $modelContent The content of the model to be updated.
     * @param bool $importAdded Indicates whether an import was added or not.
     * @param string $class The class template to be replaced in the model content.
     *
     * @return array|string The updated model content, if an import was added;
     *                     otherwise, the original model content.
     */
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
}
