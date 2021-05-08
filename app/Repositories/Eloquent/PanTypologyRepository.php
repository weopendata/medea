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
     * @param PanTypology $typology
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
     * @param string $code
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
     * Typologies are uniquely identified by their code:
     * Examples: 01, 01-02, 02-03-01-04
     *
     * @param array $typology
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

        // TODO: this snippet below can be removed, or updated. The assumption that the path of the node contains the parent path is wrong, i.e. 05-01-91-03
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
     * @param string $code
     * @return array
     */
    public function getMetaForPanId(string $code)
    {
        $panTypology = $this->findByCode($code);

        if (empty($panTypology)) {
            return [];
        }

        $mainCategory = $panTypology['label'];

        if (!empty($panTypology['parent_id'])) {
            $parent = $this->getById($panTypology['parent_id']);

            $mainCategory = $parent['label'];
        }

        return [
            'uri' => $panTypology['uri'],
            'label' => $panTypology['label'],
            'initialPeriod' => array_get($panTypology, 'meta.initialperiod'),
            'finalPeriod' => array_get($panTypology, 'meta.finalperiod'),
            'code' => $panTypology['code'],
            'imageUrl' => array_get($panTypology, 'meta.imageUrl'),
            'mainCategory' => $mainCategory
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
                        'properties'
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
                        'parent_code'
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
            'map' => $allTypologies->toArray()
        ];
    }

    /**
     * @param array $array
     * @param string $path
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
