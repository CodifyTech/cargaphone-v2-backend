<?php

namespace Domains\Auth\BLL;

use Domains\Shared\BLL\BaseBLL;
use Domains\Auth\Services\UserService;
use Domains\Shared\Services\AwsS3Service;

class UserBLL extends BaseBLL
{
    public function __construct(private readonly UserService $userService, private readonly AwsS3Service $awsS3Service)
    {
        $this->setService($this->userService);
    }

    public function roles($options){
        return $this->userService->roles($options);
    }

    public function store($data)
    {
        if (isset($data['foto'])) {
            $nomeArquivo = $this->awsS3Service->uploadBase64ImageToS3(
                base64String: $data['foto'],
                path: 'fotos_usuario/'
            );
            $data['foto'] = $nomeArquivo;
        }
        return $this->userService->store($data);
    }

    public function update($data, string $id)
    {
        $usuario = $this->show($id);
        $fotoUsuario = $data['foto'] ?? null;

        if (!is_null($fotoUsuario)) {
            $arquivoAtual = basename(parse_url($usuario->foto, PHP_URL_PATH));
            $fileName = $this->awsS3Service->uploadBase64ImageToS3(
                base64String: $fotoUsuario,
                path: 'fotos_usuario/',
                existingFileReference: $arquivoAtual == '' ? null : $arquivoAtual
            );

            if ($fileName !== null) {
                $data['foto'] = $fileName;
            }
        }
        return $this->userService->update($data, $id);
    }
}
