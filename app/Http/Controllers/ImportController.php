<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportWorkspaceRequest;
use App\Models\Workspace;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function preview(
        ImportWorkspaceRequest $request,
        Workspace $workspace,
        ImportService $service,
    ): JsonResponse {
        $format = $request->importFormat();
        $content = $request->uploadedFile()->getContent();
        $result = match ($format) {
            'json' => $service->previewFromJson($workspace, $content),
            'csv' => $service->previewFromCsv($workspace, $content),
            default => throw new \LogicException('Validated import format was not supported.'),
        };

        return response()->json([
            'preview' => [
                'format' => $format,
                ...$result,
            ],
        ]);
    }

    public function import(
        ImportWorkspaceRequest $request,
        Workspace $workspace,
        ImportService $service,
    ): JsonResponse {
        $content = $request->uploadedFile()->getContent();

        $result = match ($request->importFormat()) {
            'json' => $service->importFromJson($workspace, $content),
            'csv' => $this->legacyCsvResult($service->importFromCsv($workspace, $content)),
            default => throw new \LogicException('Validated import format was not supported.'),
        };

        return response()->json(['imported' => $result]);
    }

    /**
     * @param  array{version: int, projects: int, todos: int}  $result
     * @return array{version: int, todos_imported: int}
     */
    private function legacyCsvResult(array $result): array
    {
        return [
            'version' => $result['version'],
            'todos_imported' => $result['todos'],
        ];
    }
}
