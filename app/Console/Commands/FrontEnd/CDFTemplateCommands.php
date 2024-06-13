<?php

namespace App\Console\Commands\FrontEnd;

use Illuminate\Support\Pluralizer;
use Str;
use Touhidurabir\StubGenerator\Facades\StubGenerator;

class CDFTemplateCommands
{
    protected string $dirFront;
    protected string $crudName;
    protected string $storeName;
    protected string $interfaceName;
    protected string $serviceName;
    protected string $endPoint;
    protected string $attributes;
    protected array $fields;
    protected array $imports;
    protected string $header;
    protected string $searchDefault;
    protected string $terms;
    protected string $langName;
    protected array $langAttributes;
    protected array $fkService;
    protected array $fkStates;
    protected array $fkFetchs;
    protected array $fkLoadings;
    protected array $fkRefsState;
    protected array $fkMethods;
    protected array $fkInputs;

    public function __construct($dirFront)
    {
        $this->dirFront = $dirFront;

        Pluralizer::useLanguage('portuguese');
    }

    // all other constants and properties here saved as original
    // refactor to set multiple class properties in one go
    protected function setClassProperties(array $properties): void
    {
        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }

    public function generateStubCriar(string $crudName, string $storeName): void
    {
        $this->setClassProperties([
            'crudName' => $crudName,
            'storeName' => $storeName,
        ]);
        $this->generateStub("Criar");
    }

    public function generateStubEditar(string $crudName, string $storeName): void
    {
        $this->setClassProperties([
            'crudName' => $crudName,
            'storeName' => $storeName,
        ]);
        $this->generateStub("Editar");
    }

    public function generateStubForm(string $crudName, string $storeName, string $interfaceName, string $attributes, array $fields, array $imports, array $fkStates, array $fkMethods, array $fkFetchs, array $fkInputs): void
    {
        $this->setClassProperties([
            'crudName' => $crudName,
            'interfaceName' => $interfaceName,
            'storeName' => $storeName,
            'attributes' => $attributes,
            'fields' => $fields,
            'imports' => $imports,
            'fkRefsState' => $fkStates,
            'fkMethods' => $fkMethods,
            'fkFetchs' => $fkFetchs,
            'fkInputs' => $fkInputs,
        ]);
        $this->generateStub("Form");
    }

    public function generateStubListar(string $crudName, string $storeName, string $interfaceName, string $header, string $terms): void
    {
        $this->setClassProperties([
            'crudName' => $crudName,
            'interfaceName' => $interfaceName,
            'storeName' => $storeName,
            'header' => $header,
            'terms' => $terms
        ]);
        $this->generateStub("Listar");
    }

    public function generateStubTypes(string $crudName, $interfaceName, $attributes): void
    {
        $this->setClassProperties([
            'crudName' => $crudName,
            'interfaceName' => $interfaceName,
            'attributes' => $attributes
        ]);
        $this->generateStub("Types");
    }

    public function generateStubStore(string $crudName, $storeName, $serviceName, $interfaceName, $searchDefault, $attributes, $fkStates, $fkFetchs, $fkLoadings): void
    {
        $this->setClassProperties([
            'crudName' => $crudName,
            'storeName' => $storeName,
            'serviceName' => $serviceName,
            'interfaceName' => $interfaceName,
            'searchDefault' => $searchDefault,
            'attributes' => $attributes,
            'fkStates' => $fkStates,
            'fkFetchs' => $fkFetchs,
            'fkLoadings' => $fkLoadings,
        ]);
        $this->generateStub("Store");
    }

    public function generateStubService(string $crudName, string $serviceName, string $endPoint, array $fkService): void
    {
        $this->setClassProperties([
            'crudName' => $crudName,
            'serviceName' => $serviceName,
            'endPoint' => $endPoint,
            'fkService' => $fkService
        ]);
        $this->generateStub("Service");
    }

    public function generateStubLang(string $langName, array $langAttributes): void
    {
        $this->setClassProperties([
            'langName' => $langName,
            'langAttributes' => $langAttributes
        ]);
        $this->generateStub("Locales");
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
            ->ext($template['ext'])
            ->withReplacers($template['stubVariables'])
            ->save();
    }

    public function createCol(string $field)
    {
        return "\t <VCol cols=\"12\" md=\"4\">\n\t\t\t\t\t\t $field \t\t\t\t\t\t</VCol>";
    }

