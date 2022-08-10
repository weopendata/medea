<?php


namespace App\Http\Controllers;


use App\Constants\UploadTypes;
use App\Models\Eloquent\FileUpload;
use App\Models\Eloquent\ImportJob;
use App\Repositories\Eloquent\FileUploadRepository;
use App\Repositories\Eloquent\ImportLogRepository;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index(): \Illuminate\Http\Response
    {
        return response()
            ->view('pages.uploads', [
                'uploads' => []
            ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
    {
        $uploads = app(FileUploadRepository::class)->get();

        return response()->json($uploads);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogs(Request $request, $importJobId)
    {
        $logs = app(ImportLogRepository::class)->getLogsForJob($importJobId);

        return response()->json($logs);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startUpload(Request $request, $fileUploadId)
    {
        $fileUpload = FileUpload::find($fileUploadId);

        if (empty($fileUpload)) {
            abort(404);
        }

        $importJob = ImportJob::create([
            'import_files_id' => $fileUploadId,
            'status' => 'queued',
        ]);

        $importJob->sendToQueue();

        return response()->json(['message' => 'Success']);
    }

    /**
     * @param Request $request
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'file|required',
            'name' => 'min:2|required',
            'type' => 'required|in:' . implode(',', UploadTypes::getUploadTypeValues())
        ]);

        $fileName = $request->input('name');
        $fileName = trim($fileName);

        $path = $request->file->storeAs('local', $fileName . '____' . str_random(10) . '.csv');

        $fileUpload = [
            'path' => $path,
            'name' => $fileName,
            'type' => $request->input('type'),
            'user_name' => $request->user()->firstName . ' ' . $request->user()->lastName
        ];

        app(FileUploadRepository::class)->store($fileUpload);

        return response()->json(['message' => 'success']);
    }

    /**
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        app(FileUploadRepository::class)->delete($request->file_upload);

        return response()->json(['message' => 'success']);
    }
}
