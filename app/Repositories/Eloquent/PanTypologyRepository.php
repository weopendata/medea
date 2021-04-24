<?php


namespace App\Repositories\Eloquent;


use App\Models\Eloquent\PanTypology;
use Illuminate\Support\Str;

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

        // Remove the last piece of the code, which is the code for the child/leaf itself and keep the other parts, which is the unique code for the parent
        array_pop($pieces);

        $parentCode = implode('-', $pieces);

        $parentTypology = $this->findByCode($parentCode);

        if (empty($parentTypology)) {
            throw new \Exception("We have updated / insert the typology, but the parent with code $parentCode could not be found.");
        }

        $typology->parent_id = $parentTypology->id;
        $typology->save();

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
}
