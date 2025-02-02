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
        // Valida o tipo MIME do arquivo
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new BadRequestException('Tipo de arquivo não permitido');
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
            // Gera um nome único para o arquivo
            $filename = uniqid() . '_' . $this->sanitizeFilename($file->getClientOriginalName());
            
            // Move o arquivo para o diretório de upload
            $file->move($this->uploadDir, $filename);
            
            return $filename;
        } catch (\Exception $e) {
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