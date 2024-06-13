<?php

namespace App\Console\Commands\BackEnd;

use Illuminate\Console\Command;
use Str;

use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;

class RequestCommand extends Command
{
    protected $signature = 'cdf:request {domainName?} {requestName?}';

    protected $description = 'Criar um Request';

    protected string $domainName;
    protected string $requestName;
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

        $this->requestName = text(
            label: 'Service',
            placeholder: 'Digite o nome do Request',
            default: $this?->argument('requestName'),
            required: 'Por favor, digite o nome do Request',
        );

        $this->requestName = Str::endsWith($this->requestName, 'Request') ? "$this->requestName" : "{$this->requestName}Request";

        $dirDomainName = app_path()."\Domains\\$this->domainName";
        $requestDir = __DIR__."/../../Domains/$this->domainName/Request/$this->requestName.php";

        if(file_exists($dirDomainName))
        {
            if(!file_exists($requestDir)){
                $this->cdfTemplateCommand->generateStubRequest($this->domainName, $this->requestName);
                info("O request: ($this->requestName) foi criado com sucesso!");
            } else{
                error("O request: ($this->requestName) já existe!");
            }
        } else {
            error("O domain ($this->domainName) não existe");
        }
    }
}
