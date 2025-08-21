<?php

namespace Litepie\Masters\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Litepie\Masters\Facades\Masters;
use Litepie\Masters\Http\Requests\MasterDataRequest;

class MasterDataController extends Controller
{
    /**
     * Get master data for a specific type.
     */
    public function index(Request $request, string $type): JsonResponse
    {
        $filters = $request->only(['parent_id', 'search', 'name', 'code', 'is_active']);
        $data = Masters::get($type, $filters);

        return response()->json([
            'data' => $data,
            'type' => $type,
            'count' => $data->count()
        ]);
    }

    /**
     * Store new master data.
     */
    public function store(MasterDataRequest $request, string $type): JsonResponse
    {
        $data = Masters::create($type, $request->validated());

        return response()->json([
            'data' => $data,
            'message' => 'Master data created successfully'
        ], 201);
    }

    /**
     * Show specific master data.
     */
    public function show(string $type, int $id): JsonResponse
    {
        $data = Masters::get($type, ['id' => $id]);

        if ($data->isEmpty()) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'data' => $data->first()
        ]);
    }

    /**
     * Update master data.
     */
    public function update(MasterDataRequest $request, string $type, int $id): JsonResponse
    {
        $success = Masters::update($type, $id, $request->validated());

        if (!$success) {
            return response()->json(['message' => 'Update failed'], 400);
        }

        return response()->json([
            'message' => 'Master data updated successfully'
        ]);
    }

    /**
     * Delete master data.
     */
    public function destroy(string $type, int $id): JsonResponse
    {
        $success = Masters::delete($type, $id);

        if (!$success) {
            return response()->json(['message' => 'Delete failed'], 400);
        }

        return response()->json([
            'message' => 'Master data deleted successfully'
        ]);
    }

    /**
     * Get hierarchical tree data.
     */
    public function tree(string $type): JsonResponse
    {
        $data = Masters::getHierarchical($type);

        return response()->json([
            'data' => $data,
            'type' => $type
        ]);
    }

    /**
     * Get children of a parent.
     */
    public function children(string $type, ?int $parentId = null): JsonResponse
    {
        $data = Masters::getHierarchical($type, $parentId);

        return response()->json([
            'data' => $data,
            'parent_id' => $parentId
        ]);
    }

    /**
     * Search master data.
     */
    public function search(string $type, string $query): JsonResponse
    {
        $data = Masters::search($type, $query);

        return response()->json([
            'data' => $data,
            'query' => $query,
            'count' => $data->count()
        ]);
    }

    /**
     * Import master data.
     */
    public function import(Request $request, string $type): JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.name' => 'required|string'
        ]);

        $results = Masters::import($type, $request->input('data'));

        return response()->json($results);
    }

    /**
     * Export master data.
     */
    public function export(Request $request, string $type): JsonResponse
    {
        $filters = $request->only(['parent_id', 'search', 'name', 'code', 'is_active']);
        $data = Masters::export($type, $filters);

        return response()->json([
            'data' => $data,
            'type' => $type,
            'count' => count($data)
        ]);
    }

    // Convenience methods for common master data
    
    public function countries(): JsonResponse
    {
        return $this->index(request(), 'countries');
    }

    public function states(?int $countryId = null): JsonResponse
    {
        $filters = $countryId ? ['parent_id' => $countryId] : [];
        $data = Masters::get('states', $filters);
        
        return response()->json(['data' => $data]);
    }

    public function cities(?int $stateId = null): JsonResponse
    {
        $filters = $stateId ? ['parent_id' => $stateId] : [];
        $data = Masters::get('cities', $filters);
        
        return response()->json(['data' => $data]);
    }

    public function currencies(): JsonResponse
    {
        return $this->index(request(), 'currencies');
    }

    public function languages(): JsonResponse
    {
        return $this->index(request(), 'languages');
    }
}
