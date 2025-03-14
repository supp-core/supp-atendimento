<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AttachmentManager
{
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
        'image/gif'
    ];

    private string $uploadDir;

    public function __construct(string $uploadDir)
    {
        // Garante que o diretório de upload existe
        if (!file_exists($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            throw new \RuntimeException('Não foi possível criar o diretório de upload');
        }

        $this->uploadDir = $uploadDir;
    }

    public function validateFile(UploadedFile $file): bool
    {
        if (!$file || !($file instanceof UploadedFile) || !$file->isValid()) {
            return false; // Retorna false em vez de lançar exceção para entradas inválidas
        }

        // Valida o tipo MIME do arquivo
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new BadRequestException('Tipo de arquivo não permitido');
        }

        if (!$file->isValid()) {
            throw new BadRequestException('Arquivo inválido: ' . $file->getErrorMessage());
        }

      

        // Valida o tamanho do arquivo (10MB máximo)
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new BadRequestException('Arquivo muito grande. Tamanho máximo permitido: 10MB');
        }

        return true;
    }

    public function uploadFile(UploadedFile $file): string
    {
        try {


            $fileArray = [
                "test" => $file->isFile(),
                "originalName" => $file->getClientOriginalName(),
                "mimeType" => $file->getClientMimeType(),
                "error" => $file->getError(),
                "pathName" => $file->getPathname(),
                "fileName" => $file->getFilename()
            ];

            // Convertendo para JSON
            $json = json_encode($fileArray, JSON_PRETTY_PRINT);
            echo $json;

            // die();
            // Verificar se o arquivo existe e pode ser lido
            if (!file_exists($file->getPathname()) || !is_readable($file->getPathname())) {
                throw new \Exception('Arquivo temporário não existe ou não pode ser lido: ' . $file->getPathname());
            }

            // Gera nome único
            $filename = uniqid() . '_' . $this->sanitizeFilename($file->getClientOriginalName());

            // Verifica se o diretório de destino existe e é gravável
            if (!is_dir($this->uploadDir) || !is_writable($this->uploadDir)) {
                throw new \Exception('Diretório de upload não existe ou não é gravável: ' . $this->uploadDir);
            }

            // Move o arquivo
            $file->move($this->uploadDir, $filename);

            // Verifica se o arquivo foi movido com sucesso
            if (!file_exists($this->uploadDir . '/' . $filename)) {
                throw new \Exception('Falha ao mover o arquivo para o destino');
            }

            return $filename;
        } catch (\Exception $e) {
            // Registra o erro
            error_log('Erro no upload de arquivo: ' . $e->getMessage());

            // Relança a exceção para ser tratada pelo chamador
            throw new BadRequestException('Erro ao fazer upload do arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Sanitiza o nome do arquivo removendo caracteres especiais e espaços
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove caracteres especiais e espaços do nome do arquivo
        $info = pathinfo($filename);
        $filename = preg_replace('/[^a-zA-Z0-9-_.]/', '', $info['filename']);
        return $filename . '.' . $info['extension'];
    }

    /**
     * Remove um arquivo do sistema de arquivos
     */
    public function removeFile(string $filename): bool
    {
        $filepath = $this->uploadDir . '/' . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
}
