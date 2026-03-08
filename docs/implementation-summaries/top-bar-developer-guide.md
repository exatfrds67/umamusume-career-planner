# Top Bar Developer Guide

Quick Reference for Using the Enhanced Top Bar

---

## Quick Start

### Minimal Setup (Existing Functionality)

```php
// In your controller or Livewire component
public function index()
{
    $topStatus = [
        'currentTurn' => 15,
        'maxTurns' => 70,
        'spAvailable' => 450,
        'storageMode' => 'account', // or 'local'
    ];
    
    return view('your.view', compact('topStatus'));
}
```text

### Full Setup (All Features)

```php
// In your controller or Livewire component
public function index()
{
    $careerRun = CareerRun::findOrFail($id);
    
    $topStatus = [
        'currentTurn' => $careerRun->current_turn,
        'maxTurns' => $careerRun->max_turns,
        'spAvailable' => $careerRun->sp_available,
        'storageMode' => $careerRun->storage_mode,
        'energy' => $careerRun->energy,              // 0-100
        'mood' => $careerRun->mood,                  // 'great', 'good', 'normal', 'bad', 'very bad'
        'careerStage' => $careerRun->career_stage,   // 'junior', 'classic', 'senior'
    ];
    
    return view('your.view', compact('topStatus'));
}
```

---

## Field Reference

### Required Fields

| Field | Type | Example | Description |
| --- | --- | --- | --- |
| `currentTurn` | int\|null | `15` | Current turn number (1-70) |
| `maxTurns` | int\|null | `70` | Maximum turns in career |
| `spAvailable` | int\|null | `450` | Available skill points |
| `storageMode` | string\|null | `'account'` | Storage mode: 'local' or 'account' |

### Optional Fields (New)

| Field | Type | Example | Valid Values | Description |
| --- | --- | --- | --- | --- |
| `energy` | int\|null | `78` | 0-100 | Character energy level |
| `mood` | string\|null | `'good'` | See below | Character mood state |
| `careerStage` | string\|null | `'senior'` | 'junior', 'classic', 'senior' | Current career phase |

### Mood Values

| Value | Emoji | Modifier | Display |
| --- | --- | --- | --- |
| `'great'` or `'excellent'` | 😊 | +20% | "Great" |
| `'good'` | 🙂 | +10% | "Good" |
| `'normal'` or `'neutral'` | 😐 | 0% | "Normal" |
| `'bad'` | 🙁 | -10% | "Bad" |
| `'very bad'` or `'terrible'` | 😞 | -20% | "Very Bad" |

---

## Usage Examples

### Example 1: Dashboard with Full Status

```php
// app/Http/Controllers/DashboardController.php
public function index()
{
    $currentRun = Auth::user()->currentCareerRun();
    
    if (!$currentRun) {
        return view('dashboard.empty');
    }
    
    $topStatus = [
        'currentTurn' => $currentRun->current_turn,
        'maxTurns' => $currentRun->max_turns,
        'spAvailable' => $currentRun->sp_available,
        'storageMode' => $currentRun->storage_mode,
        'energy' => $currentRun->energy,
        'mood' => $currentRun->mood,
        'careerStage' => $currentRun->career_stage,
    ];
    
    return view('dashboard.index', compact('topStatus', 'currentRun'));
}
```text

### Example 2: Training Page with Energy Focus

```php
// app/Http/Controllers/TrainingController.php
public function index()
{
    $run = session('current_run');
    
    $topStatus = [
        'currentTurn' => $run->current_turn,
        'maxTurns' => $run->max_turns,
        'spAvailable' => $run->sp_available,
        'storageMode' => $run->storage_mode,
        'energy' => $run->energy, // Important for training decisions
        'mood' => $run->mood,     // Affects training effectiveness
        'careerStage' => $run->career_stage,
    ];
    
    return view('training.index', compact('topStatus'));
}
```

### Example 3: Livewire Component

```php
// app/Livewire/CareerRun/Dashboard.php
namespace App\Livewire\CareerRun;

use Livewire\Component;

class Dashboard extends Component
{
    public $careerRun;
    
    public function mount($id)
    {
        $this->careerRun = CareerRun::findOrFail($id);
    }
    
    public function render()
    {
        $topStatus = [
            'currentTurn' => $this->careerRun->current_turn,
            'maxTurns' => $this->careerRun->max_turns,
            'spAvailable' => $this->careerRun->sp_available,
            'storageMode' => $this->careerRun->storage_mode,
            'energy' => $this->careerRun->energy,
            'mood' => $this->careerRun->mood,
            'careerStage' => $this->careerRun->career_stage,
        ];
        
        return view('livewire.career-run.dashboard', compact('topStatus'));
    }
}
```text

