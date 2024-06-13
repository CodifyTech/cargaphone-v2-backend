<?php

namespace App\Console\Commands\BackEnd;

use Illuminate\Console\Command;
use Str;

use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;


class MigrationCommand extends Command
{
    protected $signature = 'cdf:migration {domainName?} {table?} {scheme?}';

    protected $description = 'Criar uma migration';

    protected $domainName;

    protected $table;

    protected $scheme;

    protected array $columns;

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
            default: $this?->argument('domainName') ?? '',
        );

        $this->table = text(
            label: 'Migration',
            placeholder: 'Digite o nome da Migration',
            default: $this?->argument('table') ?? '',
            required: 'Por favor, digite o nome da Migration',
        );

        $this->scheme = text(
            label: 'Schema da Migration | ex: [title=string,100;price=float]',
            placeholder: 'Digite o Schema da Migration',
            required: 'Por favor, digite o schema.',
            default: $this?->argument('scheme') ?? '',
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

        $dirName = app_path()."\Domains\\$this->domainName";
        $migrationDir = __DIR__ . "/../../Domains/$this->domainName/Migrations/".config('cdf.api_version')."/create/create_" . Str::plural(Str::lower($this->table)) . "_table.php";

        if(file_exists($dirName)) {
            $columns = explode(';', rtrim($this->scheme, ';'));
            foreach ($columns as $column) {
                @list($table, $params) = explode('=', $column);
                @list($type, $something) = explode(',', $params ?? '');

                $builder = new Migration($table, $type ?? null, $something ?? null);

                $this->columns[] = $builder->builderColumn();
            }

            if(!file_exists(app_path()."\Domains\\$this->domainName\\Migrations\\".config('cdf.api_version')."\\create")) {
                mkdir(app_path()."\Domains\\$this->domainName\\Migrations\\".config('cdf.api_version')."\\create", 0700, true);
            }

            if(!file_exists(app_path()."\Domains\\$this->domainName\\Migrations\\".config('cdf.api_version')."\\alter")) {
                mkdir(app_path()."\Domains\\$this->domainName\\Migrations\\".config('cdf.api_version')."\\alter", 0700, true);
            }

            if(!file_exists($migrationDir)){
                info("Criando migration: $this->table");
                $this->cdfTemplateCommands->generateStubMigration($this->domainName, $this->table, $this->columns);
                info("A migration: ($this->table) foi criada com sucesso!");
            } else {
                error("A migration: ($this->table) já existe!");
            }
        } else {
            error("O domain: ($this->domainName) não existe!");
        }
    }
}

