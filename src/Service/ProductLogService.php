<?php

namespace App\Service;

use App\Repository\ProductLogRepository;
use DateTime;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeImmutableToDateTimeTransformer;

class ProductLogService
{
    private $productLogRepository;

    public function __construct(ProductLogRepository $productLogRepository)
    {
        $this->productLogRepository = $productLogRepository;
    }

    public function getProductLogs($id)
    {
        $logs = $this->productLogRepository->findBy(["product" => $id], ["created_at" => "ASC"]);

        $currentYear = date('Y');

        $weekData = ["sold" => [], "acquired" => []];
        $monthData = ["sold" => [], "acquired" => []];
        $yearData = ["sold" => [], "acquired" => []];

        foreach ($logs as $log) {
            $dateTrans = new DateTimeImmutableToDateTimeTransformer();
            $date = $dateTrans->transform($log->getCreatedAt());
            $amount = $log->getAmount();
            $isSold = $log->isSold();
        
            $soldOrAcquired = $isSold == 1 ? 'sold' : 'acquired';
        
            $yearKey = $date->format('Y');
            if (!isset($yearData[$soldOrAcquired][$yearKey])) {
                $yearData[$soldOrAcquired][$yearKey] = 0;
            }
            $yearData[$soldOrAcquired][$yearKey] += $amount;
        
            if ($date->format('Y') == $currentYear) {
                $weekKey = $date->format('W');
                if (!isset($weekData[$soldOrAcquired][$weekKey])) {
                    $weekData[$soldOrAcquired][$weekKey] = 0;
                }
                $weekData[$soldOrAcquired][$weekKey] += $amount;
        
                $monthKey = $date->format('m');
                if (!isset($monthData[$soldOrAcquired][$monthKey])) {
                    $monthData[$soldOrAcquired][$monthKey] = 0;
                }
                $monthData[$soldOrAcquired][$monthKey] += $amount;
            }
        }
        
        ksort($weekData['sold']);
        ksort($weekData['acquired']);
        ksort($monthData['sold']);
        ksort($monthData['acquired']);
        ksort($yearData['sold']);
        ksort($yearData['acquired']);

        return ["weeks" => $weekData, "months" => $monthData, "years" => $yearData];
    }
}
