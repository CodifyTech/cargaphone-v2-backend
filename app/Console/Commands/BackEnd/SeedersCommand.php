<?php

namespace App\Console\Commands\BackEnd;

use Illuminate\Console\Command;
use Str;

use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;

class SeedersCommand extends Command
{
    protected $signature = 'cdf:seeders {domainName?} {seederName?}';

    protected $description = 'Criar um seeder';

    protected string $domainName;
    protected string $seederName;
    protected CDFTemplateCommands $cdfTemplateCommand;

    public function __construct(CDFTemplateCommands $cdfTemplateCommand)
    {
        parent::__construct();
        $this->cdfTemplateCommand = $cdfTemplateCommand;
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
        $this->seederName = text(
            label: 'Seeder',
            placeholder: 'Digite o nome do Seeder',
            default: $this?->argument('seederName'),
            required: 'Por favor, digite o nome do Seeder',
        );

        $this->seederName = Str::endsWith($this->seederName, 'Seeder') ? "$this->seederName" : "{$this->seederName}Seeder";

        $dirDomainName = app_path()."\Domains\\$this->domainName";

        if(file_exists($dirDomainName))
        {
            if(!file_exists(__DIR__."/../../Domains/$this->domainName/Seeders/{$this->seederName}.php")){
                $this->cdfTemplateCommand->generateStubSeeders($this->domainName, $this->seederName);
                info("O Seeders: ($this->seederName) foi criada com sucesso!");
            } else{
                error("O Seeders: ($this->seederName) já existe!");
            }
        } else {
            error("O domain: ($this->domainName) não existe");
        }
    }
}
