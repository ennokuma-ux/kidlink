$ErrorActionPreference = 'Stop'
$root = Get-Location
$routeFile = Join-Path $root 'routes\web.php'
$controllerFile = Join-Path $root 'app\Http\Controllers\Teacher\StudentController.php'
if (-not (Test-Path (Join-Path $root 'artisan')) -or -not (Test-Path $routeFile)) {
    throw "Run this script from the Laravel project root (the folder containing artisan and routes\web.php). Current folder: $root"
}
$content = Get-Content -Raw $routeFile
if ($content -match "name\(['\"]account-requests\.store['\"]\)") {
    Write-Host 'Route name teacher.account-requests.store already exists in routes/web.php.'
    exit 0
}
$import = 'use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;'
if ($content -notmatch [regex]::Escape($import)) {
    $anchor = 'use Illuminate\Support\Facades\Route;'
    if ($content.Contains($anchor)) { $content = $content.Replace($anchor, "$anchor`r`n$import") }
    else { throw 'Could not safely add TeacherStudentController import. No changes made.' }
}
$groupPattern = "(?m)^(\s*Route::middleware\(\[[^\r\n]*role:teacher[^\r\n]*\]\)->prefix\(['\"]teacher['\"]\)->name\(['\"]teacher\.['\"]\)->group\(function\s*\(\)\s*\{\s*)$"
$m = [regex]::Match($content, $groupPattern)
if (-not $m.Success) {
    throw "Could not find the teacher route group in routes/web.php. No changes were written. Please share routes/web.php so the patch can match your version."
}
$backup = "$routeFile.bak-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
Copy-Item $routeFile $backup
$route = "`r`n    Route::post('/account-requests', [TeacherStudentController::class, 'storeAccountRequest'])->name('account-requests.store');"
$content = $content.Insert($m.Index + $m.Length, $route)
Set-Content -Path $routeFile -Value $content -Encoding UTF8
Write-Host "Added teacher.account-requests.store. Backup: $backup"
if (Test-Path $controllerFile) {
    $controller = Get-Content -Raw $controllerFile
    if ($controller -notmatch 'function\s+storeAccountRequest\s*\(') {
        Write-Warning 'Route added, but StudentController does not contain storeAccountRequest(). The route will list, but submitting the form needs the matching controller method.'
    }
} else { Write-Warning 'Teacher StudentController was not found; confirm the route controller matches your project.' }
Write-Host 'Next run: php artisan optimize:clear'
Write-Host 'Verify with: php artisan route:list --name=account-requests'
