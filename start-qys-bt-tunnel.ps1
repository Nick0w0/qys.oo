param(
    [ValidateSet('menu', 'start', 'stop', 'status', 'open')]
    [string]$Action = 'menu'
)

$ErrorActionPreference = 'Stop'

$sshExe = Join-Path $env:WINDIR 'System32\OpenSSH\ssh.exe'
$keyPath = Join-Path $env:USERPROFILE '.ssh\id_ed25519_qys_server'
$serverHost = '8.145.46.243'
$serverPort = 62022
$localPort = 32742
$remoteHost = '127.0.0.1'
$remotePort = 32742
$panelPath = '/qyspanel88'
$panelUrl = "https://127.0.0.1:$localPort$panelPath"

function Assert-Env {
    if (-not (Test-Path $sshExe)) {
        throw "SSH executable not found: $sshExe"
    }

    if (-not (Test-Path $keyPath)) {
        throw "SSH key not found: $keyPath"
    }
}

function Get-TunnelConnection {
    return Get-NetTCPConnection -State Listen -LocalPort $localPort -ErrorAction SilentlyContinue |
        Sort-Object OwningProcess |
        Select-Object -First 1
}

function Start-Tunnel {
    Assert-Env
    $existing = Get-TunnelConnection
    if ($existing) {
        Write-Host "Tunnel already listening on local port $localPort. PID: $($existing.OwningProcess)"
        return $existing.OwningProcess
    }

    $arguments = @(
        '-N',
        '-L', "$localPort`:$remoteHost`:$remotePort",
        '-p', "$serverPort",
        '-i', $keyPath,
        '-o', 'StrictHostKeyChecking=accept-new',
        "root@$serverHost"
    )

    $process = Start-Process -FilePath $sshExe -ArgumentList $arguments -WindowStyle Hidden -PassThru
    Start-Sleep -Seconds 2

    $listening = Get-TunnelConnection
    if (-not $listening) {
        throw "Tunnel startup failed. Check SSH key/passphrase and server connectivity."
    }

    Write-Host "Tunnel started. PID: $($process.Id)"
    return $process.Id
}

function Stop-Tunnel {
    $existing = Get-TunnelConnection
    if (-not $existing) {
        Write-Host "Tunnel is not running."
        return
    }

    Stop-Process -Id $existing.OwningProcess -Force
    Start-Sleep -Milliseconds 800
    Write-Host "Tunnel stopped. PID: $($existing.OwningProcess)"
}

function Show-Status {
    $existing = Get-TunnelConnection
    if ($existing) {
        Write-Host "Tunnel status: RUNNING"
        Write-Host "PID: $($existing.OwningProcess)"
        Write-Host "Panel: $panelUrl"
    } else {
        Write-Host "Tunnel status: STOPPED"
        Write-Host "Panel: $panelUrl"
    }
}

function Open-Panel {
    $null = Start-Tunnel
    Start-Process $panelUrl
    Write-Host "Baota panel: $panelUrl"
}

switch ($Action) {
    'start' {
        Start-Tunnel | Out-Null
        break
    }
    'stop' {
        Stop-Tunnel
        break
    }
    'status' {
        Show-Status
        break
    }
    'open' {
        Open-Panel
        break
    }
    default {
        Write-Host "=============================="
        Write-Host " QYS 宝塔隧道管理"
        Write-Host "=============================="
        Write-Host "1. 开启并打开面板"
        Write-Host "2. 仅开启隧道"
        Write-Host "3. 关闭隧道"
        Write-Host "4. 查看状态"
        Write-Host "5. 退出"
        $choice = Read-Host "请输入选项"
        switch ($choice) {
            '1' { Open-Panel }
            '2' { Start-Tunnel | Out-Null }
            '3' { Stop-Tunnel }
            '4' { Show-Status }
            default { Write-Host "已退出。" }
        }
    }
}