### Example 4: Minimal Setup (Backward Compatible)

```php
// Existing code still works - new fields are optional
public function show($id)
{
    $run = CareerRun::findOrFail($id);
    
    $topStatus = [
        'currentTurn' => $run->current_turn,
        'maxTurns' => $run->max_turns,
        'spAvailable' => $run->sp_available,
        'storageMode' => $run->storage_mode,
        // energy, mood, careerStage will show as "—" if not provided
    ];
    
    return view('runs.show', compact('topStatus'));
}
```

---

## Null Handling

All fields are nullable. Missing or null values display as "—":

```php
$topStatus = [
    'currentTurn' => null,    // Displays: "—"
    'maxTurns' => null,       // Displays: "—"
    'spAvailable' => null,    // Displays: "—"
    'storageMode' => null,    // Displays: "—" with secondary badge
    'energy' => null,         // Not displayed
    'mood' => null,           // Not displayed
    'careerStage' => null,    // Not displayed
];
```text

---

## Energy Color Coding

The energy indicator automatically color-codes based on value:

```php
// Green (healthy)
'energy' => 85  // ⚡ 85/100 (green)

// Yellow (moderate)
'energy' => 55  // ⚡ 55/100 (yellow)

// Red (low)
'energy' => 25  // ⚡ 25/100 (red)
```

---

## Storage Mode Badge Colors

```php
// Green badge (Account mode)
'storageMode' => 'account'  // [Account] (green)

// Yellow/Amber badge (Local mode)
'storageMode' => 'local'    // [Local] (amber)

// Gray badge (Unknown)
'storageMode' => null       // [—] (gray)
```text

---

## Run Selector Integration

### Current Implementation

The run selector currently uses placeholder data. To integrate:

```php
// Option 1: Pass current run name to header
$currentRun = 'Mejiro Ardan'; // Character name
return view('your.view', compact('topStatus', 'currentRun'));
```

### Future Implementation (Dynamic)

```php
// Create Livewire component for run selector
// app/Livewire/Dashboard/RunSelector.php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class RunSelector extends Component
{
    public $currentRunId;
    public $availableRuns;
    
    public function mount()
    {
        $this->currentRunId = session('current_run_id');
        $this->loadRuns();
    }
    
    public function loadRuns()
    {
        $this->availableRuns = Auth::user()
            ->careerRuns()
            ->active()
            ->with('character')
            ->get()
            ->map(fn($run) => [
                'id' => $run->id,
                'name' => $run->character->name,
                'scenario' => $run->scenario,
                'turn' => $run->current_turn,
                'maxTurn' => $run->max_turns,
            ]);
    }
    
    public function selectRun($runId)
    {
        session(['current_run_id' => $runId]);
        $this->currentRunId = $runId;
        $this->dispatch('run-changed', runId: $runId);
        return redirect()->route('dashboard');
    }
    
    public function render()
    {
        return view('livewire.dashboard.run-selector');
    }
}
```text

---

## Common Patterns

### Pattern 1: Service Layer

```php
// app/Services/CareerRun/StatusService.php
namespace App\Services\CareerRun;

class StatusService
{
    public function getTopStatus(CareerRun $run): array
    {
        return [
            'currentTurn' => $run->current_turn,
            'maxTurns' => $run->max_turns,
            'spAvailable' => $run->sp_available,
            'storageMode' => $run->storage_mode,
            'energy' => $run->energy,
            'mood' => $run->mood,
            'careerStage' => $run->career_stage,
        ];
    }
}

// In controller
public function index(StatusService $statusService)
{
    $run = Auth::user()->currentCareerRun();
    $topStatus = $statusService->getTopStatus($run);
    
    return view('dashboard.index', compact('topStatus'));
}
```

### Pattern 2: View Composer