    public function createStubInputs($crudName, $filed, $type, $rulesDefault = []){
        match ($type){
            default => ''
        };

        $rules = implode(',', $rulesDefault);

        return match ($type)
        {
            'currency' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/currency.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "placeholder" => strtolower($crudName).".form.".strtolower($filed).".placeholder",
                    "rules" => $rules
                ])->toString(),
            'input' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/input.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "placeholder" => strtolower($crudName).".form.".strtolower($filed).".placeholder",
                    "rules" => $rules
                ])->toString(),
            'cnpj' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/cnpj.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "placeholder" => strtolower($crudName).".form.".strtolower($filed).".placeholder",
                    "rules" => $rules
                ])->toString(),
            'cpf' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/cpf.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "placeholder" => strtolower($crudName).".form.".strtolower($filed).".placeholder",
                    "rules" => $rules
                ])->toString(),
            'telefone' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/telefone.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "placeholder" => strtolower($crudName).".form.".strtolower($filed).".placeholder",
                    "rules" => $rules
                ])->toString(),
            'celular' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/celular.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "placeholder" => strtolower($crudName).".form.".strtolower($filed).".placeholder",
                    "rules" => $rules
                ])->toString(),
            'textarea' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/textarea.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "placeholder" => strtolower($crudName).".form.".strtolower($filed).".placeholder",
                    "rules" => $rules
                ])->toString(),
            'date' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/date.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "placeholder" => strtolower($crudName).".form.".strtolower($filed).".placeholder",
                    "rules" => $rules
                ])->toString(),
            'autocomplete' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/autocomplete.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "placeholder" => strtolower($crudName).".form.".strtolower($filed).".placeholder",
                    "rules" => $rules
                ])->toString(),
            'checkbox' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/checkbox.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "rules" => $rules
                ])->toString(),
            'fileinput' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/fileinput.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "id_imagem" => strtolower($filed),
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "rules" => $rules
                ])->toString(),
            'imageinput' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/imageinput.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "placeholder" => strtolower($crudName).".form.".strtolower($filed).".placeholder",
                    "rules" => $rules
                ])->toString(),
            'switch' => StubGenerator::from("../app/Domains/Shared/Stubs/FrontEnd/Fields/switch.frontend.stub")
                ->withReplacers([
                    "vModel" => "data.$filed",
                    "label" => strtolower($crudName).".form.".strtolower($filed).".label",
                    "rules" => $rules
                ])->toString(),
            default => ''
        };
    }

    public function createStubHeaderItem($crudName, $filed, $type)
    {
        return "\n\t{\n\t\ttitle: t('".strtolower($crudName).".headers.".strtolower($filed)."'),\n\t\tkey: '$filed'\n\t}";
    }

    public function createStubTermsItem($crudName, $filed)
    {
        return "\n\t{\n\t\ttitle: t('".strtolower($crudName).".terms.".strtolower($filed)."'),\n\t\tvalue: '".strtolower($filed)."'\n\t}";
    }

    public function createStubHeader($content)
    {
        $content = implode(",\n\t" ,$content);
        return "[$content]";
    }

    public function createStubTerms($content)
    {
        $content = implode(",\n\t" ,$content);
        return "[$content]";
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
            'Criar' => [
                'actionDir' => sprintf('%s\%s\%s\%s', $this->dirFront, 'pages', Str::lower($this->crudName), 'cadastrar'),
                'fileName' => 'index',
                'ext' => "vue",
                'stubPath' => "../app/Domains/Shared/Stubs/FrontEnd/cdf.frontend.cadastrar.stub",
                'stubVariables' => [
                    'import_formulario' => 'form-'.strtolower($this->crudName),
                    'store_name' => $this->storeName,
                    'entity_var' => ucwords(Str::singular($this->crudName)),
                    'entity_singular_var' => strtolower(Str::singular($this->crudName)),
                ]
            ],
            'Editar' => [
                'actionDir' => sprintf('%s\%s\%s\%s', $this->dirFront, 'pages', Str::lower($this->crudName), 'editar'),
                'fileName' => "[id]",
                'ext' => "vue",
                'stubPath' => "../app/Domains/Shared/Stubs/FrontEnd/cdf.frontend.editar.stub",
                'stubVariables' => [
                    'import_formulario' => 'form-'.strtolower(Str::singular($this->crudName)),
                    'store_name' => $this->storeName,
                    'entity_var' => ucwords(Str::singular($this->crudName)),
                    'entity_singular_var' => strtolower(Str::singular($this->crudName)),
                ]
            ],
            'Form' => [
                'actionDir' => sprintf('%s\%s\%s\%s', $this->dirFront, 'pages', Str::lower($this->crudName), 'components'),
                'fileName' => "form-".strtolower($this->crudName),
                'ext' => "vue",
                'stubPath' => "../app/Domains/Shared/Stubs/FrontEnd/cdf.frontend.form.stub",
                'stubVariables' => [
                    'store_name' => $this->storeName,
                    'interface_name' => $this->interfaceName,
                    'entity_singular_var' => strtolower($this->crudName),
                    'fields' => implode("\n\t\t\t\t\t\t", $this->fields),
                    'imports' => implode("\n", $this->imports),
                    'fk_refs_state' => implode("\n", $this->fkRefsState),
                    'fk_methods' => implode("\n", $this->fkMethods),
                    'methods_fetchs' => implode("\n", $this->fkFetchs),
                    'fk_inputs' => implode("\n", $this->fkInputs)
                ]
            ],
            'Listar' => [
                'actionDir' => sprintf('%s\%s\%s', $this->dirFront, 'pages', Str::lower($this->crudName)),
                'fileName' => "index",
                'ext' => 'vue',
                'stubPath' => "../app/Domains/Shared/Stubs/FrontEnd/cdf.frontend.listar.stub",
                'stubVariables' => [
                    'interface_name' => $this->interfaceName,
                    'store_name' => $this->storeName,
                    'crud_name' => Str::ucfirst($this->crudName),
                    'router_cadastrar' => "/".Str::lower($this->crudName)."/cadastrar",
                    'router_edit' => "/".Str::lower($this->crudName)."/editar/\${item.id}",
                    'entity_singular_var' => strtolower(Str::singular($this->crudName)),
                    'header' => $this->header,
                    'terms' => $this->terms,
                ]
            ],
            'Types' => [
                'actionDir' => sprintf('%s\%s\%s', $this->dirFront, 'pages', Str::lower($this->crudName)),
                'fileName' => "types",
                'ext' => "ts",
                'stubPath' => "../app/Domains/Shared/Stubs/FrontEnd/cdf.frontend.types.stub",
                'stubVariables' => [
                    'interface_name' => $this->interfaceName,
                    'attributes' => $this->attributes,
                ]
            ],
            'Store' => [
                'actionDir' => sprintf('%s\%s\%s\%s', $this->dirFront, 'pages', Str::lower($this->crudName), 'store'),
                'fileName' => "$this->storeName",
                'ext' => "ts",
                'stubPath' => "../app/Domains/Shared/Stubs/FrontEnd/cdf.frontend.store.stub",
                'stubVariables' => [
                    'store_name' => $this->storeName,
                    'service_name' => $this->serviceName,
                    'interface_name' => $this->interfaceName,
                    'crud_name' => $this->crudName,
                    'crud_var' => strtolower($this->crudName),
                    'entity_plural_var' => strtolower(Str::plural($this->crudName)),
                    'entity_singular_var' => strtolower(Str::singular($this->crudName)),
                    'attributes' => $this->attributes,
                    'orderKeyDefault' => $this->searchDefault,
                    'fk_fetchs' => implode("\n", $this->fkFetchs) ?? '',
                    'fk_states' => implode("\n", $this->fkStates) ?? '',
                    'fk_loadings' => implode("\n", $this->fkLoadings) ?? '',
                ]
            ],
            'Service' => [
                'actionDir' => sprintf('%s\%s\%s\%s', $this->dirFront, 'pages', Str::lower($this->crudName), 'services'),
                'fileName' => "$this->serviceName",
                'ext' => "ts",
                'stubPath' => "../app/Domains/Shared/Stubs/FrontEnd/cdf.frontend.service.stub",
                'stubVariables' => [
                    'service_name' => $this->serviceName,
                    'end_point' => $this->endPoint,
                    'methods_fk' => implode("\n", $this->fkService) ?? ''
                ]
            ],
            'Locales' => [
                'actionDir' => sprintf('%s\%s\%s\%s', $this->dirFront, 'pages', Str::lower($this->crudName), 'locales'),
                'fileName' => "$this->langName",
                'ext' => "json",
                'stubPath' => "../app/Domains/Shared/Stubs/FrontEnd/cdf.frontend.lang.stub",
                'stubVariables' => [
                    'content' => json_encode($this->langAttributes, JSON_PRETTY_PRINT)
                ]
            ],
            default => []
        };
    }
}
