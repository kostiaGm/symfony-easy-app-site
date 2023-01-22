<?php

namespace App\Controller\Traits;

use App\Entity\Interfaces\SafeDeleteInterface;
use Symfony\Component\HttpFoundation\Request;

trait BulkTrait
{
    public function bulkChange(
        Request $request,
        string $successMessage,
        string $deleteMessage,
        int $status = SafeDeleteInterface::STATUS_DELETED): bool
    {
        try {
            $ids = $request->request->get('delete_item', []);
            if (!empty($ids)) {
                $items = $this->repository->getByIdArray($ids)->getQuery()->getResult();
                $last = $items[count($items) - 1];

                foreach ($items as $index => $item) {
                    $item->setStatus($status);
                    $this->repository->add($item, (($index % 30) == 0));
                }

                $last->setStatus($status);
                $this->repository->add($last, true);
                $this->addFlash('success', $successMessage);
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->addFlash('error', $deleteMessage);
            return false;
        }

        return true;
    }
}