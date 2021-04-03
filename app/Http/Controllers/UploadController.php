<?php


namespace App\Http\Controllers;


use App\Repositories\Eloquent\FileUploadRepository;
use App\Repositories\Eloquent\ImportLogRepository;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function index()
    {
        $uploads = [];

        return response()
            ->view('pages.uploads', [
                'uploads' => $uploads
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

    public function show(Request $request)
    {

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
            'name' => 'min:2|required'
        ]);

        $path = $request->file->storeAs('local', $request->input('name') . '____' . str_random(10) . '.csv');

        $fileUpload = [
            'path' => $path,
            'name' => $request->input('name'),
            'user_name' => $request->user()->firstName . ' ' . $request->user()->lastName
        ];

        app(FileUploadRepository::class)->store($fileUpload);

        return response()->json(['message' => 'success']);
    }

    public function destroy(Request $request)
    {

    }
}
