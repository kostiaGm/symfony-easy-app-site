<?php

namespace App\Controller\Traits;

use App\Lib\ImageOptimizer;
use Symfony\Component\Form\FormInterface;

trait FileUploadTrait
{
    protected function uploadImage(FormInterface $form, string $field = 'uploadImage'): string
    {
        $newFilename = '';

        $imageFile = $form->get($field)->getData();

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            $basePath = $this->getParameter('kernel.project_dir');
            $imageParams = $this->getParameter('image');

            $originPath = $basePath .'/'.$imageParams['path']['origin'] ?? null;
            $imageFile->move($originPath, $newFilename);

            foreach ($imageParams['size'] as $key => $value) {
                $path = $imageParams['path'][$key] ?? null;
                if (!$path) {
                    continue;
                }

                copy($originPath.'/'.$newFilename, $basePath .'/'. $path .'/'. $newFilename);
                $imageOptimizer = new ImageOptimizer($value['width'], $value['height']);

                $imageOptimizer->resize($basePath .'/'. $path .'/'. $newFilename);
            }

        }
        return $newFilename;
    }

    protected function removeImage(?string $fileName): void
    {
        if (empty($fileName)) {
            return;
        }

        $basePath = $this->getParameter('kernel.project_dir');
        $imageParams = $this->getParameter('image');

        foreach ($imageParams['size'] as $key => $value) {
            $path = $imageParams['path'][$key] ?? null;

            if (!$path || !file_exists($basePath .'/'.$path .'/'.$fileName)) {
                continue;
            }

            unlink($basePath .'/'.$path .'/'.$fileName);
        }
    }
}
