<?php

namespace App\Console\Commands\FrontEnd;

use App\Console\Commands\Input as InputBase;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

use function Laravel\Prompts\select;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\error;

class CrudCommand extends Command
{
    protected $signature = 'cdf:frontend {crudName?} {attributes?} {attributesDefault?} {rules?} {foreigners?}';

    protected $description = 'Command description';

    private CDFTemplateCommands $cdfTemplateCommand;
    protected InputBase $command;
    private Filesystem $fileSystem;
    private string $dirFront;

    private array $imports = [];
    private array $headersItems = [];
    private array $termsItems = [];
    private string $searchDefault = '';

    public function __construct(InputBase $command)
    {
        parent::__construct();

        $basePath = base_path();
        $frontEndDir = env('DIR_FRONT_END');
        $this->dirFront = realpath(dirname($basePath) . DIRECTORY_SEPARATOR . $frontEndDir);

        $this->cdfTemplateCommand = new CDFTemplateCommands($this->dirFront);
        $this->command = $command;
        $this->fileSystem = new Filesystem();
    }

    public function parseFieldsAndTypes($inputString) {
        $lines = explode("\n", $inputString);
        $result = [];

        foreach ($lines as $line) {
            // Remover espaços em branco no início e no final da linha
            $line = trim($line);

            // Ignorar linhas em branco
            if ($line === "") {
                continue;
            }

            // Dividir a linha em dois usando ":"
            $parts = explode(":", $line, 2);

            // Remover espaços em branco nos lados dos campos e tipos
            $field = trim($parts[0]);
            $type = trim($parts[1]);

            // Adicionar ao resultado
            $result[$field] = $type;
        }

        return $result;
    }

    private function addToArrayInTsFile($filePath, $newValue) {
        $fs = new Filesystem();

        if (!$fs->exists($filePath)) {
            throw new \Exception("File does not exist");
        }

        $content = file_get_contents($filePath);

        // Encontrar a posição da definição do array userSubjects
        $posicaoUserSubjects = strpos($content, 'const userSubjects = [');

        if ($posicaoUserSubjects !== false) {
            // Encontrar a posição do fim do array userSubjects
            $posicaoFimUserSubjects = strpos($content, ']', $posicaoUserSubjects);

            if ($posicaoFimUserSubjects !== false) {
                // Verificar se o novo valor já existe no array
                if (strpos($content, "'{$newValue}'", $posicaoUserSubjects) === false) {
                    // Inserir o novo valor antes do fim do array userSubjects
                    $novoConteudoTS = substr_replace($content, ", '{$newValue}'", $posicaoFimUserSubjects, 0);

                    // Escrever o conteúdo atualizado de volta para o arquivo .ts
                    file_put_contents($filePath, $novoConteudoTS);
                }
            }
        }
    }

    public function addMenu($filePath, $newMenu)
    {
        // Verifica se o arquivo existe
        if (File::exists($filePath)) {
            $codigo = File::get($filePath); // Obtenha o conteúdo do arquivo TypeScript

            // Verifica se o novo menu já está presente no código
            if (strpos($codigo, $newMenu) === false) {
                // Adiciona o novo menu ao final do código
                $novoCodigo = rtrim($codigo, "] \r\n") . "\n" . $newMenu . "\n]";

                // Atualize o arquivo TypeScript com o novo código
                File::put($filePath, $novoCodigo);
            }
        }
    }

    public function addFkForm($fkRoute, $fkReq, $autoCompleteTitle)
    {
        Pluralizer::useLanguage('portuguese');

        $dataName = Str::lower("$fkRoute");
        $itemsName = Str::plural(Str::lower("$fkRoute"));
        $loading = Str::lower("$fkRoute");
        $fetchName = Str::camel("fetch$fkRoute");
        $rules = $fkReq ? '[rules.requiredValidator]' : '[]';

        $state = <<<EOT
        $itemsName,
        EOT;
        $methods = <<<EOT
        $fetchName,
        EOT;
        $fetch = "$fetchName()";
        $input = <<<EOT
        <AppAutocomplete
          v-model="data.{$dataName}_id"
          v-debounce:900="$fetchName"
          :items="$itemsName"
          label="$fkRoute"
          :return-object="false"
          :loading="loading.$loading"
          :rules="$rules"
          item-value="id"
          item-title="$autoCompleteTitle"
        >
            <template #clear>
                <button
                  @click="() => {
                    $fetchName()
                    blurHandler()
                  }"
                >
                  <VIcon icon="tabler-x" />
                </button>
            </template>
        </AppAutocomplete>
        EOT;

