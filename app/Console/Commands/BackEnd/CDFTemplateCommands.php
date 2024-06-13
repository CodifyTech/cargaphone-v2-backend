<?php

namespace App\Console\Commands\BackEnd;

use Carbon\Carbon;
use Str;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

class CDFTemplateCommands
{
    const NAMESPACE = 'Domains\\%s\\%s';

    const ACTION_DIR = __DIR__."/../../../Domains/%s/%s/";

    protected string $domainClass;
    protected string $controllerClass;
    protected string $serviceClass;
    protected string $bllClass;
    protected string $requestClass;
    protected string $modelClass;
    protected string $migrationClass;
    protected string $resourceClass;
    protected string $seederClass;
    protected array $scheme;
    protected array $fillable;
    protected array $foreign;
    protected array $requestAttributes;
    protected array $fkMethods;

    // all other constants and properties here saved as original
    // refactor to set multiple class properties in one go
    protected function setClassProperties(array $properties): void
    {
        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * @param string $domainClass Nome da Domain
     * @param string $bllClass Nome da BLL
     * @param string $serviceClass Nome do Service
     * @return void
     */
    public function generateStubBLL(string $domainClass, string $bllClass, string $serviceClass, array $fkMethods): void
    {
        $this->setClassProperties([
            'domainClass' => $domainClass,
            'bllClass' => $bllClass,
            'serviceClass' => $serviceClass,
            'fkMethods' => $fkMethods
        ]);

        $this->generateStub("BLL");
    }

    /**
     * @param string $domainClass Nome da Domain
     * @param string $controllerClass Nome da Controller
     * @param string $bllClass
     * @param string $requestClass Nome da Reques
     * @return void
     */
    public function generateStubController(string $domainClass, string $controllerClass, string $bllClass, string $requestClass, array $fkMethods): void
    {
        $this->setClassProperties([
            'domainClass' => $domainClass,
            'controllerClass' => $controllerClass,
            'bllClass' => $bllClass,
            'requestClass' => $requestClass,
            'fkMethods' => $fkMethods
        ]);

        $this->generateStub("Controllers");
    }


    /**
     * Generate stub model based on the given domain class, model class, and fillable properties.
     *
     * @param string $domainClass The domain class to generate the stub model for.
     * @param string $modelClass The model class name.
     * @param array $fillable The fillable properties of the model.
     * @param string $migrationClass
     * @return void
     */
    public function generateStubModel(string $domainClass, string $modelClass, array $fillable, string $migrationClass): void
    {
        $this->setClassProperties([
            'domainClass' => $domainClass,
            'modelClass' => $modelClass,
            'migrationClass' => $migrationClass,
            'fillable' => $fillable,
        ]);

        $this->generateStub("Models");
    }

    /**
     * Generates a stub migration file based on the provided parameters.
     *
     * @param string $domainClass The class name of the domain model.
     * @param string $migrationClass The class name of the migration file.
     * @param array $scheme The array representation of the migration schema.
     * @param array $foreign An optional array of foreign keys for the migration.
     * @return void
     */
    public function generateStubMigration(string $domainClass, string $migrationClass, array $scheme, array $foreign = []): void
    {
        $this->setClassProperties([
            'domainClass' => $domainClass,
            'migrationClass' => $migrationClass,
            'scheme' => $scheme,
            'foreign' => $foreign
        ]);

        $this->generateStub("Migrations");
    }

    /**
     * Generates a stub request for a given domain class and request class.
     *
     * @param string $domainClass The fully qualified namespace of the domain class.
     * @param string $requestClass The fully qualified namespace of the request class.
     * @param array $requestAttributes
     * @return void
     */
    public function generateStubRequest(string $domainClass, string $requestClass, array $requestAttributes): void
    {
        $this->setClassProperties([
            'domainClass' => $domainClass,
            'requestClass' => $requestClass,
            'requestAttributes' => $requestAttributes
        ]);

        $this->generateStub("Requests");
    }

    /**
     * Generates a stub resource for the given domain class and resource class.
     *
     * @param string $domainClass The fully qualified name of the domain class.
     * @param string $resourceClass The fully qualified name of the resource class.
     *
     * @return void
     */
    public function generateStubResource(string $domainClass, string $resourceClass): void
    {
        $this->setClassProperties([
            'domainClass' => $domainClass,
            'resourceClass' => $resourceClass
        ]);

        $this->generateStub("Resources");
    }

    /**
     * Generate stub seeders.
     *
     * @param string $domainClass The domain class.
     * @param string $seederClass The seeder class.
     *
     * @return void
     */
    public function generateStubSeeders(string $domainClass, string $seederClass): void
    {
        $this->setClassProperties([
            'domainClass' => $domainClass,
            'seederClass' => $seederClass
        ]);
        $this->generateStub("Seeders");
    }

    /**
     * Generate stub services.
     *
     * This method generates stub services by setting class properties with the provided domain class,
     * service class, and model class, and then generating the stub services using the generateStub method.
     *
     * @param string $domainClass The domain class to use for generating stub services.
     * @param string $serviceClass The service class to use for generating stub services.
     * @param string $modelClass The model class to use for generating stub services.
     *
     * @return void
     */
    public function generateStubServices(string $domainClass, string $serviceClass, string $modelClass, array $fkMethods): void
    {
        $this->setClassProperties([
            'domainClass' => $domainClass,
            'serviceClass' => $serviceClass,
            'modelClass' => $modelClass,
            'fkMethods' => $fkMethods
        ]);
        $this->generateStub("Services");
    }

    /**
     * Generate a stub for the given action.
     *
     * @param string $action The action for which to generate the stub.
     * @return void
     */
    protected function generateStub(string $action): void
    {
        $template = $this->getStubVariables($action);

        $this->createStubFromTemplate($template);
    }

    /**
     * Generates a stub file from a given template.
     *
     * @param array $template The template to generate the stub from.
     *                       The template should have the following keys:
     *                       - 'stubPath' : string, the path to the template stub file.
     *                       - 'actionDir' : string, the directory where the generated stub file will be saved.
     *                       - 'fileName' : string, the name of the generated stub file.
     *                       - 'stubVariables' : array, an associative array of variables to replace in the template stub file.
     *
     * @return void
     */
    protected function createStubFromTemplate(array $template): void
    {
        StubGenerator::from($template['stubPath'])
            ->to($template['actionDir'], false, true)
            ->as($template['fileName'])
            ->ext('php')
            ->withReplacers($template['stubVariables'])
            ->save();
    }

    /**
     * Returns an array of variables based on the provided action.
     *
     * @param string $action The action for which the variables are needed.
     * @return array The array of variables based on the action.
     */
    protected function getStubVariables(string $action): array
    {
        return match ($action){
            'BLL' => [
                'actionDir' => sprintf(self::ACTION_DIR, $this->domainClass, "BLL"),
                'fileName' => "$this->bllClass",
                'stubPath' => "../app/Domains/Shared/Stubs/BackEnd/cdf.bll.stub",
                'stubVariables' => [
                    'namespace' => sprintf(self::NAMESPACE, $this->domainClass, "BLL"),
                    'domain_name' => $this->domainClass,
                    'service_name' => $this->serviceClass,
                    'service_var' => Str::camel($this->serviceClass),
                    'bll_name' => $this->bllClass,
                    'methods_fk' => implode("\n", $this->fkMethods) ?? ''
                ]
            ],

            'Controllers' => [
                'actionDir' => sprintf(self::ACTION_DIR, $this->domainClass, "Controllers"),
                'fileName' => "$this->controllerClass",
                'stubPath' => "../app/Domains/Shared/Stubs/BackEnd/cdf.controller.stub",
                'stubVariables' => [
                    'namespace' => sprintf(self::NAMESPACE, $this->domainClass, "Controllers"),
                    'domain_name' => $this->domainClass,
                    'controller_name' => $this->controllerClass,
                    'bll_name' => $this->bllClass,
                    'bll_var' => Str::camel($this->bllClass),
                    'request_name' => $this->requestClass,
                    'methods_fk' => implode("\n", $this->fkMethods) ?? ''
                ]
            ],

            'Emails' => [],

            'Enums' => [],

            'Exceptions' => [],

            'Migrations' => [
                'actionDir' => sprintf(self::ACTION_DIR, $this->domainClass, "Migrations/".config('cdf.api_version', 'v1')."/create"),
                'fileName' => Carbon::now()->format('Y_m_d_His')."_create_".Str::plural(Str::lower($this->migrationClass))."_table",
                'stubPath' => count($this->foreign) > 0 ? "../app/Domains/Shared/Stubs/BackEnd/cdf.migration.foreign.stub" : "../app/Domains/Shared/Stubs/BackEnd/cdf.migration.stub",
                'stubVariables' => [
                    'table' => Str::plural(Str::lower($this->migrationClass)),
                    'entry' => implode("\n\t\t\t", $this->scheme),
                    'foreign' => implode("\n\t\t\t", $this->foreign)
                ]
            ],

            'Models' => [
                'actionDir' => sprintf(self::ACTION_DIR, $this->domainClass, "Models"),
                'fileName' => "$this->modelClass",
                'stubPath' => "../app/Domains/Shared/Stubs/BackEnd/cdf.model.stub",
                'stubVariables' => [
                    'namespace' => sprintf(self::NAMESPACE, $this->domainClass, "Models"),
                    'model_name' => $this->modelClass,
                    'table_name' => Str::plural(Str::lower($this->migrationClass)),
                    'fillable' => implode("\n\t\t", $this->fillable)
                ],
            ],

            'Policies' => [],

            'Requests' => [
                'actionDir' => sprintf(self::ACTION_DIR, $this->domainClass, "Requests"),
                'fileName' => "$this->requestClass",
                'stubPath' => "../app/Domains/Shared/Stubs/BackEnd/cdf.request.stub",
                'stubVariables' => [
                    'namespace' => sprintf(self::NAMESPACE, $this->domainClass, "Requests"),
                    'request_name' => $this->requestClass,
                    'requestAttributes' => implode(",\n", $this->requestAttributes)
                ]
            ],

            'Resources' => [
                'actionDir' => sprintf(self::ACTION_DIR, $this->domainClass, "Resources"),
                'fileName' => "$this->resourceClass",
                'stubPath' => "../app/Domains/Shared/Stubs/BackEnd/cdf.resource.stub",
                'stubVariables' => [
                    'namespace' => sprintf(self::NAMESPACE, $this->domainClass, "Resources"),
                    'resource_name' => $this->resourceClass,
                ]
            ],

            'Rules' => [],

            'Seeders' => [
                'actionDir' => sprintf(self::ACTION_DIR, $this->domainClass, "Seeders"),
                'fileName' => "$this->seederClass",
                'stubPath' => "../app/Domains/Shared/Stubs/BackEnd/cdf.seeder.stub",
                'stubVariables' => [
                    'namespace' => sprintf(self::NAMESPACE, $this->domainClass, "Seeders"),
                    'seeder_name' => $this->seederClass,
                ]
            ],

            'Services' => [
                'actionDir' => sprintf(self::ACTION_DIR, $this->domainClass, "Services"),
                'fileName' => "$this->serviceClass",
                'stubPath' => "../app/Domains/Shared/Stubs/BackEnd/cdf.service.stub",
                'stubVariables' => [
                    'namespace' => sprintf(self::NAMESPACE, $this->domainClass, "Services"),
                    'domain_name' => $this->domainClass,
                    'service_name' => $this->serviceClass,
                    'model_name' => $this->modelClass,
                    'model_var' => Str::camel($this->modelClass),
                    'methods_fk' => implode("\n", $this->fkMethods) ?? ''
                ]
            ],

            default => []
        };
    }
}
