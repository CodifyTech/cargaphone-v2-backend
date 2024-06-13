<?php

namespace App\Console\Commands\BackEnd;

use Exception;
use Illuminate\Console\Command;
use Str;

use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;

/**
 * Class ControllerCommand
 *
 * A Command class for creating a controller.
 */
class ControllerCommand extends Command
{
    protected $signature = 'cdf:controller';

    protected $description = 'Criar uma controller';

    protected string $domainName;
    protected string $controllerName;
    protected string $bllName;
    protected string $requestName;
    protected string $serviceName;
    private CDFTemplateCommands $cdfTemplateCommand;

    const ACTION_DIR = __DIR__."/../../../Domains/%s/%s/%s";

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
        );

        $this->controllerName = text(
            label: 'Controller',
            placeholder: 'Digite o nome da Controller',
            required: true
        );

        $this->bllName = text(
            label: 'BLL',
            placeholder: 'Digite o nome da BLL',
            default: "{$this->controllerName}BLL",
            required: true
        );

        $this->requestName = text(
            label: 'Request',
            placeholder: 'Digite o nome da Request',
            default: "{$this->controllerName}Request",
            required: true
        );

        $dirDomainName = app_path()."\Domains\\$this->domainName";

        $this->bllName = Str::endsWith($this->controllerName, 'BLL') ? "$this->controllerName" : "{$this->controllerName}BLL";
        $this->requestName = Str::endsWith($this->controllerName, 'Request') ? "$this->controllerName" : "{$this->controllerName}Request";
        $this->controllerName = Str::endsWith($this->controllerName, 'Controller') ? "$this->controllerName" : "{$this->controllerName}Controller";

        if(file_exists($dirDomainName))
        {
            if(!file_exists(__DIR__."/../../Domains/$this->domainName/Controllers/{$this->controllerName}.php")){
                try {
                    info("Criando Controller: ($this->controllerName)");
                    $this->cdfTemplateCommand->generateStubController($this->domainName, $this->controllerName, $this->bllName, $this->requestName);
                } catch (Exception $e){
                    error($e->getMessage());
                }

                info("A controller: ($this->controllerName) foi criado com sucesso!");

                if (confirm('Você deseja criar uma BLL?')) {
                    $this->call('cdf:bll', [
                        'domainName' => $this->domainName,
                        'bllName' => $this->bllName
                    ]);
                }
            } else {
                error("A controller: $this->controllerName já existe!");
            }
        }else {
            error("O domain ($this->domainName) não existe!");
        }
    }
}