```php
// app/Providers/ViewServiceProvider.php
use Illuminate\Support\Facades\View;

public function boot()
{
    View::composer('*', function ($view) {
        $currentRun = Auth::user()?->currentCareerRun();
        
        if ($currentRun) {
            $topStatus = [
                'currentTurn' => $currentRun->current_turn,
                'maxTurns' => $currentRun->max_turns,
                'spAvailable' => $currentRun->sp_available,
                'storageMode' => $currentRun->storage_mode,
                'energy' => $currentRun->energy,
                'mood' => $currentRun->mood,
                'careerStage' => $currentRun->career_stage,
            ];
            
            $view->with('topStatus', $topStatus);
        }
    });
}
```text

### Pattern 3: Middleware

```php
// app/Http/Middleware/InjectTopStatus.php
namespace App\Http\Middleware;

class InjectTopStatus
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $run = Auth::user()->currentCareerRun();
            
            if ($run) {
                view()->share('topStatus', [
                    'currentTurn' => $run->current_turn,
                    'maxTurns' => $run->max_turns,
                    'spAvailable' => $run->sp_available,
                    'storageMode' => $run->storage_mode,
                    'energy' => $run->energy,
                    'mood' => $run->mood,
                    'careerStage' => $run->career_stage,
                ]);
            }
        }
        
        return $next($request);
    }
}
```

---

## Testing

### Feature Test Example

```php
// tests/Feature/TopBarTest.php
use Tests\TestCase;

class TopBarTest extends TestCase
{
    public function test_top_bar_displays_all_status_indicators()
    {
        $user = User::factory()->create();
        $run = CareerRun::factory()->create([
            'user_id' => $user->id,
            'current_turn' => 15,
            'max_turns' => 70,
            'sp_available' => 450,
            'storage_mode' => 'account',
            'energy' => 78,
            'mood' => 'good',
            'career_stage' => 'senior',
        ]);
        
        $response = $this->actingAs($user)
            ->get(route('dashboard'));
        
        $response->assertSee('15');
        $response->assertSee('70');
        $response->assertSee('450');
        $response->assertSee('Account');
        $response->assertSee('78');
        $response->assertSee('Good');
        $response->assertSee('Senior');
    }
    
    public function test_energy_color_coding()
    {
        $user = User::factory()->create();
        
        // Test green (high energy)
        $run = CareerRun::factory()->create([
            'user_id' => $user->id,
            'energy' => 85,
        ]);
        
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertSee('text-green-600');
        
        // Test yellow (moderate energy)
        $run->update(['energy' => 55]);
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertSee('text-yellow-600');
        
        // Test red (low energy)
        $run->update(['energy' => 25]);
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertSee('text-red-600');
    }
}
```text

---

## Troubleshooting

### Issue: Status bar shows "—" for all values

**Solution**: Ensure `$topStatus` is passed to the view:

```php
// ❌ Wrong
return view('your.view');

// ✅ Correct
return view('your.view', compact('topStatus'));
```

### Issue: Energy/Mood not displaying

**Solution**: These fields are optional. They only display if provided:

```php
// ❌ Missing fields
$topStatus = [
    'currentTurn' => 15,
    'maxTurns' => 70,
];

// ✅ Include all fields
$topStatus = [
    'currentTurn' => 15,
    'maxTurns' => 70,
    'energy' => 78,        // Add this
    'mood' => 'good',      // Add this
];
```text

### Issue: Storage badge wrong color

**Solution**: Ensure storage mode is lowercase:

```php
// ❌ Wrong (won't match)
'storageMode' => 'Account'

// ✅ Correct
'storageMode' => 'account'
```

### Issue: Run selector not showing

**Solution**: Run selector is hidden on mobile. Check viewport size or add `$currentRun` variable:

```php
$currentRun = 'Mejiro Ardan';
return view('your.view', compact('topStatus', 'currentRun'));
```text

---

## Best Practices

1. **Always provide all fields** when available for best UX
2. **Use service layer** for complex status calculations
3. **Cache expensive queries** if status is computed from multiple sources
4. **Validate mood values** to ensure correct emoji display
5. **Test responsive behavior** at all breakpoints
6. **Use view composers** for global status availability
7. **Document custom implementations** for team consistency

---

## Related Documentation

- [top-bar-enhancement-summary.md](./top-bar-enhancement-summary.md) - Implementation details
- [top-bar-visual-comparison.md](./top-bar-visual-comparison.md) - Visual changes
- [WF-001](../01-wireframes/WF-001_Dashboard_Overview.md) - Dashboard wireframe
- [PRD-001](../02-prds/PRD-001_Character_Management.md) - Product requirements

---

**Need Help?** Check the implementation summary or visual comparison documents for more details.
