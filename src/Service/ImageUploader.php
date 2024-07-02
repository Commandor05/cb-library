<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

class ImageUploader
{
    public function __construct(
        private string $photoDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new FileException('Image upload error');
        }

        return $fileName;
    }

    public function validateImageUpload(Request $request): ConstraintViolationListInterface
    {
        $fileConstraints = new File([
            'maxSize' => '2M',
            'maxSizeMessage' => 'The file is too big, max allowed size is 2 Mb',
            'mimeTypes' => ['jpg' => 'image/jpeg', 'png' => 'image/png'],
            'mimeTypesMessage' => 'The format is incorrect, only JPG and PNG allowed'
        ]);

        $validator = Validation::createValidator();

        return $validator->validate($request->files->get('file'), $fileConstraints);
    }

    public function getTargetDirectory(): string
    {
        return $this->photoDirectory;
    }
}