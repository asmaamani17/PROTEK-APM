<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VictimsListController extends Controller
{
    /**
     * Display the victims list with sorting, searching, and filtering.
     */
    public function index(Request $request)
    {
        $admin = auth()->user();
        $adminArea = $admin->daerah;
        $activeTab = 'victims';

        // Base query for vulnerable groups with user information
        $query = DB::table('vulnerable_groups')
            ->leftJoin('users', 'vulnerable_groups.user_id', '=', 'users.id')
            ->where(function($q) use ($adminArea) {
                $q->where('users.daerah', $adminArea)
                  ->orWhere('vulnerable_groups.district', $adminArea)
                  ->orWhereNull('users.daerah');
            })
            ->select([
                'vulnerable_groups.serial_number',
                'vulnerable_groups.name',
                'vulnerable_groups.identification_number',
                'vulnerable_groups.gender',
                'vulnerable_groups.address',
                'vulnerable_groups.district',
                'vulnerable_groups.parliament',
                'vulnerable_groups.dun',
                'vulnerable_groups.phone_number',
                'vulnerable_groups.disability_category',
                'vulnerable_groups.client_type',
                'vulnerable_groups.oku_status',
                'vulnerable_groups.age_group',
                'vulnerable_groups.parliament_dun_code',
                'vulnerable_groups.prb_serial_number',
                'vulnerable_groups.installation_status',
                'vulnerable_groups.latitude',
                'vulnerable_groups.longitude',
                'vulnerable_groups.created_at',
                'vulnerable_groups.installation_date',
                'vulnerable_groups.id', // keep id for internal use if needed
            ]);

        // Search
        $search = $request->get('search');
        if ($search) {
            $searchTerms = array_filter(array_map('trim', explode(' ', $search)));
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function($innerQ) use ($term) {
                        $innerQ->where('users.name', 'like', "%{$term}%")
                              ->orWhere('vulnerable_groups.identification_number', 'like', "%{$term}%")
                              ->orWhere('vulnerable_groups.phone_number', 'like', "%{$term}%")
                              ->orWhere('users.email', 'like', "%{$term}%")
                              ->orWhere('vulnerable_groups.disability_category', 'like', "%{$term}%")
                              ->orWhere('vulnerable_groups.gender', 'like', "%{$term}%")
                              ->orWhere('vulnerable_groups.age_group', 'like', "%{$term}%")
                              ->orWhere('vulnerable_groups.district', 'like', "%{$term}%")
                              ->orWhere('vulnerable_groups.address', 'like', "%{$term}%");
                    });
                }
            });
        }

        // Category filter
        $category = $request->get('category');
        if ($category) {
            $query->where('vulnerable_groups.disability_category', $category);
        }

        // Sorting
        $sortable = [
            'name' => 'vulnerable_groups.name',
            'ic_number' => 'vulnerable_groups.identification_number',
            'phone_number' => 'vulnerable_groups.phone_number',
            'disability_category' => 'vulnerable_groups.disability_category',
            'gender' => 'vulnerable_groups.gender',
            'age_group' => 'vulnerable_groups.age_group',
            'district' => 'vulnerable_groups.district',
            'installation_status' => 'vulnerable_groups.installation_status',
            'installation_date' => 'vulnerable_groups.installation_date',
            'created_at' => 'vulnerable_groups.created_at',
        ];
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';
        if (isset($sortable[$sort])) {
            $query->orderBy($sortable[$sort], $direction);
        } else {
            $sort = 'created_at';
            $direction = 'desc';
            $query->orderBy('vulnerable_groups.created_at', 'desc');
        }

        $victims = $query->paginate(15)->appends($request->except('page'));

        // Unique categories for filter dropdown
        $categories = DB::table('vulnerable_groups')
            ->select('disability_category')
            ->distinct()
            ->pluck('disability_category')
            ->filter();

        return view('admin.victims', [
            'victims' => $victims,
            'categories' => $categories,
            'activeTab' => $activeTab,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }
}
