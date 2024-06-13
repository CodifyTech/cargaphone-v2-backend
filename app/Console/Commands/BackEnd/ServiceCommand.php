<?php

namespace App\Console\Commands\BackEnd;

use Illuminate\Console\Command;
use Str;

use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\error;

/**
 * Class ServiceCommand
 *
 * Esta classe representa o comando "cdf:service" que cria um serviço.
 */
class ServiceCommand extends Command
{
    protected $signature = 'cdf:service {domainName?} {serviceName?} {modelName?}';

    protected $description = 'Criar um Service';

    protected string $domainName;
    protected string $serviceName;
    protected string $modelName;
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

        $this->serviceName = text(
            label: 'Service',
            placeholder: 'Digite o nome da Service',
            default: $this?->argument('serviceName'),
            required: 'Por favor, digite o nome da service',
        );

        $this->modelName = text(
            label: 'Model',
            placeholder: 'Digite o nome da Model',
            default: $this?->argument('modelName'),
            required: 'Por favor, digite o nome da Model',
        );

        $this->serviceName = Str::endsWith($this->serviceName, 'Service') ? "$this->serviceName" : "{$this->serviceName}Service";

        $dirDomainName = app_path()."\Domains\\$this->domainName";
        $serviceDir = __DIR__."/../../Domains/$this->domainName/Services/{$this->serviceName}.php";

        if(file_exists($dirDomainName))
        {
            if(!file_exists($serviceDir)){
                $this->cdfTemplateCommand->generateStubServices($this->domainName, $this->serviceName, $this->modelName);
                info("A service: ($this->serviceName) foi criada com sucesso!");

                if ($this->confirm('Deseja criar uma Model?')) {
                    $this->call('cdf:model', [
                        'domainName' => $this->domainName,
                        'modelName' => $this->modelName
                    ]);
                }
            } else {
                error("A service: ($this->serviceName) já existe!");
            }
        }
        else
        {
            error("O domain: ($this->domainName) não existe");
        }
    }
}
