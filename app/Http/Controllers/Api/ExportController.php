<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\FindRepository;
use App\Http\Requests\ExportFindsRequest;
use League\Csv\Writer;
use App\Models\FindEvent;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function __construct(FindRepository $finds)
    {
        $this->finds = $finds;
    }

    public function export(ExportFindsRequest $request)
    {
        set_time_limit(300);

        $writer = Writer::createFromFileObject(new \SplTempFileObject());
        $writer->setDelimiter(';');
        $writer->setNewline("\r\n");
        $writer->setOutputBOM(Writer::BOM_UTF8);

        // Set the CSV header
        $header = [
            'MEDEA vondst-ID',
            'objectcategorie',
            'periode',
            'materiaal',
            'X-coördinaat',
            'Y-coördinaat',
            'precisie van locatie',
            'vondst toegevoegd op',
            'personalia van de vinder'
        ];

        $writer->insertOne($header);

        // Get all the bare (not connected) FindEvent nodes
        $finds = $this->finds->getAll();

        foreach ($finds as $findNode) {
            // Get the data for the findEvent
            $find = $this->finds->getExportableData($findNode->getId());

            // Pre-process some data points
            $createdAt = new Carbon($find['created_at']);

            $personalInfo = '';

            if (! empty($find['showName']) && @$find['showName'] == true) {
                $personalInfo = $find['firstName'] . ' ' . $find['lastName'];

                if (! empty($find['detectoristNumber'])) {
                    $personalInfo .= ' - ' . $find['detectoristNumber'];
                }
            }

            $writer->insertOne([
                $find['identifier'],
                @$find['objectCategory'],
                @$find['period'],
                @$find['objectMaterial'],
                @$find['longitude'],
                @$find['latitude'],
                @$find['accuracy'],
                $createdAt->toDateString(),
                $personalInfo
            ]);
        }

        return response()->make($writer->__toString())->withHeaders([
            'Content-Type' => 'text/csv',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment;filename=Vondsten_Export_' . date('Y-m-md_his') . '.csv'
        ]);
    }
}
