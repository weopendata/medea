<?php


namespace App\Repositories\Eloquent;


use App\Models\Eloquent\PanTypology;

class PanTypologyRepository
{
    /**
     * @var PanTypology
     */
    private $typology;

    /**
     * PanTypologyRepository constructor.
     *
     * @param  PanTypology $typology
     */
    public function __construct(PanTypology $typology)
    {
        $this->typology = $typology;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->typology->find($id);
    }

    /**
     * @param  string $code
     * @return PanTypology|null
     */
    public function findByCode(string $code)
    {
        return $this
            ->typology
            ->where('code', $code)
            ->first();
    }

    /**
     * @param  array $panIds
     * @return array
     */
    public function getPanTypologyInformationForIds(array $panIds): array
    {
        if (empty($panIds)) {
            return [];
        }

        $parentIds = [];

        $panTypologyInformation = $this
            ->typology
            ->whereIn('code', $panIds)
            ->get()
            ->mapWithKeys(function ($result) use (&$parentIds) {
                if (!empty($result['main_category_id'])) {
                    $parentIds[] = $result['main_category_id'];
                }

                return [
                    $result['code'] => [
                        'uri' => $result['uri'],
                        'label' => $result['label'],
                        'initialPeriod' => array_get($result, 'meta.initialperiod'),
                        'finalPeriod' => array_get($result, 'meta.finalperiod'),
                        'code' => $result['code'],
                        'imageUrl' => array_get($result, 'meta.imageUrl'),
                        'mainCategory' => $result['label'],
                        'main_category_id' => $result['main_category_id'],
                        'startYear' => $result['start_year'],
                        'endYear' => $result['end_year'],
                        'productionClassificationDescription' => implode(' ', array_get($result, 'meta.scopeNotes') ?? [])
                    ],
                ];
            });

        $parentLabels = [];

        if (!empty($parentIds)) {
            $parentLabels = $this
                ->typology
                ->whereIn('id', $parentIds)
                ->get()
                ->mapWithKeys(function ($typology) {
                    return [
                      $typology['id'] => $typology['label']
                    ];
                })
                ->toArray();
        }

        return $panTypologyInformation
            ->map(function ($typology) use ($parentLabels) {
                if (!empty($typology['main_category_id']) && !empty($parentLabels[$typology['main_category_id']])) {
                    $typology['mainCategory'] = $parentLabels[$typology['main_category_id']];
                }

                unset($typology['main_category_id']);

                return $typology;
            })
            ->toArray();

    }

    /**
     * Typologies are uniquely identified by their code:
     * Examples: 01, 01-02, 02-03-01-04
     *
     * @param  array $typology
     * @return PanTypology
     * @throws \Exception
     */
    public function updateOrCreate(array $typology)
    {
        $typology = $this
            ->typology
            ->updateOrCreate(
                ['code' => $typology['code']],
                $typology
            );

        // Determine the depth
        $pieces = explode('-', $typology['code']);
        $depth = count($pieces) - 1;

        $typology->depth = $depth;
        $typology->save();

        // If the code contains parent information, i.e. a "-" character, then link the parent_id
        if ($depth == 0) {
            return $typology;
        }

        // NOTE: this snippet below can be removed, or updated. The assumption that the path of the node contains the parent path is wrong, i.e. 05-01-91-03
        // I'll leave this here as a reminder in case someone has the idea to base the logic based on the assumption that paths contain the parent's path as a sub-path
        /*// Remove the last piece of the code, which is the code for the child/leaf itself and keep the other parts, which is the unique code for the parent
        array_pop($pieces);

        $parentCode = implode('-', $pieces);

        $parentTypology = $this->findByCode($parentCode);

        if (empty($parentTypology)) {
            throw new \Exception("We have updated / insert the typology, but the parent with code $parentCode could not be found.");
        }

        $typology->parent_id = $parentTypology->id;
        $typology->save();*/

        return $typology;
    }

