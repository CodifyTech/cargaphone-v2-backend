<?php

namespace App\Console\Commands\BackEnd;

use Exception;
use Illuminate\Console\Command;
use Str;
use function Laravel\Prompts\error;
use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;

/**
 * Class BLLCommand
 *
 * This class represents a command to create a Domain Name, BLL, and Service.
 *
 * @package App\Console\Commands
 */
class BLLCommand extends Command
{
    protected $signature = 'cdf:bll {domainName?} {bllName?} {serviceName?}';

    protected $description = 'Criar uma BLL';

    protected string $domainName;
    protected string $bllName;
    protected string $serviceName;
    private CDFTemplateCommands $cdfTemplateCommands;

    /**
     * Class constructor.
     *
     * @param CDFTemplateCommands $cdfTemplateCommands The CDFTemplateCommands instance.
     *
     * @return void
     */
    public function __construct(CDFTemplateCommands $cdfTemplateCommands)
    {
        parent::__construct();

        $this->cdfTemplateCommands = $cdfTemplateCommands;
    }

    /**
     * Handle method to create a Domain Name, BLL, and Service.
     *
     * @return void
     */
    public function handle(): void
    {
        $domainsDir = [];
        foreach(collect(scandir(app_path()."/Domains")) as $dir){
            if(!in_array($dir, array(".", "..", "Auth", "Shared"))){
                $domainsDir[] = $dir;
            }
        }

        $this->domainName = select(
            label: 'Selecione um domain?',
            options: $domainsDir,
        );
        $this->bllName = text(
            label: 'Informe o nome da BLL?',
            default: $this?->argument('bllName') ?? '',
            required: true
        );
        $this->serviceName = text(
            label: 'Informe o nome da Service?',
            default: $this?->argument('serviceName') ?? "{$this->bllName}Service",
            required: true
        );

        $this->bllName = Str::endsWith($this->bllName, 'BLL') ? "$this->bllName" : "{$this->bllName}BLL";
        $this->serviceName = Str::endsWith($this->serviceName, 'Service') ? "$this->serviceName" : "{$this->serviceName}Service";

        $dirDomainName = app_path()."\Domains\\$this->domainName";
        $dirBLLName = __DIR__."/../../Domains/$this->domainName/BLL/$this->bllName.php";

        if(file_exists($dirDomainName))
        {
            if(!file_exists($dirBLLName)){
                try {
                    info("Crianda a BLL: ($this->bllName)");
                    $this->cdfTemplateCommands->generateStubBLL($this->domainName, $this->bllName, $this->serviceName);
                    info("A BLL: ($this->bllName) foi criada com sucesso!");
                } catch (Exception $exception){
                    error($exception->getMessage());
                }

                if (confirm('Você deseja criar um Service?')) {
                    $this->call('cdf:service', [
                        'domainName' => $this->domainName,
                        'serviceName' => $this->serviceName
                    ]);
                }
            } else {
                error("A BLL: ($this->bllName) já existe!");
            }
        }
        else
        {
            error("O domain: ($this->domainName) não existe");
        }
    }
}
