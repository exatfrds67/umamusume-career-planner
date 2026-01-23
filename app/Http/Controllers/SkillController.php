<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    /**
     * Display the skill management interface.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        // Get all characters for the authenticated user
        $characters = Character::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('skills.index', [
            'characters' => $characters,
        ]);
    }
}