    /**
     * @param  int|null $startYear
     * @param  int|null $endYear
     * @return array
     */
    public function getPanIdsForDateRange(?int $startYear = null, ?int $endYear = null): array
    {
        if (is_null($startYear) && is_null($endYear)) {
            return [];
        }

        return PanTypology
            ::when(!is_null($startYear), function ($query) use ($startYear) {
                return $query->where('start_year', '>=', $startYear);
            })
            ->when(!is_null($endYear), function ($query) use ($endYear) {
                return $query->where('end_year', '<=', $endYear);
            })
            ->select('id')
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * @param  string $code
     * @return array
     */
    public function getMetaForPanId(string $code)
    {
        $panTypology = $this->findByCode($code);

        if (empty($panTypology)) {
            return [];
        }

        $mainCategory = $panTypology['label'];

        if (!empty($panTypology['main_category_id'])) {
            $parent = $this->getById($panTypology['main_category_id']);

            $mainCategory = $parent['label'];
        }

        return [
            'uri' => $panTypology['uri'],
            'label' => $panTypology['label'],
            'initialPeriod' => array_get($panTypology, 'meta.initialperiod'),
            'finalPeriod' => array_get($panTypology, 'meta.finalperiod'),
            'initialDate' => array_get($panTypology, 'meta.properties.InitialDate.0'),
            'finalDate' => array_get($panTypology, 'meta.properties.FinalDate.0'),
            'code' => $panTypology['code'],
            'imageUrl' => array_get($panTypology, 'meta.imageUrl'),
            'mainCategory' => $mainCategory,
        ];
    }

    /**
     * @return array
     */
    public function getTree()
    {
        $tree = [];

        $allTypologies = $this
            ->typology
            ->orderBy('depth', 'ASC')
            ->get()
            ->mapWithKeys(function ($typology) {
                $typology = $typology->toArray();

                // Build up the parent code if there is one
                $typology['parent_code'] = null;

                if (!empty($typology['parent_id'])) {
                    // We assume there's only 1 parent URI
                    $parentUri = array_get($typology, 'meta.broaders.0');
                    $pieces = explode('/', $parentUri);
                    $parentCode = end($pieces);

                    $pieces = explode('-', $parentCode);

                    $typology['parent_code'] = implode('-', $pieces);
                }

                $children = array_get($typology, 'meta.narrowers') ?? [];

                // Save the keys as strings, but since they are numerical, we need to force them to be strings
                // This can be done by casting a class to an array, i.e. ['1' => ...] will not work as it will interpret it as a number, == [1 => ...]
                $childrenCodes = collect($children)
                    ->mapWithKeys(function ($child) {
                        $pieces = explode('/', $child);
                        return [end($pieces) => []];
                    })
                    ->toArray();

                $meta = $typology['meta'];
                $meta = array_only(
                    $meta,
                    [
                        'initialperiod',
                        'finalperiod',
                        'imageUrl',
                        'scopeNotes',
                        'definitions',
                        'properties',
                    ]
                );

                $meta['childrenCodes'] = $childrenCodes;

                $typology = array_only(
                    $typology,
                    [
                        'label',
                        'parent_code',
                        'uri',
                        'depth',
                        'code',
                        'parent_code',
                    ]
                );

                $typology = array_merge($typology, $meta);

                return [$typology['code'] => $typology];
            });

        foreach ($allTypologies as $typology) {
            $code = $typology['code'];
            $parentCode = $typology['parent_code'];

            if (empty($parentCode)) {
                $tree[$code] = $typology;
            } else {
                try {
                    $childValue = &$this->getItem($tree, $parentCode);
                    $childValue[$code] = $typology;
                } catch (\Exception $ex) {
                    // This could happen as sometimes the import command for the typology misses out on some parts of the typology because of timeouts on the API server's side
                    \Log::error("No entry found for parent $parentCode, this was required for typology with code $code");
                }
            }
        }

        return [
            'tree' => array_values($tree),
            'map' => $allTypologies->toArray(),
        ];
    }

    /**
     * @param  array  $array
     * @param  string $path
     * @return mixed
     * @throws \Exception
     */
    private function &getItem(&$array, $path)
    {
        $target = &$array;
        $path = explode('-', $path);

        $pathParts = [];
        $pathString = '';
        foreach ($path as $pathPiece) {
            $pathString .= '-' . $pathPiece;
            $pathString = ltrim($pathString, '-');
            $pathParts[] = $pathString;
        }

        foreach ($pathParts as $key) {
            if (!is_null(@$target[$key]) && array_key_exists('childrenCodes', $target[$key])) {
                $target = &$target[$key];
                $target = &$target['childrenCodes'];
            } else {
                if (!is_null(@$target[$key])) {
                    $target = &$target[$key];
                } else {
                    throw new \Exception('Undefined path: ["' . implode('","', $path) . '"]');
                }
            }
        }

        return $target;
    }
}
