<?php

namespace App\Console\Commands\BackEnd;

use Illuminate\Console\Command;

use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\error;

class ModelCommand extends Command
{
    protected $signature = 'cdf:model {domainName?} {modelName?} {scheme?}';

    protected $description = 'Criar um model';

    protected string $domainName;
    protected string $modelName;
    protected string $scheme;
    private CDFTemplateCommands $cdfTemplateCommands;

    public function __construct(CDFTemplateCommands $cdfTemplateCommands)
    {
        parent::__construct();

        $this->cdfTemplateCommands = $cdfTemplateCommands;
    }

    public function handle(): void
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
            default: $this?->argument('domainName')
        );

        $this->modelName = text(
            label: 'Model',
            placeholder: 'Digite o nome da Model',
            default: $this?->argument('modelName'),
            required: 'Por favor, digite o nome da Model',
        );

        $this->scheme = text(
            label: 'Schema da Migration | ex: [title=string,100;price=float]',
            placeholder: 'Digite o Schema da Migration',
            required: 'Por favor, digite o schema.',
            default: $this?->argument('scheme'),
            validate: function($value) {
                $columns = explode(';', rtrim($value, ';'));
                foreach ($columns as $column) {
                    @list($table, $params) = explode('=', $column);
                    @list($type, $something) = explode(',', $params ?? '');

                    if(!$table || !$type){
                        return 'Por favor, digite o schema corretamente.';
                    }
                }
                return true;
            }
        );

        $dirDomainName = app_path()."\Domains\\$this->domainName";
        $modelDir = __DIR__."/../../Domains/$this->domainName/Models/$this->modelName.php";

        if(file_exists($dirDomainName))
        {
            if(!file_exists($modelDir)){
                $fillable = [];
                $columns = explode(';', $this->scheme);
                foreach ($columns as $item) {
                    list($column, $_) = explode('=', $item);
                    $fillable[] = "'$column',";
                }

                $this->cdfTemplateCommands->generateStubModel($this->domainName, $this->modelName, $fillable);

                info("A model: ({$this->modelName}) foi criada com sucesso!");

                if ($this->confirm('Deseja criar uma Migration?')) {
                    $this->call('cdf:migration', [
                        'domainName' => $this->domainName,
                        'table' => $this->modelName,
                        'scheme' => $this->scheme
                    ]);
                }
            } else {
                error("A model: ($this->modelName) já existe!");
            }
        }
        else
        {
            error("O diretório: ($this->domainName) não existe");
        }
    }
}
