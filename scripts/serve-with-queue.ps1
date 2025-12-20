# Serve with queue worker (Windows PowerShell)
# Starts queue worker as a background job and then runs `php artisan serve` in foreground.
# Run with: PowerShell -ExecutionPolicy Bypass -File scripts\serve-with-queue.ps1

Write-Host "Starting queue worker as background job..."
$queueJob = Start-Job -Name "artisan-queue-work" -ScriptBlock {
    Write-Output "[queue] starting worker"
    # Use --sleep/--tries tuning as needed
    php artisan queue:work --sleep=3 --tries=3
}
Write-Host "Queue job started (Id:" $queueJob.Id ")"

Write-Host "Starting Laravel dev server (php artisan serve)..."
# Run serve in the same console so user can see logs; when it exits we stop the job.
php artisan serve

Write-Host "artisan serve stopped — stopping queue job..."
Get-Job -Name "artisan-queue-work" | ForEach-Object {
    if ($_.State -eq 'Running' -or $_.State -eq 'Completed' -or $_.State -eq 'Blocked') {
        Stop-Job -Force -Job $_
    }
    Remove-Job -Job $_ -Force
}
Write-Host "Queue job stopped and removed."