        return [
            'state' => $state,
            'methods' => $methods,
            'fetch' => $fetch,
            'input' => $input
        ];
    }

    public function addFkStore($fkName, $fkRoute)
    {
        Pluralizer::useLanguage('portuguese');

        $methodName = Str::camel("fetch$fkName");
        $itemsName = Str::plural(Str::lower("$fkName"));
        $serviceName = "{$fkRoute}Service";
        $loading = Str::lower("$fkName");

        $state = <<<EOT
        $itemsName: [],
        EOT;

        $loadingItem = <<<EOT
        $loading: false,
        EOT;

        $fetch = <<<EOT
        async $methodName(search?: string) {
              this.loading.$loading = true
              await $serviceName.$methodName(search)
                .then(res => {
                  this.$itemsName = res.data
                  this.loading.$loading = false
                }).catch(() => {
                  this.$itemsName = []
                  this.loading.$loading = false
                })
            },
        EOT;

        return [
            'fetch' => $fetch,
            'state' => $state,
            'loading' => $loadingItem,
        ];
    }

    public function addFkService($fkName)
    {
        Pluralizer::useLanguage('portuguese');

        $methodName = Str::camel("fetch$fkName");
        $fkNameSlug = Str::slug($fkName);

        return <<<EOT
        async $methodName(search?: string) {
            return await this.getOrDeleteRequest('GET', {
              search,
            }, 'listar/$fkNameSlug')
        }
        EOT;
    }

    public function handle(): void
    {
        info('Gerando Front-end');

        $crudName = $this->argument("crudName");
        $attributes = $this->argument("attributes");
        $attributesDefault = $this->argument('attributesDefault');
        $rules = $this->argument('rules');
        $foreigners = $this->argument('foreigners');

        $fields = [];
        foreach($this->parseFieldsAndTypes($attributes) as $field => $_){
            $type = select(
                label: "Selecione tipo de campo para ". \Str::upper($field),
                options: [
                    'input',
                    'cnpj',
                    'cpf',
                    'telefone',
                    'celular',
                    'currency',
                    'textarea',
                    'date',
                    'autocomplete',
                    'checkbox',
                    'fileinput',
                    'imageinput',
                    'switch',
                ],
                default: 'input'
            );
            $fields[] = $this->cdfTemplateCommand->createCol(
                $this->cdfTemplateCommand->createStubInputs($crudName, $field, $type, $rules[$field] ?? [])
            );
            $this->headersItems[] = $this->cdfTemplateCommand->createStubHeaderItem($crudName, $field, $type);
            $this->termsItems[] = $this->cdfTemplateCommand->createStubTermsItem($crudName, $field);
        }

        $this->searchDefault = select(
            label: "Qual é o campo padrão da pesquisar?",
            options: array_keys($this->parseFieldsAndTypes($attributes)),
            default: array_keys($this->parseFieldsAndTypes($attributes))[0]
        );

        $pathCrud = $this->dirFront . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . strtolower($crudName);

        if(file_exists($this->dirFront))
        {
            if(!file_exists($pathCrud)){
                foreach ($this->directories() as $folder){
                    mkdir("$pathCrud\\$folder", 0700, true);
                }

                $store = "use{$crudName}Store";
                $interface = "I$crudName";
                $serviceName = $crudName . 'Service';
                $attributesTypes = "$attributes\n";

                $fkFormMethods = [];
                $fkFormState = [];
                $fkFormFetchs = [];
                $fkFormInputs = [];
                $fkStoreState = [];
                $fkStoreFetch = [];
                $fkStoreLoading = [];
                $fkService = [];

                if($foreigners){
                    foreach ($foreigners as $foreigner){
                        $attributesTypes .= "{$foreigner['fkAttribute']}: string\n";

                        $model = new $foreigner['fkNamespace']();

                        $autoCompleteTitle = select(
                            label: "Qual é o titulo do autocomplete da fk: {$foreigner['fkName']}?",
                            options: $model->getFillable(),
                            default: $model->getFillable()[0]
                        );

                        $form = $this->addFkForm($foreigner['fkName'], $foreigner['fkRequired'], $autoCompleteTitle);
                        $fkFormState[] = $form['state'];
                        $fkFormMethods[] = $form['methods'];
                        $fkFormFetchs[] = $form['fetch'];
                        $fkFormInputs[] = $this->cdfTemplateCommand->createCol($form['input']);

                        $state = $this->addFkStore($foreigner['fkName'], $foreigner['fkRoute']);
                        $fkStoreState[] = $state['state'];
                        $fkStoreFetch[] = $state['fetch'];
                        $fkStoreLoading[] = $state['loading'];

                        $fkService[] = $this->addFkService($foreigner['fkName']);
                    }
                }

                $this->cdfTemplateCommand->generateStubCriar($crudName, $store);
                $this->cdfTemplateCommand->generateStubEditar($crudName, $store);
                $this->cdfTemplateCommand->generateStubForm($crudName, $store, $interface, $attributesDefault, $fields, $this->imports, $fkFormState, $fkFormMethods, $fkFormFetchs, $fkFormInputs);
                $this->cdfTemplateCommand->generateStubListar($crudName, $store, $interface, $this->cdfTemplateCommand->createStubHeader($this->headersItems), $this->cdfTemplateCommand->createStubTerms($this->termsItems));
                $this->cdfTemplateCommand->generateStubTypes($crudName, $interface, $attributesTypes);
                $this->cdfTemplateCommand->generateStubStore($crudName, $store, $serviceName, $interface, $this->searchDefault, $attributesDefault, $fkStoreState, $fkStoreFetch, $fkStoreLoading);
                $this->cdfTemplateCommand->generateStubService($crudName, $serviceName, Str::slug($crudName, '-', 'pt'), $fkService);

                foreach(config('cdf.locales') as $lang){
                    $headers = [];
                    $terms = [];
                    $form = [];

                    foreach($this->parseFieldsAndTypes($attributes) as $field => $_){
                        $form[strtolower($field)] = [
                            'label' => Str::headline($field),
                            'placeholder' => ''
                        ];
                    }

                    foreach($this->parseFieldsAndTypes($attributes) as $field => $_){
                        $headers[strtolower($field)] = Str::headline($field);
                    }

                    foreach($this->parseFieldsAndTypes($attributes) as $field => $_){
                        $terms[strtolower($field)] = Str::headline($field);
                    }

                    Pluralizer::useLanguage('portuguese');
                    $attributesLang = [
                        strtolower($crudName) => [
                            'register' => 'Cadastrar '.$crudName,
                            'edit' => 'Editar '.$crudName,
                            'list' => Str::plural($crudName),
                            'form' => $form,
                            'headers' => $headers,
                            'terms' => $terms
                        ]
                    ];

                    $this->cdfTemplateCommand->generateStubLang($lang, $attributesLang);

                    $pathAbility = $this->dirFront . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'abilityConfig.ts';
                    $this->addToArrayInTsFile($pathAbility, strtolower($crudName));

                    $pathMenu = $this->dirFront . DIRECTORY_SEPARATOR . 'navigation' . DIRECTORY_SEPARATOR . 'vertical' . DIRECTORY_SEPARATOR . 'index.ts';
                    $menu = "{
                        title: '$crudName',
                        icon: { icon: 'tabler-template' },
                        children: [
                          {
                            title: 'Criar',
                            to: '".strtolower($crudName)."-cadastrar',
                            action: 'create',
                            subject: '".strtolower($crudName)."',
                          },
                          {
                            title: 'Pesquisar',
                            to: '".strtolower($crudName)."',
                            action: 'list',
                            subject: '".strtolower($crudName)."',
                          },
                        ],
                      },";
                    $this->addMenu($pathMenu, $menu);
                }

                info("Criado crud ($crudName) com sucesso!");

                note("Executando eslint no crud $crudName");
                $command = sprintf("cd %s && %s %s --fix",
                    realpath(dirname(base_path()) . DIRECTORY_SEPARATOR . str_replace('src', '', env('DIR_FRONT_END'))),
                    ".\\node_modules\\.bin\\eslint",
                    ".\\src\\pages\\".strtolower($crudName)."\\**\\*.{ts,vue} ./src/navigation/vertical/*.{ts,vue}");

                exec($command);
            } else {
                error("O crud ($crudName) já existe!");
            }
        } else {
            error("Front-End não encontrado!");
        }
    }

    public function directories(): array
    {
        return [
            'cadastrar',
            'components',
            'editar',
            'locales',
            'services',
            'store',
        ];
    }
}
