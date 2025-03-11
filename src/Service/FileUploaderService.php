<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploaderService
{
    private string $targetDirectory;
    private array $allowedExtensions;

    public function __construct(KernelInterface $kernel, string $uploadDirectory, array $allowedExtensions)
    {
        $this->targetDirectory = $kernel->getProjectDir().'/public/uploads/'.$uploadDirectory;
        $this->allowedExtensions = $allowedExtensions;
    }

    public function upload(UploadedFile $file, ?string $oldFileName = null): string
    {
        $extension = $file->guessExtension();
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new \Exception('Extension de fichier non autorisée. Extensions autorisées: ' . implode(', ', $this->allowedExtensions));
        }

        $fileName = uniqid().'.'.$extension;
        
        // Supprimer l'ancienne photo si elle existe
        if ($oldFileName) {
            $this->deleteFile($oldFileName);
        }
        
        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new \Exception('Erreur lors de l’upload de l’image.');
        }
        
        return $fileName;
    }

    public function deleteFile(string $fileName): void
    {
        $filePath = $this->targetDirectory . '/' . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
