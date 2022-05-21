<?php

namespace App\Services;

use App\Helpers\Pager;
use App\Repositories\ElasticSearch\FindRepository;

class FindService
{
    /**
     * @param  array       $filters
     * @param  int|null    $limit
     * @param  int|null    $offset
     * @param  string|null $orderBy
     * @param  string|null $orderFlow
     * @return array
     */
    public function getAllWithFilter(array $filters, ?int $limit = 20, ?int $offset = 0, ?string $orderBy = 'findDate', ?string $orderFlow = 'ASC'): array
    {
        $findsResult = app(FindRepository::class)->getAllWithFilter($filters, $limit, $offset, $orderBy, $orderFlow);

        $findsResult['data'] = TransformerService::transformFinds($findsResult['data']);
        $findsResult['facets'] = TransformerService::transformFindFacets($findsResult['facets']);

        $findsResult['data'] = $this->obfuscateFinds($findsResult['data'], $filters);
        $findsResult['paging'] = $this->addPagingInfo($limit, $offset, $findsResult['total']);

        return $findsResult;
    }

    /**
     * @param  array $finds
     * @param  array $filters
     * @return array
     */
    private function obfuscateFinds(array $finds, array $filters): array
    {
        // If the user is a researcher or personal finds have been set, return the exact find location
        // If this is not the case, round up to 2 digits, which lowers the accuracy to about 10km
        if (!empty($filters['myfinds'])) {
            return $finds;
        }

        $obfuscatedFinds = [];

        $user = auth()->user();

        foreach ($finds as $find) {
            if (empty($user)
                || (!empty($find['finderId']) && $find['finderId'] != $user->id) && !in_array('onderzoeker', $user->getRoles())
            ) {
                if (!empty($find['lat'])) {
                    $accuracy = isset($find['accuracy']) ? $find['accuracy'] : 1;
                    $find['accuracy'] = max(7000, $accuracy);
                }
            }

            $obfuscatedFinds[] = $find;
        }

        return $obfuscatedFinds;
    }

    /**
     * @param  int $limit
     * @param  int $offset
     * @param  int $totalCount
     * @return array
     */
    private function addPagingInfo(int $limit, int $offset, int $totalCount)
    {
        $pages = Pager::calculatePagingInfo($limit, $offset, $totalCount);

        $pagingInfo = [
            'total_count' => $totalCount,
        ];

        foreach ($pages as $rel => $page_info) {
            $pagingInfo[$rel] = [
                'offset' => $page_info[0],
                'limit' => $page_info[1],
            ];
        }

        return $pagingInfo;
    }
}