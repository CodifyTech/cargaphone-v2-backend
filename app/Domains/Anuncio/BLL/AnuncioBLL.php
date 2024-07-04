<?php

namespace Domains\Anuncio\BLL;

use App\Domains\Shared\Services\AwsS3Service;
use Domains\Anuncio\Services\AnuncioService;
use Domains\Shared\BLL\BaseBLL;

class AnuncioBLL extends BaseBLL
{
    public function __construct(
        private readonly AnuncioService $anuncioService,
        private readonly AwsS3Service $awsS3Service
    ) {
        $this->setService($this->anuncioService);
    }
    // 👉 methods

    public function listarTotems($options)
    {
        return $this->anuncioService->listarTotems($options);
    }

    public function store($data)
    {
        if (isset($data['arquivo'])) {
            $nomeArquivo = $this->awsS3Service->uploadFromS3(
                file: $data['arquivo'],
                caminhoS3: 'anuncios/'
            );
            $data['arquivo'] = $nomeArquivo;
        }
        return $this->anuncioService->store($data);
    }
}
