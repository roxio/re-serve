$ErrorActionPreference = 'Stop'

$appRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$uploadTmp = Join-Path $appRoot 'writable\uploads'

New-Item -ItemType Directory -Force -Path $uploadTmp | Out-Null
Set-Location $appRoot

php -d "upload_tmp_dir=$uploadTmp" -S 127.0.0.1:8080 -t public
